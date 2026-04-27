<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Logger;
use App\Helpers\SmsHelper;

class SmsApiController extends Controller
{
    private ?array $user = null;

    private function authenticate(): bool
    {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
        if (empty($apiKey)) {
            $this->json(['error' => 'API key required', 'status' => 401], 401);
            return false;
        }

        $key = Database::fetch("SELECT ak.*, u.* FROM api_keys ak JOIN users u ON ak.user_id = u.id WHERE ak.api_key = ? AND ak.is_active = 1", [$apiKey]);
        if (!$key || $key['status'] !== 'active') {
            $this->json(['error' => 'Invalid or inactive API key', 'status' => 401], 401);
            return false;
        }

        // IP whitelist check
        if (!empty($key['ip_whitelist'])) {
            $allowedIps = array_map('trim', explode(',', $key['ip_whitelist']));
            if (!in_array(Logger::getIp(), $allowedIps)) {
                $this->json(['error' => 'IP not whitelisted', 'status' => 403], 403);
                return false;
            }
        }

        Database::update('api_keys', ['last_used_at' => date('Y-m-d H:i:s')], 'id = ?', [$key['id']]);
        $this->user = $key;
        return true;
    }

    public function send(): void
    {
        if (!$this->authenticate()) return;

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $recipient = SmsHelper::cleanNumber($input['to'] ?? $input['recipient'] ?? '');
        $message = trim($input['message'] ?? '');
        $senderId = $input['sender_id'] ?? $input['from'] ?? '';

        if (empty($recipient) || empty($message)) {
            $this->json(['error' => 'recipient and message are required', 'status' => 400], 400);
            return;
        }

        if (!SmsHelper::validateNumber($recipient)) {
            $this->json(['error' => 'Invalid phone number', 'status' => 400], 400);
            return;
        }

        if (SmsHelper::isBlacklisted($recipient, $this->user['user_id'])) {
            $this->json(['error' => 'Number is blacklisted', 'status' => 400], 400);
            return;
        }

        $gateway = SmsHelper::selectGateway(SmsHelper::detectCountry($recipient));
        if (!$gateway) {
            $this->json(['error' => 'No gateway available', 'status' => 503], 503);
            return;
        }

        $parts = SmsHelper::calculateParts($message);
        $cost = $parts * (float)$gateway['cost_per_sms'];

        if (!SmsHelper::deductCredits($this->user['user_id'], $cost, "API SMS to {$recipient}")) {
            $this->json(['error' => 'Insufficient balance', 'status' => 402], 402);
            return;
        }

        $queueId = SmsHelper::queueSms([
            'user_id' => $this->user['user_id'],
            'gateway_id' => $gateway['id'],
            'sender_id' => $senderId ?: $gateway['default_sender_id'],
            'recipient' => $recipient,
            'message' => $message,
            'cost' => $cost,
            'priority' => 3
        ]);

        $this->json([
            'success' => true,
            'message_id' => $queueId,
            'to' => $recipient,
            'parts' => $parts,
            'cost' => $cost,
            'status' => 'queued'
        ]);
    }

    public function sendBulk(): void
    {
        if (!$this->authenticate()) return;
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $recipients = $input['recipients'] ?? $input['to'] ?? [];
        $message = trim($input['message'] ?? '');

        if (empty($recipients) || empty($message)) {
            $this->json(['error' => 'recipients and message required', 'status' => 400], 400);
            return;
        }

        if (is_string($recipients)) $recipients = explode(',', $recipients);

        $gateway = SmsHelper::selectGateway();
        if (!$gateway) { $this->json(['error' => 'No gateway available'], 503); return; }

        $parts = SmsHelper::calculateParts($message);
        $costPerSms = $parts * (float)$gateway['cost_per_sms'];
        $results = [];
        $queued = 0;

        foreach ($recipients as $recipient) {
            $recipient = SmsHelper::cleanNumber(trim($recipient));
            if (!SmsHelper::validateNumber($recipient)) { $results[] = ['to' => $recipient, 'status' => 'invalid']; continue; }
            if (!SmsHelper::deductCredits($this->user['user_id'], $costPerSms)) { $results[] = ['to' => $recipient, 'status' => 'insufficient_balance']; continue; }

            $id = SmsHelper::queueSms(['user_id' => $this->user['user_id'], 'gateway_id' => $gateway['id'], 'recipient' => $recipient, 'message' => $message, 'cost' => $costPerSms]);
            $results[] = ['to' => $recipient, 'message_id' => $id, 'status' => 'queued'];
            $queued++;
        }

        $this->json(['success' => true, 'queued' => $queued, 'total' => count($recipients), 'results' => $results]);
    }

    public function status(string $id): void
    {
        if (!$this->authenticate()) return;
        $sms = Database::fetch("SELECT uuid, recipient, status, parts, cost, sent_at, delivered_at, created_at FROM sms_queue WHERE id = ? AND user_id = ?", [(int)$id, $this->user['user_id']]);
        if (!$sms) { $this->json(['error' => 'Message not found'], 404); return; }
        $this->json($sms);
    }

    public function balance(): void
    {
        if (!$this->authenticate()) return;
        $user = Database::fetch("SELECT sms_balance, daily_sms_limit, monthly_sms_limit FROM users WHERE id = ?", [$this->user['user_id']]);
        $this->json(['balance' => (float)$user['sms_balance'], 'daily_limit' => $user['daily_sms_limit'], 'monthly_limit' => $user['monthly_sms_limit']]);
    }
}
