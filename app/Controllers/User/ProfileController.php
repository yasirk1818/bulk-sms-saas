<?php
namespace App\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Logger;

class ProfileController extends Controller
{
    public function index(): void
    {
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [Session::userId()]);
        $loginLogs = Database::fetchAll("SELECT * FROM login_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10", [Session::userId()]);
        $this->layout('app', 'user.profile', ['pageTitle' => 'Profile', 'user' => $user, 'loginLogs' => $loginLogs]);
    }

    public function update(): void
    {
        Csrf::check();
        Database::update('users', [
            'name' => trim($_POST['name']), 'phone' => trim($_POST['phone'] ?? ''),
            'timezone' => $_POST['timezone'] ?? 'UTC', 'language' => $_POST['language'] ?? 'en',
        ], 'id = ?', [Session::userId()]);

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [Session::userId()]);
        unset($user['password'], $user['two_factor_secret']);
        Session::setUser($user);

        Logger::audit('profile.updated', Session::userId());
        $this->redirect('/profile', ['success' => 'Profile updated']);
    }

    public function changePassword(): void
    {
        Csrf::check();
        $user = Database::fetch("SELECT password FROM users WHERE id = ?", [Session::userId()]);

        if (!password_verify($_POST['current_password'] ?? '', $user['password'])) {
            $this->redirect('/profile', ['error' => 'Current password is incorrect']);
            return;
        }
        if (strlen($_POST['new_password'] ?? '') < 8) {
            $this->redirect('/profile', ['error' => 'New password must be at least 8 characters']);
            return;
        }
        if ($_POST['new_password'] !== $_POST['new_password_confirmation']) {
            $this->redirect('/profile', ['error' => 'Passwords do not match']);
            return;
        }

        Database::update('users', ['password' => password_hash($_POST['new_password'], PASSWORD_BCRYPT, ['cost' => 12])], 'id = ?', [Session::userId()]);
        Logger::audit('password.changed', Session::userId());
        $this->redirect('/profile', ['success' => 'Password changed successfully']);
    }
}
