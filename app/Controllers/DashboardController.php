<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;

class DashboardController extends Controller
{
    public function index(): void
    {
        $userId = Session::userId();

        $stats = [
            'total_sent' => Database::count('sms_logs', 'user_id = ?', [$userId]),
            'total_delivered' => Database::count('sms_logs', 'user_id = ? AND status = ?', [$userId, 'delivered']),
            'total_failed' => Database::count('sms_logs', 'user_id = ? AND status = ?', [$userId, 'failed']),
            'total_queued' => Database::count('sms_queue', 'user_id = ? AND status = ?', [$userId, 'queued']),
            'total_contacts' => Database::count('contacts', 'user_id = ?', [$userId]),
            'total_campaigns' => Database::count('campaigns', 'user_id = ?', [$userId]),
            'balance' => Session::getUser()['sms_balance'] ?? 0,
        ];

        // Recent SMS
        $recentSms = Database::fetchAll(
            "SELECT * FROM sms_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10",
            [$userId]
        );

        // Recent Campaigns
        $recentCampaigns = Database::fetchAll(
            "SELECT * FROM campaigns WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
            [$userId]
        );

        // SMS stats for chart (last 7 days)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $sent = Database::count('sms_logs', 'user_id = ? AND DATE(created_at) = ?', [$userId, $date]);
            $delivered = Database::count('sms_logs', 'user_id = ? AND DATE(created_at) = ? AND status = ?', [$userId, $date, 'delivered']);
            $chartData[] = [
                'date' => date('M d', strtotime($date)),
                'sent' => $sent,
                'delivered' => $delivered
            ];
        }

        $this->layout('app', 'user.dashboard', [
            'pageTitle' => 'Dashboard',
            'stats' => $stats,
            'recentSms' => $recentSms,
            'recentCampaigns' => $recentCampaigns,
            'chartData' => $chartData
        ]);
    }
}
