<?php
namespace App\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Logger;
use App\Helpers\SmsHelper;

class CampaignController extends Controller
{
    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $result = Database::paginate("SELECT * FROM campaigns WHERE user_id = ? ORDER BY id DESC", [Session::userId()], $page, 20);
        $this->layout('app', 'user.campaigns.index', ['pageTitle' => 'Campaigns', 'campaigns' => $result['data'], 'pagination' => $result]);
    }

    public function create(): void
    {
        $userId = Session::userId();
        $groups = Database::fetchAll("SELECT cg.*, COUNT(cgm.id) as member_count FROM contact_groups cg LEFT JOIN contact_group_members cgm ON cg.id = cgm.group_id WHERE cg.user_id = ? GROUP BY cg.id", [$userId]);
        $templates = Database::fetchAll("SELECT * FROM sms_templates WHERE user_id = ? AND status = 'approved'", [$userId]);
        $senderIds = Database::fetchAll("SELECT * FROM sender_ids WHERE user_id = ? AND status = 'approved'", [$userId]);
        $gateways = Database::fetchAll("SELECT id, name FROM gateways WHERE status = 'active'");

        $this->layout('app', 'user.campaigns.form', ['pageTitle' => 'Create Campaign', 'campaign' => null, 'groups' => $groups, 'templates' => $templates, 'senderIds' => $senderIds, 'gateways' => $gateways]);
    }

    public function store(): void
    {
        Csrf::check();
        $userId = Session::userId();

        $campaignId = Database::insert('campaigns', [
            'user_id' => $userId, 'name' => trim($_POST['name']),
            'message' => trim($_POST['message']), 'sender_id' => trim($_POST['sender_id'] ?? ''),
            'type' => $_POST['type'] ?? 'instant', 'status' => $_POST['type'] === 'scheduled' ? 'scheduled' : 'draft',
            'gateway_id' => $_POST['gateway_id'] ?: null, 'template_id' => $_POST['template_id'] ?: null,
            'group_ids' => json_encode($_POST['group_ids'] ?? []),
            'scheduled_at' => $_POST['scheduled_at'] ?? null,
            'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Queue messages if instant
        if (($_POST['type'] ?? 'instant') === 'instant' && !empty($_POST['group_ids'])) {
            $this->queueCampaignMessages($campaignId, $userId);
        }

        Logger::audit('campaign.created', $userId, ['campaign_id' => $campaignId]);
        $this->redirect('/campaigns', ['success' => 'Campaign created successfully']);
    }

    public function show(string $id): void
    {
        $campaign = Database::fetch("SELECT * FROM campaigns WHERE id = ? AND user_id = ?", [(int)$id, Session::userId()]);
        if (!$campaign) { $this->redirect('/campaigns', ['error' => 'Campaign not found']); return; }
        $logs = Database::fetchAll("SELECT * FROM sms_logs WHERE campaign_id = ? ORDER BY created_at DESC LIMIT 50", [(int)$id]);
        $this->layout('app', 'user.campaigns.show', ['pageTitle' => 'Campaign Details', 'campaign' => $campaign, 'logs' => $logs]);
    }

    public function edit(string $id): void
    {
        $campaign = Database::fetch("SELECT * FROM campaigns WHERE id = ? AND user_id = ? AND status IN ('draft','scheduled')", [(int)$id, Session::userId()]);
        if (!$campaign) { $this->redirect('/campaigns', ['error' => 'Campaign not found or not editable']); return; }
        $userId = Session::userId();
        $groups = Database::fetchAll("SELECT cg.*, COUNT(cgm.id) as member_count FROM contact_groups cg LEFT JOIN contact_group_members cgm ON cg.id = cgm.group_id WHERE cg.user_id = ? GROUP BY cg.id", [$userId]);
        $templates = Database::fetchAll("SELECT * FROM sms_templates WHERE user_id = ? AND status = 'approved'", [$userId]);
        $senderIds = Database::fetchAll("SELECT * FROM sender_ids WHERE user_id = ? AND status = 'approved'", [$userId]);
        $gateways = Database::fetchAll("SELECT id, name FROM gateways WHERE status = 'active'");
        $this->layout('app', 'user.campaigns.form', ['pageTitle' => 'Edit Campaign', 'campaign' => $campaign, 'groups' => $groups, 'templates' => $templates, 'senderIds' => $senderIds, 'gateways' => $gateways]);
    }

    public function update(string $id): void
    {
        Csrf::check();
        Database::update('campaigns', [
            'name' => trim($_POST['name']), 'message' => trim($_POST['message']),
            'sender_id' => trim($_POST['sender_id'] ?? ''), 'type' => $_POST['type'] ?? 'instant',
            'gateway_id' => $_POST['gateway_id'] ?: null,
            'group_ids' => json_encode($_POST['group_ids'] ?? []),
            'scheduled_at' => $_POST['scheduled_at'] ?? null,
        ], 'id = ? AND user_id = ?', [(int)$id, Session::userId()]);
        $this->redirect('/campaigns/' . $id, ['success' => 'Campaign updated']);
    }

    public function delete(string $id): void
    {
        Csrf::check();
        Database::delete('campaigns', 'id = ? AND user_id = ?', [(int)$id, Session::userId()]);
        $this->redirect('/campaigns', ['success' => 'Campaign deleted']);
    }

    private function queueCampaignMessages(int $campaignId, int $userId): void
    {
        $campaign = Database::fetch("SELECT * FROM campaigns WHERE id = ?", [$campaignId]);
        $groupIds = json_decode($campaign['group_ids'] ?? '[]', true);
        if (empty($groupIds)) return;

        $gateway = $campaign['gateway_id'] ? Database::fetch("SELECT * FROM gateways WHERE id = ?", [$campaign['gateway_id']]) : SmsHelper::selectGateway();
        if (!$gateway) return;

        $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
        $contacts = Database::fetchAll("SELECT DISTINCT c.* FROM contacts c JOIN contact_group_members cgm ON c.id = cgm.contact_id WHERE cgm.group_id IN ({$placeholders}) AND c.user_id = ? AND c.is_blacklisted = 0 AND c.opt_out = 0", [...$groupIds, $userId]);

        $parts = SmsHelper::calculateParts($campaign['message']);
        $costPerSms = $parts * (float)$gateway['cost_per_sms'];
        $totalCost = $costPerSms * count($contacts);

        if (!SmsHelper::deductCredits($userId, $totalCost, "Campaign: {$campaign['name']}")) return;

        $queued = 0;
        foreach ($contacts as $contact) {
            $msg = SmsHelper::personalizeMessage($campaign['message'], $contact);
            SmsHelper::queueSms([
                'user_id' => $userId, 'campaign_id' => $campaignId,
                'gateway_id' => $gateway['id'], 'sender_id' => $campaign['sender_id'] ?: $gateway['default_sender_id'],
                'recipient' => $contact['phone'], 'message' => $msg,
                'cost' => $costPerSms, 'priority' => 3
            ]);
            $queued++;
        }

        Database::update('campaigns', ['status' => 'running', 'total_recipients' => $queued, 'total_cost' => $totalCost, 'started_at' => date('Y-m-d H:i:s')], 'id = ?', [$campaignId]);
    }
}
