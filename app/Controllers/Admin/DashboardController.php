<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

class DashboardController extends Controller
{
    public function index(): void
    {
        $stats = [
            'total_users' => Database::count('users', 'role != ?', ['super_admin']),
            'active_users' => Database::count('users', "status = 'active' AND role != 'super_admin'"),
            'total_sms_sent' => Database::count('sms_logs'),
            'total_delivered' => Database::count('sms_logs', "status = 'delivered'"),
            'total_failed' => Database::count('sms_logs', "status = 'failed'"),
            'total_queued' => Database::count('sms_queue', "status = 'queued'"),
            'total_processing' => Database::count('sms_queue', "status = 'processing'"),
            'total_campaigns' => Database::count('campaigns'),
            'active_campaigns' => Database::count('campaigns', "status = 'running'"),
            'total_revenue' => Database::fetch("SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE type = 'credit' AND status = 'completed'")['total'] ?? 0,
            'active_gateways' => Database::count('gateways', "status = 'active'"),
            'total_gateways' => Database::count('gateways'),
            'pending_tickets' => Database::count('tickets', "status IN ('open','in_progress')"),
            'pending_templates' => Database::count('sms_templates', "status = 'pending'"),
            'pending_sender_ids' => Database::count('sender_ids', "status = 'pending'"),
        ];

        // Recent users
        $recentUsers = Database::fetchAll("SELECT id, name, email, role, status, created_at FROM users ORDER BY id DESC LIMIT 5");

        // Active gateways
        $gateways = Database::fetchAll("SELECT * FROM gateways WHERE status = 'active' ORDER BY priority ASC LIMIT 10");

        // Queue summary
        $queueSummary = Database::fetchAll("SELECT status, COUNT(*) as count FROM sms_queue GROUP BY status");

        // SMS chart data (last 7 days)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $sent = Database::count('sms_logs', 'DATE(created_at) = ?', [$date]);
            $delivered = Database::count('sms_logs', "DATE(created_at) = ? AND status = 'delivered'", [$date]);
            $failed = Database::count('sms_logs', "DATE(created_at) = ? AND status = 'failed'", [$date]);
            $chartData[] = ['date' => date('M d', strtotime($date)), 'sent' => $sent, 'delivered' => $delivered, 'failed' => $failed];
        }

        // Revenue chart (last 7 days)
        $revenueData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $rev = Database::fetch("SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE type = 'credit' AND DATE(created_at) = ?", [$date]);
            $revenueData[] = ['date' => date('M d', strtotime($date)), 'revenue' => (float)($rev['total'] ?? 0)];
        }

        $this->layout('app', 'admin.dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'gateways' => $gateways,
            'queueSummary' => $queueSummary,
            'chartData' => $chartData,
            'revenueData' => $revenueData
        ]);
    }
}
