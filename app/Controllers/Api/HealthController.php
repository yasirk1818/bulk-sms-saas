<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;

class HealthController extends Controller
{
    public function check(): void
    {
        try {
            Database::fetch("SELECT 1");
            $this->json([
                'status' => 'healthy',
                'version' => defined('APP_VERSION') ? APP_VERSION : '1.0.0',
                'timestamp' => date('c')
            ]);
        } catch (\Exception $e) {
            $this->json(['status' => 'unhealthy', 'error' => 'Database connection failed'], 503);
        }
    }
}
