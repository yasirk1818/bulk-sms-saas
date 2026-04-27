<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Csrf;

class AnnouncementController extends Controller
{
    public function index(): void
    {
        $announcements = Database::fetchAll("SELECT * FROM announcements ORDER BY id DESC");
        $this->layout('app', 'admin.announcements', ['pageTitle' => 'Announcements', 'announcements' => $announcements]);
    }

    public function store(): void
    {
        Csrf::check();
        Database::insert('announcements', [
            'title' => trim($_POST['title']), 'content' => trim($_POST['content']),
            'type' => $_POST['type'] ?? 'info', 'is_active' => 1,
            'created_by' => \App\Core\Session::userId(), 'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->redirect('/admin/announcements', ['success' => 'Announcement created']);
    }

    public function delete(string $id): void
    {
        Csrf::check();
        Database::delete('announcements', 'id = ?', [(int)$id]);
        $this->redirect('/admin/announcements', ['success' => 'Announcement deleted']);
    }
}
