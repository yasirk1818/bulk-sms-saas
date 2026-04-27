<?php
namespace App\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;

class TicketController extends Controller
{
    public function index(): void
    {
        $tickets = Database::fetchAll("SELECT * FROM tickets WHERE user_id = ? ORDER BY updated_at DESC", [Session::userId()]);
        $this->layout('app', 'user.tickets.index', ['pageTitle' => 'Support Tickets', 'tickets' => $tickets]);
    }

    public function create(): void
    {
        $this->layout('app', 'user.tickets.create', ['pageTitle' => 'New Ticket']);
    }

    public function store(): void
    {
        Csrf::check();
        $ticketNumber = 'TKT-' . strtoupper(bin2hex(random_bytes(4)));
        $ticketId = Database::insert('tickets', [
            'user_id' => Session::userId(), 'ticket_number' => $ticketNumber,
            'subject' => trim($_POST['subject']), 'category' => $_POST['category'] ?? 'general',
            'priority' => $_POST['priority'] ?? 'medium', 'status' => 'open',
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);
        Database::insert('ticket_replies', [
            'ticket_id' => $ticketId, 'user_id' => Session::userId(),
            'message' => trim($_POST['message']), 'is_staff_reply' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        $this->redirect('/tickets/' . $ticketId, ['success' => 'Ticket created']);
    }

    public function show(string $id): void
    {
        $ticket = Database::fetch("SELECT * FROM tickets WHERE id = ? AND user_id = ?", [(int)$id, Session::userId()]);
        if (!$ticket) { $this->redirect('/tickets', ['error' => 'Ticket not found']); return; }
        $replies = Database::fetchAll("SELECT tr.*, u.name, u.role FROM ticket_replies tr JOIN users u ON tr.user_id = u.id WHERE tr.ticket_id = ? ORDER BY tr.created_at ASC", [(int)$id]);
        $this->layout('app', 'user.tickets.show', ['pageTitle' => 'Ticket #' . $ticket['ticket_number'], 'ticket' => $ticket, 'replies' => $replies]);
    }

    public function reply(string $id): void
    {
        Csrf::check();
        Database::insert('ticket_replies', [
            'ticket_id' => (int)$id, 'user_id' => Session::userId(),
            'message' => trim($_POST['message']), 'is_staff_reply' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        Database::update('tickets', ['last_reply_at' => date('Y-m-d H:i:s'), 'last_reply_by' => 'user', 'status' => 'open'], 'id = ?', [(int)$id]);
        $this->redirect('/tickets/' . $id, ['success' => 'Reply sent']);
    }
}
