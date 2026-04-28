<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Csrf;

class BillingController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Database::paginate("SELECT t.*, u.name as user_name FROM transactions t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC", [], $page, 30);
        $totalRevenue = Database::fetch("SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE type = 'credit' AND status = 'completed'")['total'] ?? 0;
        $this->layout('app', 'admin.billing', ['pageTitle' => 'Billing & Transactions', 'transactions' => $result['data'], 'pagination' => $result, 'totalRevenue' => $totalRevenue]);
    }

    public function manualPayment(): void
    {
        Csrf::check();
        $userId = (int)$_POST['user_id'];
        $amount = (float)$_POST['amount'];
        $smsCredits = (float)$_POST['sms_credits'];

        Database::insert('transactions', [
            'user_id' => $userId, 'type' => 'credit', 'amount' => $amount,
            'description' => 'Manual payment by admin', 'payment_method' => 'manual',
            'status' => 'completed', 'created_at' => date('Y-m-d H:i:s')
        ]);

        Database::beginTransaction();
        $user = Database::fetch("SELECT sms_balance FROM users WHERE id = ? FOR UPDATE", [$userId]);
        $newBalance = $user['sms_balance'] + $smsCredits;
        Database::update('users', ['sms_balance' => $newBalance], 'id = ?', [$userId]);
        Database::insert('credit_logs', ['user_id' => $userId, 'type' => 'purchase', 'amount' => $smsCredits, 'balance_before' => $user['sms_balance'], 'balance_after' => $newBalance, 'description' => "Payment: $" . number_format($amount, 2), 'created_at' => date('Y-m-d H:i:s')]);
        Database::commit();

        $this->redirect('/admin/billing', ['success' => "Added {$smsCredits} credits for payment of \${$amount}"]);
    }
}
