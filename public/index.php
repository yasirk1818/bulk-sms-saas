<?php
/**
 * BulkSMS SaaS Platform - Entry Point
 * Enterprise-grade Bulk SMS Sender
 */

define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', __DIR__);
define('STORAGE_PATH', ROOT_PATH . '/storage');
define('START_TIME', microtime(true));

// Check if installed
if (!file_exists(ROOT_PATH . '/app/Config/installed.lock')) {
    // Redirect to installer
    $installPath = str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME']));
    header('Location: ' . $installPath . '/install/');
    exit;
}

// Load configuration
require_once ROOT_PATH . '/app/Config/config.php';
require_once ROOT_PATH . '/app/Core/Autoloader.php';

// Initialize autoloader
App\Core\Autoloader::register();

// Boot application
$app = new App\Core\Application();
$app->run();
