<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Csrf;
use App\Core\Logger;
use App\Core\Session;

class GdprController extends Controller
{
    public function index(): void
    {
        $requests = Database::fetchAll("SELECT gdr.*, u.name as user_name, u.email as user_email FROM gdpr_data_requests gdr JOIN users u ON gdr.user_id = u.id ORDER BY gdr.created_at DESC");
        $consentLogs = Database::fetchAll("SELECT gcl.*, u.name as user_name FROM gdpr_consent_logs gcl JOIN users u ON gcl.user_id = u.id ORDER BY gcl.created_at DESC LIMIT 50");
        $this->layout('app', 'admin.gdpr', ['pageTitle' => 'GDPR Management', 'requests' => $requests, 'consentLogs' => $consentLogs]);
    }

    public function processRequest(string $id): void
    {
        Csrf::check();
        $request = Database::fetch("SELECT * FROM gdpr_data_requests WHERE id = ?", [(int)$id]);
        if (!$request) { $this->redirect('/admin/gdpr', ['error' => 'Request not found']); return; }

        if ($request['request_type'] === 'export') {
            $userData = $this->exportUserData($request['user_id']);
            $filename = 'gdpr_export_' . $request['user_id'] . '_' . date('Ymd') . '.json';
            $filepath = ROOT_PATH . '/storage/exports/' . $filename;
            if (!is_dir(dirname($filepath))) mkdir(dirname($filepath), 0755, true);
            file_put_contents($filepath, json_encode($userData, JSON_PRETTY_PRINT));
            Database::update('gdpr_data_requests', ['status' => 'completed', 'processed_by' => Session::userId(), 'processed_at' => date('Y-m-d H:i:s'), 'notes' => "Export file: {$filename}"], 'id = ?', [(int)$id]);
        } elseif ($request['request_type'] === 'deletion') {
            $this->deleteUserData($request['user_id']);
            Database::update('gdpr_data_requests', ['status' => 'completed', 'processed_by' => Session::userId(), 'processed_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$id]);
        }

        Logger::audit('admin.gdpr.processed', Session::userId(), ['request_id' => $id, 'type' => $request['request_type']]);
        $this->redirect('/admin/gdpr', ['success' => 'GDPR request processed']);
    }

    private function exportUserData(int $userId): array
    {
        return [
            'user' => Database::fetch("SELECT id, name, email, phone, role, created_at FROM users WHERE id = ?", [$userId]),
            'contacts' => Database::fetchAll("SELECT phone, name, email FROM contacts WHERE user_id = ?", [$userId]),
            'sms_logs' => Database::fetchAll("SELECT recipient, message, status, created_at FROM sms_logs WHERE user_id = ? ORDER BY created_at DESC", [$userId]),
            'login_history' => Database::fetchAll("SELECT ip_address, device_type, browser, os, created_at FROM login_logs WHERE user_id = ?", [$userId]),
            'exported_at' => date('c')
        ];
    }

    private function deleteUserData(int $userId): void
    {
        Database::delete('sms_logs', 'user_id = ?', [$userId]);
        Database::delete('sms_queue', 'user_id = ?', [$userId]);
        Database::delete('contacts', 'user_id = ?', [$userId]);
        Database::delete('contact_groups', 'user_id = ?', [$userId]);
        Database::delete('campaigns', 'user_id = ?', [$userId]);
        Database::delete('sms_templates', 'user_id = ?', [$userId]);
        Database::delete('login_logs', 'user_id = ?', [$userId]);
        Database::delete('api_keys', 'user_id = ?', [$userId]);
        Database::delete('webhooks', 'user_id = ?', [$userId]);
        Database::update('users', ['name' => 'Deleted User', 'email' => 'deleted_' . $userId . '@deleted.com', 'phone' => '', 'status' => 'deleted', 'sms_balance' => 0], 'id = ?', [$userId]);
    }
}
