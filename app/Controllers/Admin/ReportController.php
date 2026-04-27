<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;

class ReportController extends Controller
{
    public function index(): void
    {
        $dateFrom = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $_GET['to'] ?? date('Y-m-d');

        $stats = [
            'total_sent' => Database::count('sms_logs', "DATE(created_at) BETWEEN ? AND ?", [$dateFrom, $dateTo]),
            'total_delivered' => Database::count('sms_logs', "status = 'delivered' AND DATE(created_at) BETWEEN ? AND ?", [$dateFrom, $dateTo]),
            'total_failed' => Database::count('sms_logs', "status = 'failed' AND DATE(created_at) BETWEEN ? AND ?", [$dateFrom, $dateTo]),
            'total_revenue' => Database::fetch("SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE type = 'credit' AND DATE(created_at) BETWEEN ? AND ?", [$dateFrom, $dateTo])['total'] ?? 0,
            'new_users' => Database::count('users', "DATE(created_at) BETWEEN ? AND ?", [$dateFrom, $dateTo]),
        ];

        $topUsers = Database::fetchAll("SELECT u.name, u.email, COUNT(l.id) as sms_count, SUM(l.cost) as total_cost FROM sms_logs l JOIN users u ON l.user_id = u.id WHERE DATE(l.created_at) BETWEEN ? AND ? GROUP BY l.user_id ORDER BY sms_count DESC LIMIT 10", [$dateFrom, $dateTo]);
        $topGateways = Database::fetchAll("SELECT g.name, COUNT(l.id) as sms_count, SUM(CASE WHEN l.status='delivered' THEN 1 ELSE 0 END) as delivered FROM sms_logs l JOIN gateways g ON l.gateway_id = g.id WHERE DATE(l.created_at) BETWEEN ? AND ? GROUP BY l.gateway_id ORDER BY sms_count DESC", [$dateFrom, $dateTo]);

        $this->layout('app', 'admin.reports', ['pageTitle' => 'Reports', 'stats' => $stats, 'topUsers' => $topUsers, 'topGateways' => $topGateways, 'dateFrom' => $dateFrom, 'dateTo' => $dateTo]);
    }

    public function export(): void
    {
        $type = $_GET['type'] ?? 'sms';
        $dateFrom = $_GET['from'] ?? date('Y-m-d', strtotime('-30 days'));
        $dateTo = $_GET['to'] ?? date('Y-m-d');

        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"{$type}_report_{$dateFrom}_to_{$dateTo}.csv\"");
        $out = fopen('php://output', 'w');

        if ($type === 'sms') {
            fputcsv($out, ['Recipient', 'User', 'Gateway', 'Status', 'Parts', 'Cost', 'Sent At']);
            $logs = Database::fetchAll("SELECT l.recipient, u.email, g.name as gateway, l.status, l.parts, l.cost, l.created_at FROM sms_logs l LEFT JOIN users u ON l.user_id = u.id LEFT JOIN gateways g ON l.gateway_id = g.id WHERE DATE(l.created_at) BETWEEN ? AND ? ORDER BY l.created_at DESC", [$dateFrom, $dateTo]);
            foreach ($logs as $log) fputcsv($out, $log);
        } elseif ($type === 'revenue') {
            fputcsv($out, ['User', 'Type', 'Amount', 'Description', 'Date']);
            $txns = Database::fetchAll("SELECT u.email, t.type, t.amount, t.description, t.created_at FROM transactions t JOIN users u ON t.user_id = u.id WHERE DATE(t.created_at) BETWEEN ? AND ? ORDER BY t.created_at DESC", [$dateFrom, $dateTo]);
            foreach ($txns as $txn) fputcsv($out, $txn);
        }

        fclose($out);
        exit;
    }
}
