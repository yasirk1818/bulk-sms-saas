<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Logger;

class UserController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $search = $_GET['search'] ?? '';
        $role = $_GET['role'] ?? '';
        $status = $_GET['status'] ?? '';

        $where = '1=1';
        $params = [];

        if ($search) { $where .= " AND (name LIKE ? OR email LIKE ?)"; $params[] = "%{$search}%"; $params[] = "%{$search}%"; }
        if ($role) { $where .= " AND role = ?"; $params[] = $role; }
        if ($status) { $where .= " AND status = ?"; $params[] = $status; }

        $result = Database::paginate("SELECT * FROM users WHERE {$where} ORDER BY id DESC", $params, $page, 20);

        $this->layout('app', 'admin.users.index', [
            'pageTitle' => 'User Management',
            'users' => $result['data'],
            'pagination' => $result,
            'search' => $search,
            'role' => $role,
            'statusFilter' => $status
        ]);
    }

    public function create(): void
    {
        $packages = Database::fetchAll("SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order");
        $this->layout('app', 'admin.users.create', ['pageTitle' => 'Create User', 'packages' => $packages]);
    }

    public function store(): void
    {
        Csrf::check();
        $errors = $this->validate($_POST, [
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role' => 'required'
        ]);

        if (!empty($errors)) {
            $this->redirect('/admin/users/create', ['error' => implode('. ', array_merge(...array_values($errors)))]);
            return;
        }

        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x', mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000, mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

        Database::insert('users', [
            'uuid' => $uuid,
            'name' => trim($_POST['name']),
            'email' => trim($_POST['email']),
            'phone' => trim($_POST['phone'] ?? ''),
            'password' => password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]),
            'role' => $_POST['role'],
            'status' => $_POST['status'] ?? 'active',
            'sms_balance' => (float)($_POST['sms_balance'] ?? 0),
            'package_id' => $_POST['package_id'] ?: null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        Logger::audit('admin.user.created', Session::userId(), ['email' => $_POST['email']]);
        $this->redirect('/admin/users', ['success' => 'User created successfully']);
    }

    public function show(string $id): void
    {
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [(int)$id]);
        if (!$user) { $this->redirect('/admin/users', ['error' => 'User not found']); return; }

        $loginLogs = Database::fetchAll("SELECT * FROM login_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10", [(int)$id]);
        $smsStats = [
            'total' => Database::count('sms_logs', 'user_id = ?', [(int)$id]),
            'delivered' => Database::count('sms_logs', "user_id = ? AND status = 'delivered'", [(int)$id]),
            'failed' => Database::count('sms_logs', "user_id = ? AND status = 'failed'", [(int)$id]),
        ];

        $this->layout('app', 'admin.users.show', ['pageTitle' => 'User Details', 'user' => $user, 'loginLogs' => $loginLogs, 'smsStats' => $smsStats]);
    }

    public function edit(string $id): void
    {
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [(int)$id]);
        if (!$user) { $this->redirect('/admin/users', ['error' => 'User not found']); return; }
        $packages = Database::fetchAll("SELECT * FROM packages WHERE is_active = 1 ORDER BY sort_order");
        $this->layout('app', 'admin.users.edit', ['pageTitle' => 'Edit User', 'user' => $user, 'packages' => $packages]);
    }

    public function update(string $id): void
    {
        Csrf::check();
        $data = ['name' => trim($_POST['name']), 'email' => trim($_POST['email']), 'phone' => trim($_POST['phone'] ?? ''), 'role' => $_POST['role'], 'status' => $_POST['status']];
        if (!empty($_POST['password'])) $data['password'] = password_hash($_POST['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        if (isset($_POST['sms_balance'])) $data['sms_balance'] = (float)$_POST['sms_balance'];
        if (isset($_POST['package_id'])) $data['package_id'] = $_POST['package_id'] ?: null;

        Database::update('users', $data, 'id = ?', [(int)$id]);
        Logger::audit('admin.user.updated', Session::userId(), ['user_id' => $id]);
        $this->redirect('/admin/users/' . $id, ['success' => 'User updated successfully']);
    }

    public function delete(string $id): void
    {
        Csrf::check();
        Database::delete('users', 'id = ? AND role != ?', [(int)$id, 'super_admin']);
        Logger::audit('admin.user.deleted', Session::userId(), ['user_id' => $id]);
        $this->redirect('/admin/users', ['success' => 'User deleted']);
    }

    public function toggleStatus(string $id): void
    {
        Csrf::check();
        $user = Database::fetch("SELECT status FROM users WHERE id = ?", [(int)$id]);
        $newStatus = $user['status'] === 'active' ? 'suspended' : 'active';
        Database::update('users', ['status' => $newStatus], 'id = ?', [(int)$id]);
        $this->redirect('/admin/users', ['success' => "User {$newStatus}"]);
    }

    public function addCredits(string $id): void
    {
        Csrf::check();
        $amount = (float)($_POST['amount'] ?? 0);
        if ($amount <= 0) { $this->redirect('/admin/users/' . $id, ['error' => 'Invalid amount']); return; }

        Database::beginTransaction();
        $user = Database::fetch("SELECT sms_balance FROM users WHERE id = ? FOR UPDATE", [(int)$id]);
        $newBalance = $user['sms_balance'] + $amount;
        Database::update('users', ['sms_balance' => $newBalance], 'id = ?', [(int)$id]);
        Database::insert('credit_logs', ['user_id' => (int)$id, 'type' => 'topup', 'amount' => $amount, 'balance_before' => $user['sms_balance'], 'balance_after' => $newBalance, 'description' => 'Admin credit addition', 'created_at' => date('Y-m-d H:i:s')]);
        Database::commit();

        Logger::audit('admin.credits.added', Session::userId(), ['user_id' => $id, 'amount' => $amount]);
        $this->redirect('/admin/users/' . $id, ['success' => "Added {$amount} credits"]);
    }
}
