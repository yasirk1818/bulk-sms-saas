<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;

class AjaxController extends Controller
{
    public function updateTheme(): void
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $theme = ($input['theme'] ?? '') === 'light' ? 'light' : 'dark';
        if (Session::userId()) {
            Database::update('users', ['theme' => $theme], 'id = ?', [Session::userId()]);
            $user = Session::getUser();
            $user['theme'] = $theme;
            Session::setUser($user);
        }
        $this->json(['success' => true]);
    }

    public function unreadNotificationCount(): void
    {
        $count = 0;
        if (Session::userId()) {
            $count = Database::count('notifications', '(user_id = ? OR is_global = 1) AND is_read = 0', [Session::userId()]);
        }
        $this->json(['count' => $count]);
    }

    public function getNotifications(): void
    {
        $notifications = Database::fetchAll(
            "SELECT * FROM notifications WHERE (user_id = ? OR is_global = 1) ORDER BY created_at DESC LIMIT 20",
            [Session::userId()]
        );
        $this->json(['notifications' => $notifications]);
    }

    public function markNotificationsRead(): void
    {
        Database::update('notifications', ['is_read' => 1], 'user_id = ? AND is_read = 0', [Session::userId()]);
        $this->json(['success' => true]);
    }

    public function dashboardStats(): void
    {
        $userId = Session::userId();
        $today = date('Y-m-d');
        $this->json([
            'today_sent' => Database::count('sms_logs', 'user_id = ? AND DATE(created_at) = ?', [$userId, $today]),
            'today_delivered' => Database::count('sms_logs', "user_id = ? AND DATE(created_at) = ? AND status = 'delivered'", [$userId, $today]),
            'queued' => Database::count('sms_queue', "user_id = ? AND status = 'queued'", [$userId]),
        ]);
    }
}
