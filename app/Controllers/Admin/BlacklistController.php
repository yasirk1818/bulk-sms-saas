<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Csrf;
use App\Helpers\SmsHelper;

class BlacklistController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Database::paginate("SELECT * FROM blacklist ORDER BY id DESC", [], $page, 50);
        $this->layout('app', 'admin.blacklist', ['pageTitle' => 'Blacklist', 'items' => $result['data'], 'pagination' => $result]);
    }

    public function store(): void
    {
        Csrf::check();
        $numbers = preg_split('/[\r\n,;]+/', $_POST['numbers'] ?? '');
        $added = 0;
        foreach ($numbers as $num) {
            $phone = SmsHelper::cleanNumber(trim($num));
            if (!empty($phone)) {
                Database::insert('blacklist', ['phone' => $phone, 'reason' => trim($_POST['reason'] ?? ''), 'is_global' => 1, 'created_at' => date('Y-m-d H:i:s')]);
                $added++;
            }
        }
        $this->redirect('/admin/blacklist', ['success' => "{$added} numbers added to blacklist"]);
    }

    public function delete(string $id): void
    {
        Csrf::check();
        Database::delete('blacklist', 'id = ?', [(int)$id]);
        $this->redirect('/admin/blacklist', ['success' => 'Removed from blacklist']);
    }
}
