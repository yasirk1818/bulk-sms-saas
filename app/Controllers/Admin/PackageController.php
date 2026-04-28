<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Csrf;
use App\Core\Logger;
use App\Core\Session;

class PackageController extends Controller
{
    public function index(): void
    {
        $packages = Database::fetchAll("SELECT * FROM packages ORDER BY sort_order, price");
        $this->layout('app', 'admin.packages.index', ['pageTitle' => 'Packages', 'packages' => $packages]);
    }

    public function create(): void
    {
        $this->layout('app', 'admin.packages.form', ['pageTitle' => 'Create Package', 'package' => null]);
    }

    public function store(): void
    {
        Csrf::check();
        Database::insert('packages', [
            'name' => trim($_POST['name']), 'slug' => strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($_POST['name']))),
            'description' => trim($_POST['description'] ?? ''), 'price' => (float)$_POST['price'],
            'sms_quota' => (int)$_POST['sms_quota'], 'validity_days' => (int)$_POST['validity_days'],
            'cost_per_sms' => (float)($_POST['cost_per_sms'] ?? 0.01),
            'features' => json_encode(array_filter(array_map('trim', explode("\n", $_POST['features'] ?? '')))),
            'is_active' => isset($_POST['is_active']) ? 1 : 0, 'is_trial' => isset($_POST['is_trial']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0),
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);
        Logger::audit('admin.package.created', Session::userId());
        $this->redirect('/admin/packages', ['success' => 'Package created']);
    }

    public function edit(string $id): void
    {
        $package = Database::fetch("SELECT * FROM packages WHERE id = ?", [(int)$id]);
        if (!$package) { $this->redirect('/admin/packages', ['error' => 'Not found']); return; }
        $this->layout('app', 'admin.packages.form', ['pageTitle' => 'Edit Package', 'package' => $package]);
    }

    public function update(string $id): void
    {
        Csrf::check();
        Database::update('packages', [
            'name' => trim($_POST['name']), 'description' => trim($_POST['description'] ?? ''),
            'price' => (float)$_POST['price'], 'sms_quota' => (int)$_POST['sms_quota'],
            'validity_days' => (int)$_POST['validity_days'], 'cost_per_sms' => (float)($_POST['cost_per_sms'] ?? 0.01),
            'features' => json_encode(array_filter(array_map('trim', explode("\n", $_POST['features'] ?? '')))),
            'is_active' => isset($_POST['is_active']) ? 1 : 0, 'is_trial' => isset($_POST['is_trial']) ? 1 : 0,
            'sort_order' => (int)($_POST['sort_order'] ?? 0)
        ], 'id = ?', [(int)$id]);
        $this->redirect('/admin/packages', ['success' => 'Package updated']);
    }

    public function delete(string $id): void
    {
        Csrf::check();
        Database::delete('packages', 'id = ?', [(int)$id]);
        $this->redirect('/admin/packages', ['success' => 'Package deleted']);
    }
}
