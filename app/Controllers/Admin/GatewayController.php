<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Logger;
use App\Helpers\GatewayHelper;

class GatewayController extends Controller
{
    public function index(): void
    {
        $gateways = Database::fetchAll("SELECT * FROM gateways ORDER BY priority ASC");
        $this->layout('app', 'admin.gateways.index', ['pageTitle' => 'Gateway Management', 'gateways' => $gateways]);
    }

    public function create(): void
    {
        $this->layout('app', 'admin.gateways.form', ['pageTitle' => 'Add Gateway', 'gateway' => null]);
    }

    public function store(): void
    {
        Csrf::check();
        Database::insert('gateways', [
            'name' => trim($_POST['name']),
            'slug' => strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($_POST['name']))),
            'type' => $_POST['type'],
            'api_url' => trim($_POST['api_url']),
            'api_key' => trim($_POST['api_key'] ?? ''),
            'api_secret' => trim($_POST['api_secret'] ?? ''),
            'recipient_param' => trim($_POST['recipient_param'] ?? ''),
            'message_param' => trim($_POST['message_param'] ?? ''),
            'sender_id_param' => trim($_POST['sender_id_param'] ?? ''),
            'default_sender_id' => trim($_POST['default_sender_id'] ?? ''),
            'cost_per_sms' => (float)($_POST['cost_per_sms'] ?? 0.01),
            'rate_limit' => (int)($_POST['rate_limit'] ?? 30),
            'timeout' => (int)($_POST['timeout'] ?? 30),
            'retry_attempts' => (int)($_POST['retry_attempts'] ?? 3),
            'priority' => (int)($_POST['priority'] ?? 50),
            'status' => $_POST['status'] ?? 'inactive',
            'extra_params' => $_POST['extra_params'] ?? '{}',
            'headers' => $_POST['headers'] ?? '{}',
            'supports_unicode' => isset($_POST['supports_unicode']) ? 1 : 0,
            'supports_dlr' => isset($_POST['supports_dlr']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        Logger::audit('admin.gateway.created', Session::userId(), ['name' => $_POST['name']]);
        $this->redirect('/admin/gateways', ['success' => 'Gateway added successfully']);
    }

    public function edit(string $id): void
    {
        $gateway = Database::fetch("SELECT * FROM gateways WHERE id = ?", [(int)$id]);
        if (!$gateway) { $this->redirect('/admin/gateways', ['error' => 'Gateway not found']); return; }
        $this->layout('app', 'admin.gateways.form', ['pageTitle' => 'Edit Gateway', 'gateway' => $gateway]);
    }

    public function update(string $id): void
    {
        Csrf::check();
        Database::update('gateways', [
            'name' => trim($_POST['name']),
            'type' => $_POST['type'],
            'api_url' => trim($_POST['api_url']),
            'api_key' => trim($_POST['api_key'] ?? ''),
            'api_secret' => trim($_POST['api_secret'] ?? ''),
            'recipient_param' => trim($_POST['recipient_param'] ?? ''),
            'message_param' => trim($_POST['message_param'] ?? ''),
            'sender_id_param' => trim($_POST['sender_id_param'] ?? ''),
            'default_sender_id' => trim($_POST['default_sender_id'] ?? ''),
            'cost_per_sms' => (float)($_POST['cost_per_sms'] ?? 0.01),
            'rate_limit' => (int)($_POST['rate_limit'] ?? 30),
            'timeout' => (int)($_POST['timeout'] ?? 30),
            'retry_attempts' => (int)($_POST['retry_attempts'] ?? 3),
            'priority' => (int)($_POST['priority'] ?? 50),
            'status' => $_POST['status'] ?? 'inactive',
            'extra_params' => $_POST['extra_params'] ?? '{}',
            'headers' => $_POST['headers'] ?? '{}',
            'supports_unicode' => isset($_POST['supports_unicode']) ? 1 : 0,
            'supports_dlr' => isset($_POST['supports_dlr']) ? 1 : 0,
        ], 'id = ?', [(int)$id]);
        Logger::audit('admin.gateway.updated', Session::userId(), ['gateway_id' => $id]);
        $this->redirect('/admin/gateways', ['success' => 'Gateway updated']);
    }

    public function delete(string $id): void
    {
        Csrf::check();
        Database::delete('gateways', 'id = ?', [(int)$id]);
        $this->redirect('/admin/gateways', ['success' => 'Gateway deleted']);
    }

    public function toggle(string $id): void
    {
        Csrf::check();
        $gw = Database::fetch("SELECT status FROM gateways WHERE id = ?", [(int)$id]);
        $newStatus = $gw['status'] === 'active' ? 'inactive' : 'active';
        Database::update('gateways', ['status' => $newStatus], 'id = ?', [(int)$id]);
        $this->redirect('/admin/gateways', ['success' => "Gateway {$newStatus}"]);
    }

    public function test(string $id): void
    {
        $gateway = Database::fetch("SELECT * FROM gateways WHERE id = ?", [(int)$id]);
        $health = GatewayHelper::checkHealth($gateway);
        Database::insert('gateway_health_logs', [
            'gateway_id' => (int)$id,
            'status' => $health['status'],
            'response_time' => $health['response_time'] ?? null,
            'error_message' => $health['error'] ?? null,
            'checked_at' => date('Y-m-d H:i:s')
        ]);
        $this->json($health);
    }
}
