<?php
/**
 * SMS Queue Worker - Run via cron every minute
 * Usage: php cron/queue_worker.php
 * Cron: * * * * * /usr/bin/php /path/to/cron/queue_worker.php >> /path/to/storage/logs/queue.log 2>&1
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Config/config.php';
require_once ROOT_PATH . '/app/Core/Autoloader.php';

use App\Core\Database;
use App\Core\Logger;
use App\Helpers\GatewayHelper;
use App\Helpers\SmsHelper;

echo "[" . date('Y-m-d H:i:s') . "] Queue worker started\n";

$lockFile = ROOT_PATH . '/storage/cache/queue.lock';
if (file_exists($lockFile) && (time() - filemtime($lockFile)) < 300) {
    echo "Another worker is running. Exiting.\n";
    exit;
}
file_put_contents($lockFile, getmypid());

try {
    $batchSize = 50;

    // Process due scheduled messages first
    Database::query(
        "UPDATE sms_queue SET status = 'queued' WHERE status = 'queued' AND scheduled_at IS NOT NULL AND scheduled_at <= NOW()"
    );

    // Fetch and lock messages
    $messages = Database::fetchAll(
        "SELECT sq.*, g.* FROM sms_queue sq LEFT JOIN gateways g ON sq.gateway_id = g.id WHERE sq.status = 'queued' AND (sq.scheduled_at IS NULL OR sq.scheduled_at <= NOW()) AND sq.locked_at IS NULL ORDER BY sq.priority ASC, sq.created_at ASC LIMIT ?",
        [$batchSize]
    );

    echo "Found " . count($messages) . " messages to process\n";

    foreach ($messages as $msg) {
        // Lock the message
        Database::query("UPDATE sms_queue SET status = 'processing', locked_at = NOW(), locked_by = ? WHERE id = ? AND status = 'queued'", ['worker-' . getmypid(), $msg['id']]);

        $gateway = $msg['gateway_id'] ? Database::fetch("SELECT * FROM gateways WHERE id = ? AND status = 'active'", [$msg['gateway_id']]) : SmsHelper::selectGateway();

        if (!$gateway) {
            Database::update('sms_queue', ['status' => 'failed', 'error_message' => 'No gateway available', 'processed_at' => date('Y-m-d H:i:s')], 'id = ?', [$msg['id']]);
            SmsHelper::refundCredits($msg['user_id'], $msg['cost'], 'No gateway available', $msg['id']);
            continue;
        }

        // Send via gateway
        $result = GatewayHelper::send($gateway, $msg['recipient'], $msg['message'], $msg['sender_id']);

        if ($result['success']) {
            Database::update('sms_queue', [
                'status' => 'sent', 'gateway_message_id' => $result['message_id'] ?? null,
                'sent_at' => date('Y-m-d H:i:s'), 'response' => $result['response'] ?? '',
                'processed_at' => date('Y-m-d H:i:s'), 'locked_at' => null, 'locked_by' => null,
            ], 'id = ?', [$msg['id']]);

            // Log to sms_logs
            Database::insert('sms_logs', [
                'user_id' => $msg['user_id'], 'campaign_id' => $msg['campaign_id'],
                'gateway_id' => $gateway['id'], 'recipient' => $msg['recipient'],
                'sender_id' => $msg['sender_id'], 'message' => $msg['message'],
                'message_type' => $msg['message_type'], 'parts' => $msg['parts'],
                'cost' => $msg['cost'], 'status' => 'sent',
                'gateway_message_id' => $result['message_id'] ?? null,
                'gateway_response' => json_encode($result), 'response_time' => $result['response_time'] ?? null,
                'sent_at' => date('Y-m-d H:i:s'), 'created_at' => date('Y-m-d H:i:s')
            ]);

            echo "  Sent to {$msg['recipient']} via {$gateway['name']}\n";
        } else {
            $attempts = ($msg['retry_count'] ?? 0) + 1;
            $maxRetries = $gateway['retry_attempts'] ?? 3;

            if ($attempts < $maxRetries) {
                Database::update('sms_queue', [
                    'status' => 'queued', 'retry_count' => $attempts,
                    'last_retry_at' => date('Y-m-d H:i:s'), 'error_message' => $result['error'] ?? 'Unknown',
                    'locked_at' => null, 'locked_by' => null
                ], 'id = ?', [$msg['id']]);
                echo "  Retry {$attempts}/{$maxRetries} for {$msg['recipient']}\n";
            } else {
                Database::update('sms_queue', [
                    'status' => 'failed', 'error_message' => $result['error'] ?? 'Max retries exceeded',
                    'processed_at' => date('Y-m-d H:i:s'), 'locked_at' => null, 'locked_by' => null
                ], 'id = ?', [$msg['id']]);

                Database::insert('sms_logs', [
                    'user_id' => $msg['user_id'], 'campaign_id' => $msg['campaign_id'],
                    'gateway_id' => $gateway['id'], 'recipient' => $msg['recipient'],
                    'sender_id' => $msg['sender_id'], 'message' => $msg['message'],
                    'message_type' => $msg['message_type'], 'parts' => $msg['parts'],
                    'cost' => $msg['cost'], 'status' => 'failed',
                    'gateway_response' => json_encode($result), 'response_time' => $result['response_time'] ?? null,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

                SmsHelper::refundCredits($msg['user_id'], $msg['cost'], "SMS to {$msg['recipient']} failed", $msg['id']);
                echo "  Failed: {$msg['recipient']} - {$result['error']}\n";
            }
        }

        // Rate limit: brief pause between messages
        usleep(100000);
    }

    // Release stale locks (older than 5 minutes)
    Database::query("UPDATE sms_queue SET status = 'queued', locked_at = NULL, locked_by = NULL WHERE status = 'processing' AND locked_at < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");

} catch (\Exception $e) {
    Logger::error('Queue worker error: ' . $e->getMessage());
    echo "ERROR: " . $e->getMessage() . "\n";
} finally {
    if (file_exists($lockFile)) unlink($lockFile);
}

echo "[" . date('Y-m-d H:i:s') . "] Queue worker finished\n";
