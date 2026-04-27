<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

class LogController extends Controller
{
    public function audit(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Database::paginate("SELECT al.*, u.name as user_name FROM audit_logs al LEFT JOIN users u ON al.user_id = u.id ORDER BY al.created_at DESC", [], $page, 50);
        $this->layout('app', 'admin.logs.audit', ['pageTitle' => 'Audit Logs', 'logs' => $result['data'], 'pagination' => $result]);
    }

    public function system(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Database::paginate("SELECT * FROM system_logs ORDER BY created_at DESC", [], $page, 50);
        $this->layout('app', 'admin.logs.system', ['pageTitle' => 'System Logs', 'logs' => $result['data'], 'pagination' => $result]);
    }

    public function api(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Database::paginate("SELECT * FROM api_logs ORDER BY created_at DESC", [], $page, 50);
        $this->layout('app', 'admin.logs.api', ['pageTitle' => 'API Logs', 'logs' => $result['data'], 'pagination' => $result]);
    }
}
