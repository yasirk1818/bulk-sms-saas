<?php
namespace App\Controllers\Api;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Logger;
use App\Helpers\SmsHelper;

class ContactApiController extends Controller
{
    private ?array $user = null;

    private function authenticate(): bool
    {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? $_GET['api_key'] ?? '';
        $key = Database::fetch("SELECT ak.*, u.status FROM api_keys ak JOIN users u ON ak.user_id = u.id WHERE ak.api_key = ? AND ak.is_active = 1 AND u.status = 'active'", [$apiKey]);
        if (!$key) { $this->json(['error' => 'Unauthorized'], 401); return false; }
        $this->user = $key;
        return true;
    }

    public function index(): void
    {
        if (!$this->authenticate()) return;
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Database::paginate("SELECT id, phone, name, email, country_code FROM contacts WHERE user_id = ? ORDER BY id DESC", [$this->user['user_id']], $page, 50);
        $this->json($result);
    }

    public function store(): void
    {
        if (!$this->authenticate()) return;
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $phone = SmsHelper::cleanNumber($input['phone'] ?? '');
        if (!SmsHelper::validateNumber($phone)) { $this->json(['error' => 'Invalid phone'], 400); return; }

        $id = Database::insert('contacts', [
            'user_id' => $this->user['user_id'], 'phone' => $phone,
            'name' => $input['name'] ?? '', 'email' => $input['email'] ?? '',
            'country_code' => SmsHelper::detectCountry($phone),
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);
        $this->json(['success' => true, 'id' => $id]);
    }

    public function groups(): void
    {
        if (!$this->authenticate()) return;
        $groups = Database::fetchAll("SELECT id, name, contacts_count FROM contact_groups WHERE user_id = ?", [$this->user['user_id']]);
        $this->json(['groups' => $groups]);
    }
}
