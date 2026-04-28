<?php
/**
 * Gateway Health Monitor - Run every 5 minutes
 * Cron: */5 * * * * /usr/bin/php /path/to/cron/gateway_health.php
 */

define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/app/Config/config.php';
require_once ROOT_PATH . '/app/Core/Autoloader.php';

use App\Core\Database;
use App\Core\Logger;
use App\Helpers\GatewayHelper;

echo "[" . date('Y-m-d H:i:s') . "] Gateway health check started\n";

$gateways = Database::fetchAll("SELECT * FROM gateways WHERE status IN ('active', 'inactive')");

foreach ($gateways as $gw) {
    $health = GatewayHelper::checkHealth($gw);

    Database::insert('gateway_health_logs', [
        'gateway_id' => $gw['id'], 'status' => $health['status'],
        'response_time' => $health['response_time'] ?? null,
        'error_message' => $health['error'] ?? null,
        'checked_at' => date('Y-m-d H:i:s')
    ]);

    if ($health['status'] === 'down' && $gw['status'] === 'active') {
        $recentFails = Database::count('gateway_health_logs', "gateway_id = ? AND status = 'down' AND checked_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)", [$gw['id']]);
        if ($recentFails >= 3) {
            Database::update('gateways', ['status' => 'inactive'], 'id = ?', [$gw['id']]);
            Database::insert('notifications', [
                'user_id' => null, 'is_global' => 1, 'type' => 'gateway_down',
                'title' => "Gateway Down: {$gw['name']}", 'message' => "Gateway {$gw['name']} has been auto-disabled due to repeated failures.",
                'created_at' => date('Y-m-d H:i:s')
            ]);
            echo "  DISABLED gateway: {$gw['name']}\n";
        }
    }

    echo "  {$gw['name']}: {$health['status']} ({$health['response_time']}ms)\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Gateway health check finished\n";
