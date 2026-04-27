<?php
namespace App\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;

class ApiKeyController extends Controller
{
    public function index(): void
    {
        $keys = Database::fetchAll("SELECT * FROM api_keys WHERE user_id = ? ORDER BY id DESC", [Session::userId()]);
        $this->layout('app', 'user.api-keys', ['pageTitle' => 'API Keys', 'keys' => $keys]);
    }

    public function generate(): void
    {
        Csrf::check();
        $apiKey = bin2hex(random_bytes(24));
        $apiSecret = bin2hex(random_bytes(32));

        Database::insert('api_keys', [
            'user_id' => Session::userId(), 'name' => trim($_POST['name'] ?? 'Default'),
            'api_key' => $apiKey, 'api_secret' => password_hash($apiSecret, PASSWORD_BCRYPT),
            'rate_limit' => 60, 'is_active' => 1,
            'ip_whitelist' => trim($_POST['ip_whitelist'] ?? ''),
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);

        Session::flash('new_api_key', $apiKey);
        Session::flash('new_api_secret', $apiSecret);
        $this->redirect('/api-keys', ['success' => 'API key generated. Save the secret - it won\'t be shown again.']);
    }

    public function revoke(string $id): void
    {
        Csrf::check();
        Database::update('api_keys', ['is_active' => 0], 'id = ? AND user_id = ?', [(int)$id, Session::userId()]);
        $this->redirect('/api-keys', ['success' => 'API key revoked']);
    }
}
