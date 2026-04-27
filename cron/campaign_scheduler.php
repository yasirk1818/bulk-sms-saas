<?php
/**
 * Campaign Scheduler - Run every minute
 * Cron: * * * * * /usr/bin/php /path/to/cron/campaign_scheduler.php
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Config/config.php';
require_once ROOT_PATH . '/app/Core/Autoloader.php';

use App\Core\Database;
use App\Core\Logger;
use App\Helpers\SmsHelper;

echo "[" . date('Y-m-d H:i:s') . "] Campaign scheduler started\n";

// Find scheduled campaigns ready to run
$campaigns = Database::fetchAll(
    "SELECT * FROM campaigns WHERE status = 'scheduled' AND scheduled_at <= NOW()"
);

foreach ($campaigns as $campaign) {
    echo "  Processing campaign: {$campaign['name']} (ID: {$campaign['id']})\n";

    Database::update('campaigns', ['status' => 'running', 'started_at' => date('Y-m-d H:i:s')], 'id = ?', [$campaign['id']]);

    $groupIds = json_decode($campaign['group_ids'] ?? '[]', true);
    if (empty($groupIds)) {
        Database::update('campaigns', ['status' => 'completed', 'completed_at' => date('Y-m-d H:i:s')], 'id = ?', [$campaign['id']]);
        continue;
    }

    $gateway = $campaign['gateway_id'] ? Database::fetch("SELECT * FROM gateways WHERE id = ?", [$campaign['gateway_id']]) : SmsHelper::selectGateway();
    if (!$gateway) {
        Database::update('campaigns', ['status' => 'failed'], 'id = ?', [$campaign['id']]);
        continue;
    }

    $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
    $contacts = Database::fetchAll(
        "SELECT DISTINCT c.* FROM contacts c JOIN contact_group_members cgm ON c.id = cgm.contact_id WHERE cgm.group_id IN ({$placeholders}) AND c.user_id = ? AND c.is_blacklisted = 0 AND c.opt_out = 0",
        [...$groupIds, $campaign['user_id']]
    );

    $parts = SmsHelper::calculateParts($campaign['message']);
    $costPerSms = $parts * (float)$gateway['cost_per_sms'];
    $totalCost = $costPerSms * count($contacts);

    if (!SmsHelper::deductCredits($campaign['user_id'], $totalCost, "Campaign: {$campaign['name']}")) {
        Database::update('campaigns', ['status' => 'failed'], 'id = ?', [$campaign['id']]);
        continue;
    }

    $queued = 0;
    foreach ($contacts as $contact) {
        $msg = SmsHelper::personalizeMessage($campaign['message'], $contact);
        SmsHelper::queueSms([
            'user_id' => $campaign['user_id'], 'campaign_id' => $campaign['id'],
            'gateway_id' => $gateway['id'], 'sender_id' => $campaign['sender_id'] ?: $gateway['default_sender_id'],
            'recipient' => $contact['phone'], 'message' => $msg, 'cost' => $costPerSms, 'priority' => 3
        ]);
        $queued++;
    }

    Database::update('campaigns', ['total_recipients' => $queued, 'total_cost' => $totalCost], 'id = ?', [$campaign['id']]);
    echo "  Queued {$queued} messages for campaign {$campaign['id']}\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Campaign scheduler finished\n";
