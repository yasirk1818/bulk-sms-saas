<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Csrf;
use App\Core\Logger;
use App\Core\Session;

class SettingsController extends Controller
{
    public function index(): void
    {
        $settings = [];
        $rows = Database::fetchAll("SELECT * FROM settings ORDER BY setting_group, id");
        foreach ($rows as $row) $settings[$row['setting_key']] = $row['setting_value'];

        $groups = [];
        foreach ($rows as $row) $groups[$row['setting_group'] ?? 'general'][] = $row;

        $this->layout('app', 'admin.settings', ['pageTitle' => 'System Settings', 'settings' => $settings, 'groups' => $groups]);
    }

    public function update(): void
    {
        Csrf::check();
        foreach ($_POST as $key => $value) {
            if ($key === '_csrf_token') continue;
            Database::query("UPDATE settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?", [$value, $key]);
        }
        Logger::audit('admin.settings.updated', Session::userId());
        $this->redirect('/admin/settings', ['success' => 'Settings updated successfully']);
    }

    public function maintenance(): void
    {
        Csrf::check();
        $enabled = $_POST['maintenance_mode'] ?? '0';
        Database::query("UPDATE settings SET setting_value = ? WHERE setting_key = 'maintenance_mode'", [$enabled]);
        $this->redirect('/admin/settings', ['success' => $enabled === '1' ? 'Maintenance mode enabled' : 'Maintenance mode disabled']);
    }
}
