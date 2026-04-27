<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Csrf;

class SenderIdController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Database::paginate("SELECT s.*, u.name as user_name FROM sender_ids s JOIN users u ON s.user_id = u.id ORDER BY s.id DESC", [], $page, 20);
        $this->layout('app', 'admin.sender-ids', ['pageTitle' => 'Sender ID Approval', 'senderIds' => $result['data'], 'pagination' => $result]);
    }

    public function approve(string $id): void
    {
        Csrf::check();
        Database::update('sender_ids', ['status' => 'approved', 'approved_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$id]);
        $this->redirect('/admin/sender-ids', ['success' => 'Sender ID approved']);
    }

    public function reject(string $id): void
    {
        Csrf::check();
        Database::update('sender_ids', ['status' => 'rejected', 'rejection_reason' => trim($_POST['reason'] ?? '')], 'id = ?', [(int)$id]);
        $this->redirect('/admin/sender-ids', ['success' => 'Sender ID rejected']);
    }
}
