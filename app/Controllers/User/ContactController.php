<?php
namespace App\Controllers\User;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Logger;
use App\Helpers\SmsHelper;

class ContactController extends Controller
{
    public function index(): void
    {
        $userId = Session::userId();
        $page = max(1, (int)($_GET['page'] ?? 1));
        $search = $_GET['search'] ?? '';

        $where = 'user_id = ?';
        $params = [$userId];
        if ($search) { $where .= " AND (phone LIKE ? OR name LIKE ? OR email LIKE ?)"; $params[] = "%{$search}%"; $params[] = "%{$search}%"; $params[] = "%{$search}%"; }

        $result = Database::paginate("SELECT * FROM contacts WHERE {$where} ORDER BY id DESC", $params, $page, 20);
        $groups = Database::fetchAll("SELECT * FROM contact_groups WHERE user_id = ?", [$userId]);

        $this->layout('app', 'user.contacts.index', ['pageTitle' => 'Contacts', 'contacts' => $result['data'], 'pagination' => $result, 'groups' => $groups, 'search' => $search]);
    }

    public function store(): void
    {
        Csrf::check();
        $userId = Session::userId();
        $phone = SmsHelper::cleanNumber($_POST['phone'] ?? '');
        if (!SmsHelper::validateNumber($phone)) { $this->redirect('/contacts', ['error' => 'Invalid phone number']); return; }
        if (Database::count('contacts', 'user_id = ? AND phone = ?', [$userId, $phone]) > 0) { $this->redirect('/contacts', ['error' => 'Contact already exists']); return; }

        $id = Database::insert('contacts', [
            'user_id' => $userId, 'phone' => $phone, 'name' => trim($_POST['name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''), 'country_code' => SmsHelper::detectCountry($phone),
            'company' => trim($_POST['company'] ?? ''), 'tags' => trim($_POST['tags'] ?? ''),
            'notes' => trim($_POST['notes'] ?? ''), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
        ]);

        if (!empty($_POST['group_id'])) {
            Database::insert('contact_group_members', ['contact_id' => $id, 'group_id' => (int)$_POST['group_id'], 'created_at' => date('Y-m-d H:i:s')]);
            Database::query("UPDATE contact_groups SET contacts_count = contacts_count + 1 WHERE id = ?", [(int)$_POST['group_id']]);
        }

        $this->redirect('/contacts', ['success' => 'Contact added']);
    }

    public function update(string $id): void
    {
        Csrf::check();
        Database::update('contacts', [
            'name' => trim($_POST['name'] ?? ''), 'email' => trim($_POST['email'] ?? ''),
            'company' => trim($_POST['company'] ?? ''), 'tags' => trim($_POST['tags'] ?? ''),
            'notes' => trim($_POST['notes'] ?? '')
        ], 'id = ? AND user_id = ?', [(int)$id, Session::userId()]);
        $this->redirect('/contacts', ['success' => 'Contact updated']);
    }

    public function delete(string $id): void
    {
        Csrf::check();
        Database::delete('contact_group_members', 'contact_id = ?', [(int)$id]);
        Database::delete('contacts', 'id = ? AND user_id = ?', [(int)$id, Session::userId()]);
        $this->redirect('/contacts', ['success' => 'Contact deleted']);
    }

    public function groups(): void
    {
        $groups = Database::fetchAll("SELECT cg.*, COUNT(cgm.id) as member_count FROM contact_groups cg LEFT JOIN contact_group_members cgm ON cg.id = cgm.group_id WHERE cg.user_id = ? GROUP BY cg.id ORDER BY cg.name", [Session::userId()]);
        $this->layout('app', 'user.contacts.groups', ['pageTitle' => 'Contact Groups', 'groups' => $groups]);
    }

    public function storeGroup(): void
    {
        Csrf::check();
        Database::insert('contact_groups', ['user_id' => Session::userId(), 'name' => trim($_POST['name']), 'description' => trim($_POST['description'] ?? ''), 'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);
        $this->redirect('/contacts/groups', ['success' => 'Group created']);
    }

    public function import(): void
    {
        Csrf::check();
        $userId = Session::userId();
        if (empty($_FILES['file']['tmp_name'])) { $this->redirect('/contacts', ['error' => 'No file uploaded']); return; }

        $handle = fopen($_FILES['file']['tmp_name'], 'r');
        $imported = $skipped = $failed = 0;
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $phone = SmsHelper::cleanNumber($row[0] ?? '');
            if (!SmsHelper::validateNumber($phone)) { $failed++; continue; }
            if (Database::count('contacts', 'user_id = ? AND phone = ?', [$userId, $phone]) > 0) { $skipped++; continue; }

            Database::insert('contacts', [
                'user_id' => $userId, 'phone' => $phone, 'name' => $row[1] ?? '',
                'email' => $row[2] ?? '', 'country_code' => SmsHelper::detectCountry($phone),
                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
            ]);
            $imported++;
        }
        fclose($handle);

        Database::insert('import_logs', ['user_id' => $userId, 'file_name' => $_FILES['file']['name'], 'file_size' => $_FILES['file']['size'], 'type' => 'contacts', 'total_rows' => $imported + $skipped + $failed, 'imported' => $imported, 'skipped' => $skipped, 'failed' => $failed, 'status' => 'completed', 'created_at' => date('Y-m-d H:i:s')]);

        $this->redirect('/contacts', ['success' => "Imported: {$imported}, Skipped: {$skipped}, Failed: {$failed}"]);
    }

    public function export(): void
    {
        $contacts = Database::fetchAll("SELECT phone, name, email, company, tags FROM contacts WHERE user_id = ? ORDER BY name", [Session::userId()]);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="contacts_' . date('Y-m-d') . '.csv"');
        $out = fopen('php://output', 'w');
        fputcsv($out, ['Phone', 'Name', 'Email', 'Company', 'Tags']);
        foreach ($contacts as $c) fputcsv($out, $c);
        fclose($out);
        exit;
    }
}
