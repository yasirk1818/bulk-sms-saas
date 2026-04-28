<?php
namespace App\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Logger;
use App\Helpers\SmsHelper;

class SmsController extends Controller
{
    public function sendPage(): void
    {
        $userId = Session::userId();
        $senderIds = Database::fetchAll("SELECT * FROM sender_ids WHERE user_id = ? AND status = 'approved'", [$userId]);
        $templates = Database::fetchAll("SELECT * FROM sms_templates WHERE user_id = ? AND status = 'approved'", [$userId]);
        $groups = Database::fetchAll("SELECT * FROM contact_groups WHERE user_id = ?", [$userId]);

        $this->layout('app', 'user.sms.send', [
            'pageTitle' => 'Send SMS',
            'senderIds' => $senderIds,
            'templates' => $templates,
            'groups' => $groups
        ]);
    }

    public function send(): void
    {
        Csrf::check();
        $userId = Session::userId();

        $recipient = SmsHelper::cleanNumber($_POST['recipient'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $senderId = trim($_POST['sender_id'] ?? '');
        $scheduleAt = $_POST['schedule_at'] ?? null;

        if (empty($recipient) || empty($message)) {
            $this->redirect('/sms/send', ['error' => 'Recipient and message are required']);
            return;
        }

        if (!SmsHelper::validateNumber($recipient)) {
            $this->redirect('/sms/send', ['error' => 'Invalid phone number format']);
            return;
        }

        if (SmsHelper::isBlacklisted($recipient, $userId)) {
            $this->redirect('/sms/send', ['error' => 'This number is blacklisted']);
            return;
        }

        if (SmsHelper::checkSpam($message)) {
            $this->redirect('/sms/send', ['error' => 'Message contains blocked content']);
            return;
        }

        $gateway = SmsHelper::selectGateway(SmsHelper::detectCountry($recipient));
        if (!$gateway) {
            $this->redirect('/sms/send', ['error' => 'No active gateway available']);
            return;
        }

        $parts = SmsHelper::calculateParts($message);
        $cost = $parts * (float)$gateway['cost_per_sms'];

        if (!SmsHelper::deductCredits($userId, $cost, "SMS to {$recipient}")) {
            $this->redirect('/sms/send', ['error' => 'Insufficient SMS balance']);
            return;
        }

        $queueId = SmsHelper::queueSms([
            'user_id' => $userId,
            'gateway_id' => $gateway['id'],
            'sender_id' => $senderId ?: $gateway['default_sender_id'],
            'recipient' => $recipient,
            'message' => $message,
            'cost' => $cost,
            'priority' => 5,
            'scheduled_at' => $scheduleAt ?: null
        ]);

        Logger::audit('sms.queued', $userId, ['recipient' => $recipient, 'queue_id' => $queueId]);

        $this->redirect('/sms/send', ['success' => $scheduleAt ? 'SMS scheduled successfully' : 'SMS queued for delivery']);
    }

    public function bulkPage(): void
    {
        $userId = Session::userId();
        $senderIds = Database::fetchAll("SELECT * FROM sender_ids WHERE user_id = ? AND status = 'approved'", [$userId]);
        $templates = Database::fetchAll("SELECT * FROM sms_templates WHERE user_id = ? AND status = 'approved'", [$userId]);
        $groups = Database::fetchAll("SELECT cg.*, COUNT(cgm.id) as member_count FROM contact_groups cg LEFT JOIN contact_group_members cgm ON cg.id = cgm.group_id WHERE cg.user_id = ? GROUP BY cg.id", [$userId]);

        $this->layout('app', 'user.sms.bulk', [
            'pageTitle' => 'Bulk SMS',
            'senderIds' => $senderIds,
            'templates' => $templates,
            'groups' => $groups
        ]);
    }

    public function sendBulk(): void
    {
        Csrf::check();
        $userId = Session::userId();

        $message = trim($_POST['message'] ?? '');
        $senderId = trim($_POST['sender_id'] ?? '');
        $numbers = $_POST['numbers'] ?? '';
        $groupIds = $_POST['group_ids'] ?? [];
        $scheduleAt = $_POST['schedule_at'] ?? null;

        if (empty($message)) {
            $this->redirect('/sms/bulk', ['error' => 'Message is required']);
            return;
        }

        // Collect numbers
        $recipients = [];
        if (!empty($numbers)) {
            $lines = preg_split('/[\r\n,;]+/', $numbers);
            foreach ($lines as $line) {
                $num = SmsHelper::cleanNumber(trim($line));
                if (SmsHelper::validateNumber($num)) {
                    $recipients[] = $num;
                }
            }
        }

        // Add group contacts
        if (!empty($groupIds)) {
            foreach ($groupIds as $groupId) {
                $contacts = Database::fetchAll(
                    "SELECT c.phone FROM contacts c JOIN contact_group_members cgm ON c.id = cgm.contact_id WHERE cgm.group_id = ? AND c.user_id = ? AND c.is_blacklisted = 0 AND c.opt_out = 0",
                    [(int)$groupId, $userId]
                );
                foreach ($contacts as $c) {
                    $recipients[] = SmsHelper::cleanNumber($c['phone']);
                }
            }
        }

        // Handle CSV upload
        if (!empty($_FILES['csv_file']['tmp_name'])) {
            $handle = fopen($_FILES['csv_file']['tmp_name'], 'r');
            while (($row = fgetcsv($handle)) !== false) {
                $num = SmsHelper::cleanNumber($row[0] ?? '');
                if (SmsHelper::validateNumber($num)) {
                    $recipients[] = $num;
                }
            }
            fclose($handle);
        }

        // Remove duplicates & blacklisted
        $recipients = array_unique($recipients);
        $recipients = array_filter($recipients, fn($n) => !SmsHelper::isBlacklisted($n, $userId));
        $recipients = array_values($recipients);

        if (empty($recipients)) {
            $this->redirect('/sms/bulk', ['error' => 'No valid recipients found']);
            return;
        }

        $gateway = SmsHelper::selectGateway();
        if (!$gateway) {
            $this->redirect('/sms/bulk', ['error' => 'No active gateway available']);
            return;
        }

        $parts = SmsHelper::calculateParts($message);
        $costPerSms = $parts * (float)$gateway['cost_per_sms'];
        $totalCost = $costPerSms * count($recipients);

        if (!SmsHelper::deductCredits($userId, $totalCost, "Bulk SMS to " . count($recipients) . " recipients")) {
            $this->redirect('/sms/bulk', ['error' => 'Insufficient balance. Need ' . number_format($totalCost, 2) . ' SMS credits.']);
            return;
        }

        $queued = 0;
        foreach ($recipients as $recipient) {
            SmsHelper::queueSms([
                'user_id' => $userId,
                'gateway_id' => $gateway['id'],
                'sender_id' => $senderId ?: $gateway['default_sender_id'],
                'recipient' => $recipient,
                'message' => $message,
                'cost' => $costPerSms,
                'priority' => 5,
                'scheduled_at' => $scheduleAt ?: null
            ]);
            $queued++;
        }

        Logger::audit('sms.bulk_queued', $userId, ['count' => $queued, 'cost' => $totalCost]);

        $this->redirect('/sms/bulk', ['success' => "{$queued} SMS queued for delivery. Total cost: " . number_format($totalCost, 2) . " credits."]);
    }

    public function history(): void
    {
        $userId = Session::userId();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $status = $_GET['status'] ?? '';
        $search = $_GET['search'] ?? '';

        $where = 'user_id = ?';
        $params = [$userId];

        if ($status) {
            $where .= ' AND status = ?';
            $params[] = $status;
        }
        if ($search) {
            $where .= ' AND (recipient LIKE ? OR message LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }

        $result = Database::paginate(
            "SELECT * FROM sms_logs WHERE {$where} ORDER BY created_at DESC",
            $params, $page, 20
        );

        $this->layout('app', 'user.sms.history', [
            'pageTitle' => 'SMS History',
            'logs' => $result['data'],
            'pagination' => $result,
            'status' => $status,
            'search' => $search
        ]);
    }

    public function scheduled(): void
    {
        $userId = Session::userId();
        $scheduled = Database::fetchAll(
            "SELECT * FROM sms_queue WHERE user_id = ? AND scheduled_at IS NOT NULL AND status = 'queued' ORDER BY scheduled_at ASC",
            [$userId]
        );

        $this->layout('app', 'user.sms.scheduled', [
            'pageTitle' => 'Scheduled SMS',
            'scheduled' => $scheduled
        ]);
    }

    public function schedule(): void
    {
        Csrf::check();
        // Reuse send logic with schedule_at
        $this->send();
    }
}
