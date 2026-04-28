<?php
namespace App\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;

class WebhookController extends Controller
{
    public function index(): void
    {
        $webhooks = Database::fetchAll("SELECT * FROM webhooks WHERE user_id = ? ORDER BY id DESC", [Session::userId()]);
        $this->layout('app', 'user.webhooks', ['pageTitle' => 'Webhooks', 'webhooks' => $webhooks]);
    }

    public function store(): void
    {
        Csrf::check();
        Database::insert('webhooks', [
            'user_id' => Session::userId(), 'url' => trim($_POST['url']),
            'events' => json_encode($_POST['events'] ?? []),
            'secret' => bin2hex(random_bytes(16)), 'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);
        $this->redirect('/webhooks', ['success' => 'Webhook created']);
    }

    public function update(string $id): void
    {
        Csrf::check();
        Database::update('webhooks', [
            'url' => trim($_POST['url']), 'events' => json_encode($_POST['events'] ?? []),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ], 'id = ? AND user_id = ?', [(int)$id, Session::userId()]);
        $this->redirect('/webhooks', ['success' => 'Webhook updated']);
    }

    public function delete(string $id): void
    {
        Csrf::check();
        Database::delete('webhooks', 'id = ? AND user_id = ?', [(int)$id, Session::userId()]);
        $this->redirect('/webhooks', ['success' => 'Webhook deleted']);
    }
}
