<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;

class TicketController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $where = '1=1';
        $params = [];
        if ($status) { $where .= ' AND t.status = ?'; $params[] = $status; }

        $result = Database::paginate("SELECT t.*, u.name as user_name FROM tickets t JOIN users u ON t.user_id = u.id WHERE {$where} ORDER BY FIELD(t.status,'open','in_progress','answered','closed'), t.updated_at DESC", $params, $page, 20);
        $this->layout('app', 'admin.tickets.index', ['pageTitle' => 'Support Tickets', 'tickets' => $result['data'], 'pagination' => $result, 'status' => $status]);
    }

    public function show(string $id): void
    {
        $ticket = Database::fetch("SELECT t.*, u.name as user_name, u.email as user_email FROM tickets t JOIN users u ON t.user_id = u.id WHERE t.id = ?", [(int)$id]);
        if (!$ticket) { $this->redirect('/admin/tickets', ['error' => 'Ticket not found']); return; }
        $replies = Database::fetchAll("SELECT tr.*, u.name, u.role FROM ticket_replies tr JOIN users u ON tr.user_id = u.id WHERE tr.ticket_id = ? ORDER BY tr.created_at ASC", [(int)$id]);
        $this->layout('app', 'admin.tickets.show', ['pageTitle' => 'Ticket #' . $ticket['ticket_number'], 'ticket' => $ticket, 'replies' => $replies]);
    }

    public function reply(string $id): void
    {
        Csrf::check();
        Database::insert('ticket_replies', ['ticket_id' => (int)$id, 'user_id' => Session::userId(), 'message' => trim($_POST['message']), 'is_staff_reply' => 1, 'created_at' => date('Y-m-d H:i:s')]);
        Database::update('tickets', ['status' => 'answered', 'last_reply_at' => date('Y-m-d H:i:s'), 'last_reply_by' => 'staff', 'assigned_to' => Session::userId()], 'id = ?', [(int)$id]);
        $this->redirect('/admin/tickets/' . $id, ['success' => 'Reply sent']);
    }

    public function close(string $id): void
    {
        Csrf::check();
        Database::update('tickets', ['status' => 'closed', 'closed_at' => date('Y-m-d H:i:s')], 'id = ?', [(int)$id]);
        $this->redirect('/admin/tickets/' . $id, ['success' => 'Ticket closed']);
    }
}
