<?php
namespace App\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;

class TemplateController extends Controller
{
    public function index(): void
    {
        $templates = Database::fetchAll("SELECT * FROM sms_templates WHERE user_id = ? ORDER BY id DESC", [Session::userId()]);
        $this->layout('app', 'user.templates', ['pageTitle' => 'SMS Templates', 'templates' => $templates]);
    }

    public function store(): void
    {
        Csrf::check();
        $approvalRequired = Database::fetch("SELECT setting_value FROM settings WHERE setting_key = 'template_approval'");
        $status = ($approvalRequired && $approvalRequired['setting_value'] === '1') ? 'pending' : 'approved';

        Database::insert('sms_templates', [
            'user_id' => Session::userId(), 'name' => trim($_POST['name']),
            'content' => trim($_POST['content']), 'category' => trim($_POST['category'] ?? ''),
            'status' => $status, 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);
        $this->redirect('/templates', ['success' => $status === 'pending' ? 'Template submitted for approval' : 'Template created']);
    }

    public function update(string $id): void
    {
        Csrf::check();
        Database::update('sms_templates', [
            'name' => trim($_POST['name']), 'content' => trim($_POST['content']),
            'category' => trim($_POST['category'] ?? ''), 'status' => 'pending',
        ], 'id = ? AND user_id = ?', [(int)$id, Session::userId()]);
        $this->redirect('/templates', ['success' => 'Template updated']);
    }

    public function delete(string $id): void
    {
        Csrf::check();
        Database::delete('sms_templates', 'id = ? AND user_id = ?', [(int)$id, Session::userId()]);
        $this->redirect('/templates', ['success' => 'Template deleted']);
    }
}
