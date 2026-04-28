<?php
namespace App\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;

class ReportController extends Controller
{
    public function index(): void
    {
        $userId = Session::userId();
        $dateFrom = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $_GET['to'] ?? date('Y-m-d');

        $stats = [
            'sent' => Database::count('sms_logs', 'user_id = ? AND DATE(created_at) BETWEEN ? AND ?', [$userId, $dateFrom, $dateTo]),
            'delivered' => Database::count('sms_logs', "user_id = ? AND status = 'delivered' AND DATE(created_at) BETWEEN ? AND ?", [$userId, $dateFrom, $dateTo]),
            'failed' => Database::count('sms_logs', "user_id = ? AND status = 'failed' AND DATE(created_at) BETWEEN ? AND ?", [$userId, $dateFrom, $dateTo]),
        ];
        $stats['delivery_rate'] = $stats['sent'] > 0 ? round(($stats['delivered'] / $stats['sent']) * 100, 1) : 0;

        $costData = Database::fetch("SELECT COALESCE(SUM(cost), 0) as total FROM sms_logs WHERE user_id = ? AND DATE(created_at) BETWEEN ? AND ?", [$userId, $dateFrom, $dateTo]);

        $this->layout('app', 'user.reports', ['pageTitle' => 'Reports', 'stats' => $stats, 'totalCost' => $costData['total'], 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
    }

    public function export(): void
    {
        $userId = Session::userId();
        $logs = Database::fetchAll("SELECT recipient, message, status, parts, cost, sender_id, created_at FROM sms_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10000", [$userId]);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="sms_report_' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Recipient', 'Message', 'Status', 'Parts', 'Cost', 'Sender ID', 'Date']);
        foreach ($logs as $log) fputcsv($out, $log);
        fclose($out);
        exit;
    }
}
