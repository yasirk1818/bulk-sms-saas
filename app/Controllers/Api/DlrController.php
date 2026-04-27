<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Logger;

class DlrController extends Controller
{
    public function callback(string $gateway): void
    {
        $input = array_merge($_GET, $_POST, json_decode(file_get_contents('php://input'), true) ?? []);
        Logger::info("DLR callback for {$gateway}", $input);

        $messageId = $input['MessageSid'] ?? $input['message-id'] ?? $input['messageId'] ?? $input['msgid'] ?? null;
        $status = $input['MessageStatus'] ?? $input['status'] ?? $input['dlr_status'] ?? null;

        if ($messageId && $status) {
            $mappedStatus = $this->mapStatus($status);
            $sms = Database::fetch("SELECT id, user_id, campaign_id, gateway_id FROM sms_queue WHERE gateway_message_id = ?", [$messageId]);
            if ($sms) {
                Database::update('sms_queue', ['status' => $mappedStatus, 'delivered_at' => $mappedStatus === 'delivered' ? date('Y-m-d H:i:s') : null], 'id = ?', [$sms['id']]);
                Database::update('sms_logs', ['dlr_status' => $mappedStatus, 'dlr_received_at' => date('Y-m-d H:i:s'), 'status' => $mappedStatus], 'gateway_message_id = ?', [$messageId]);

                if ($sms['campaign_id']) {
                    $col = $mappedStatus === 'delivered' ? 'total_delivered' : 'total_failed';
                    Database::query("UPDATE campaigns SET {$col} = {$col} + 1 WHERE id = ?", [$sms['campaign_id']]);
                }

                // Refund on failure
                if ($mappedStatus === 'failed') {
                    $queueItem = Database::fetch("SELECT cost FROM sms_queue WHERE id = ?", [$sms['id']]);
                    if ($queueItem && $queueItem['cost'] > 0) {
                        \App\Helpers\SmsHelper::refundCredits($sms['user_id'], $queueItem['cost'], 'DLR failure refund', $sms['id']);
                    }
                }

                // Trigger webhooks
                $this->triggerWebhooks($sms['user_id'], 'dlr.' . $mappedStatus, ['message_id' => $messageId, 'status' => $mappedStatus]);
            }
        }

        $this->json(['success' => true]);
    }

    private function mapStatus(string $status): string
    {
        $status = strtolower($status);
        return match (true) {
            in_array($status, ['delivered', 'accepted', 'sent']) => 'delivered',
            in_array($status, ['failed', 'undelivered', 'rejected', 'expired']) => 'failed',
            default => 'sent'
        };
    }

    private function triggerWebhooks(int $userId, string $event, array $payload): void
    {
        $webhooks = Database::fetchAll("SELECT * FROM webhooks WHERE user_id = ? AND is_active = 1 AND JSON_CONTAINS(events, ?)", [$userId, json_encode($event)]);
        foreach ($webhooks as $webhook) {
            $body = json_encode(['event' => $event, 'data' => $payload, 'timestamp' => time()]);
            $signature = hash_hmac('sha256', $body, $webhook['secret'] ?? '');

            $ch = curl_init($webhook['url']);
            curl_setopt_array($ch, [CURLOPT_POST => true, CURLOPT_POSTFIELDS => $body, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'X-Webhook-Signature: ' . $signature]]);
            $response = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            Database::insert('webhook_logs', ['webhook_id' => $webhook['id'], 'event' => $event, 'payload' => json_encode($payload), 'response_code' => $code, 'response_body' => substr($response, 0, 1000), 'status' => $code >= 200 && $code < 300 ? 'success' : 'failed', 'created_at' => date('Y-m-d H:i:s')]);
        }
    }
}
