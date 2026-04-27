<?php
namespace App\Middleware;

use App\Core\Session;

class MaintenanceMiddleware
{
    public function handle(): void
    {
        $maintenanceFile = STORAGE_PATH . '/cache/maintenance.flag';
        if (!file_exists($maintenanceFile)) {
            return;
        }

        // Allow admin access during maintenance
        if (Session::isLoggedIn() && in_array(Session::userRole(), ['super_admin', 'admin'])) {
            return;
        }

        // Allow access to login page
        $uri = $_GET['url'] ?? '';
        if (in_array($uri, ['login', 'api/health'])) {
            return;
        }

        http_response_code(503);
        include APP_PATH . '/Views/errors/maintenance.php';
        exit;
    }
}
