<?php
/**
 * Subscription Checker - Run hourly
 * Cron: 0 * * * * /usr/bin/php /path/to/cron/subscription_checker.php
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Config/config.php';
require_once ROOT_PATH . '/app/Core/Autoloader.php';

use App\Core\Database;
use App\Core\Logger;

echo "[" . date('Y-m-d H:i:s') . "] Subscription checker started\n";

// Expire subscriptions
$expired = Database::fetchAll(
    "SELECT * FROM subscriptions WHERE status = 'active' AND expires_at < NOW()"
);

foreach ($expired as $sub) {
    Database::update('subscriptions', ['status' => 'expired'], 'id = ?', [$sub['id']]);
    Database::update('users', ['package_id' => null, 'sms_balance' => 0], 'id = ?', [$sub['user_id']]);

    Database::insert('notifications', [
        'user_id' => $sub['user_id'], 'type' => 'subscription_expired',
        'title' => 'Subscription Expired', 'message' => 'Your subscription has expired. Please renew to continue.',
        'created_at' => date('Y-m-d H:i:s')
    ]);
    echo "  Expired subscription for user {$sub['user_id']}\n";
}

// Low balance alerts
$lowBalanceUsers = Database::fetchAll("SELECT * FROM users WHERE sms_balance > 0 AND sms_balance <= 10 AND status = 'active'");
foreach ($lowBalanceUsers as $user) {
    $existing = Database::fetch("SELECT id FROM notifications WHERE user_id = ? AND type = 'low_balance' AND DATE(created_at) = CURDATE()", [$user['id']]);
    if (!$existing) {
        Database::insert('notifications', [
            'user_id' => $user['id'], 'type' => 'low_balance',
            'title' => 'Low Balance Alert', 'message' => "Your SMS balance is low ({$user['sms_balance']} remaining). Top up to avoid service interruption.",
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}

echo "[" . date('Y-m-d H:i:s') . "] Subscription checker finished\n";
