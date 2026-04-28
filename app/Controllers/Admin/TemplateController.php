<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Csrf;

class TemplateController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $where = '1=1';
        $params = [];
        if ($status) { $where .= ' AND t.status = ?'; $params[] = $status; }

        $result = Database::paginate("SELECT t.*, u.name as user_name FROM sms_templates t JOIN users u ON t.user_id = u.id WHERE {$where} ORDER BY t.id DESC", $params, $page, 20);
        $this->layout('app', 'admin.templates', ['pageTitle' => 'Template Approval', 'templates' => $result['data'], 'pagination' => $result, 'status' => $status]);
    }

    public function approve(string $id): void
    {
        Csrf::check();
        Database::update('sms_templates', ['status' => 'approved', 'reviewed_at' => date('Y-m-d H:i:s'), 'reviewed_by' => \App\Core\Session::userId()], 'id = ?', [(int)$id]);
        $this->redirect('/admin/templates', ['success' => 'Template approved']);
    }

    public function reject(string $id): void
    {
        Csrf::check();
        Database::update('sms_templates', ['status' => 'rejected', 'rejection_reason' => trim($_POST['reason'] ?? ''), 'reviewed_at' => date('Y-m-d H:i:s'), 'reviewed_by' => \App\Core\Session::userId()], 'id = ?', [(int)$id]);
        $this->redirect('/admin/templates', ['success' => 'Template rejected']);
    }
}
