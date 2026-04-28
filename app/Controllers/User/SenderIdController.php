<?php
namespace App\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;

class SenderIdController extends Controller
{
    public function index(): void
    {
        $senderIds = Database::fetchAll("SELECT * FROM sender_ids WHERE user_id = ? ORDER BY id DESC", [Session::userId()]);
        $this->layout('app', 'user.sender-ids', ['pageTitle' => 'Sender IDs', 'senderIds' => $senderIds]);
    }

    public function request(): void
    {
        Csrf::check();
        Database::insert('sender_ids', [
            'user_id' => Session::userId(), 'sender_id' => trim($_POST['sender_id']),
            'purpose' => trim($_POST['purpose'] ?? ''), 'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);
        $this->redirect('/sender-ids', ['success' => 'Sender ID request submitted']);
    }
}
