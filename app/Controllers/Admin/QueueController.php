<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Csrf;

class QueueController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $where = '1=1';
        $params = [];
        if ($status) { $where .= ' AND sq.status = ?'; $params[] = $status; }

        $result = Database::paginate("SELECT sq.*, u.name as user_name, g.name as gateway_name FROM sms_queue sq LEFT JOIN users u ON sq.user_id = u.id LEFT JOIN gateways g ON sq.gateway_id = g.id WHERE {$where} ORDER BY sq.priority ASC, sq.created_at DESC", $params, $page, 50);

        $summary = Database::fetchAll("SELECT status, COUNT(*) as count FROM sms_queue GROUP BY status");
        $summaryMap = [];
        foreach ($summary as $s) $summaryMap[$s['status']] = $s['count'];

        $this->layout('app', 'admin.queue.index', ['pageTitle' => 'SMS Queue', 'items' => $result['data'], 'pagination' => $result, 'status' => $status, 'summary' => $summaryMap]);
    }

    public function retry(string $id): void
    {
        Csrf::check();
        Database::update('sms_queue', ['status' => 'queued', 'retry_count' => 0, 'error_message' => null, 'locked_at' => null, 'locked_by' => null], 'id = ?', [(int)$id]);
        $this->redirect('/admin/queue', ['success' => 'Message requeued']);
    }

    public function retryAll(): void
    {
        Csrf::check();
        Database::query("UPDATE sms_queue SET status = 'queued', retry_count = 0, error_message = NULL, locked_at = NULL, locked_by = NULL WHERE status = 'failed'");
        $this->redirect('/admin/queue', ['success' => 'All failed messages requeued']);
    }

    public function purge(): void
    {
        Csrf::check();
        $status = $_POST['status'] ?? 'failed';
        Database::delete('sms_queue', 'status = ?', [$status]);
        $this->redirect('/admin/queue', ['success' => "Purged {$status} messages"]);
    }

    public function pause(): void
    {
        Csrf::check();
        file_put_contents(ROOT_PATH . '/storage/cache/queue_paused.lock', time());
        $this->redirect('/admin/queue', ['success' => 'Queue processing paused']);
    }

    public function resume(): void
    {
        Csrf::check();
        @unlink(ROOT_PATH . '/storage/cache/queue_paused.lock');
        $this->redirect('/admin/queue', ['success' => 'Queue processing resumed']);
    }
}
