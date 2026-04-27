<?php
namespace App\Helpers;

use App\Core\Database;
use App\Core\Logger;

class SmsHelper
{
    public static function isUnicode(string $text): bool
    {
        return preg_match('/[^\x00-\x7F]/', $text) === 1;
    }

    public static function calculateParts(string $text): int
    {
        $isUnicode = self::isUnicode($text);
        $len = mb_strlen($text, 'UTF-8');

        if ($isUnicode) {
            return $len <= 70 ? 1 : (int) ceil($len / 67);
        }
        return $len <= 160 ? 1 : (int) ceil($len / 153);
    }

    public static function cleanNumber(string $number): string
    {
        return preg_replace('/[^0-9+]/', '', trim($number));
    }

    public static function validateNumber(string $number): bool
    {
        $clean = self::cleanNumber($number);
        return preg_match('/^\+?[1-9]\d{6,14}$/', $clean) === 1;
    }

    public static function detectCountry(string $number): ?string
    {
        $clean = ltrim(self::cleanNumber($number), '+');
        $prefixes = [
            '1' => 'US', '44' => 'GB', '91' => 'IN', '92' => 'PK',
            '86' => 'CN', '81' => 'JP', '49' => 'DE', '33' => 'FR',
            '39' => 'IT', '34' => 'ES', '55' => 'BR', '7' => 'RU',
            '966' => 'SA', '971' => 'AE', '20' => 'EG', '27' => 'ZA',
            '61' => 'AU', '62' => 'ID', '63' => 'PH', '60' => 'MY',
            '90' => 'TR', '82' => 'KR', '234' => 'NG', '254' => 'KE',
        ];

        foreach ($prefixes as $prefix => $country) {
            if (str_starts_with($clean, $prefix)) {
                return $country;
            }
        }
        return null;
    }

    public static function isBlacklisted(string $number, ?int $userId = null): bool
    {
        $clean = self::cleanNumber($number);
        $where = "(phone = ? AND (is_global = 1" . ($userId ? " OR user_id = ?" : "") . "))";
        $params = [$clean];
        if ($userId) $params[] = $userId;

        return Database::count('blacklist', $where, $params) > 0;
    }

    public static function personalizeMessage(string $message, array $contact): string
    {
        $replacements = [
            '{name}' => $contact['name'] ?? '',
            '{phone}' => $contact['phone'] ?? '',
            '{email}' => $contact['email'] ?? '',
            '{company}' => $contact['company'] ?? '',
        ];

        // Custom fields
        if (!empty($contact['custom_fields'])) {
            $custom = is_string($contact['custom_fields']) ? json_decode($contact['custom_fields'], true) : $contact['custom_fields'];
            if (is_array($custom)) {
                foreach ($custom as $key => $value) {
                    $replacements['{' . $key . '}'] = $value;
                }
            }
        }

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    public static function checkSpam(string $message): bool
    {
        $spamSetting = Database::fetch("SELECT setting_value FROM settings WHERE setting_key = 'spam_keywords'");
        if (!$spamSetting || empty($spamSetting['setting_value'])) return false;

        $keywords = array_map('trim', explode(',', strtolower($spamSetting['setting_value'])));
        $messageLower = strtolower($message);

        foreach ($keywords as $keyword) {
            if (!empty($keyword) && str_contains($messageLower, $keyword)) {
                return true;
            }
        }
        return false;
    }

    public static function deductCredits(int $userId, float $amount, string $description = '', ?int $referenceId = null): bool
    {
        try {
            Database::beginTransaction();

            $user = Database::fetch("SELECT sms_balance FROM users WHERE id = ? FOR UPDATE", [$userId]);
            if (!$user || $user['sms_balance'] < $amount) {
                Database::rollback();
                return false;
            }

            $newBalance = $user['sms_balance'] - $amount;
            Database::update('users', ['sms_balance' => $newBalance], 'id = ?', [$userId]);

            Database::insert('credit_logs', [
                'user_id' => $userId,
                'type' => 'deduct',
                'amount' => $amount,
                'balance_before' => $user['sms_balance'],
                'balance_after' => $newBalance,
                'reference_type' => 'sms',
                'reference_id' => $referenceId,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            Database::commit();
            return true;
        } catch (\Exception $e) {
            Database::rollback();
            Logger::error('Credit deduction failed: ' . $e->getMessage());
            return false;
        }
    }

    public static function refundCredits(int $userId, float $amount, string $description = '', ?int $referenceId = null): void
    {
        try {
            Database::beginTransaction();
            $user = Database::fetch("SELECT sms_balance FROM users WHERE id = ? FOR UPDATE", [$userId]);
            $newBalance = ($user['sms_balance'] ?? 0) + $amount;
            Database::update('users', ['sms_balance' => $newBalance], 'id = ?', [$userId]);
            Database::insert('credit_logs', [
                'user_id' => $userId,
                'type' => 'refund',
                'amount' => $amount,
                'balance_before' => $user['sms_balance'],
                'balance_after' => $newBalance,
                'reference_type' => 'sms',
                'reference_id' => $referenceId,
                'description' => $description,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            Database::commit();
        } catch (\Exception $e) {
            Database::rollback();
            Logger::error('Credit refund failed: ' . $e->getMessage());
        }
    }

    public static function selectGateway(?string $countryCode = null): ?array
    {
        $where = "status = 'active'";
        $params = [];

        if ($countryCode) {
            $route = Database::fetch(
                "SELECT gr.*, g.* FROM gateway_routes gr JOIN gateways g ON g.id = gr.gateway_id WHERE gr.country_code = ? AND gr.is_active = 1 AND g.status = 'active' ORDER BY gr.priority ASC LIMIT 1",
                [$countryCode]
            );
            if ($route) return $route;
        }

        // Default: cheapest active gateway
        return Database::fetch(
            "SELECT * FROM gateways WHERE {$where} ORDER BY is_default DESC, cost_per_sms ASC, priority ASC LIMIT 1",
            $params
        );
    }

    public static function queueSms(array $data): ?int
    {
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

        return Database::insert('sms_queue', [
            'uuid' => $uuid,
            'user_id' => $data['user_id'],
            'campaign_id' => $data['campaign_id'] ?? null,
            'gateway_id' => $data['gateway_id'] ?? null,
            'sender_id' => $data['sender_id'] ?? null,
            'recipient' => $data['recipient'],
            'message' => $data['message'],
            'message_type' => self::isUnicode($data['message']) ? 'unicode' : 'plain',
            'parts' => self::calculateParts($data['message']),
            'priority' => $data['priority'] ?? 5,
            'status' => 'queued',
            'cost' => $data['cost'] ?? 0,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
