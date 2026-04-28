<?php
namespace App\Controllers\Admin;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Csrf;
use App\Core\Logger;
use App\Core\Session;

class BackupController extends Controller
{
    public function index(): void
    {
        $backups = [];
        $backupDir = ROOT_PATH . '/storage/backups';
        if (is_dir($backupDir)) {
            foreach (glob($backupDir . '/*.sql') as $file) {
                $backups[] = ['name' => basename($file), 'size' => filesize($file), 'date' => date('Y-m-d H:i:s', filemtime($file))];
            }
        }
        usort($backups, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));

        $this->layout('app', 'admin.backups', ['pageTitle' => 'Backup Manager', 'backups' => $backups]);
    }

    public function create(): void
    {
        Csrf::check();
        $backupDir = ROOT_PATH . '/storage/backups';
        if (!is_dir($backupDir)) mkdir($backupDir, 0755, true);

        $filename = 'backup_' . date('Y-m-d_His') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        $tables = Database::fetchAll("SHOW TABLES");
        $output = "-- BulkSMS Pro Database Backup\n-- Date: " . date('Y-m-d H:i:s') . "\n-- Version: " . (defined('APP_VERSION') ? APP_VERSION : '1.0.0') . "\n\nSET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $tableName = array_values($table)[0];
            $createTable = Database::fetch("SHOW CREATE TABLE `{$tableName}`");
            $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
            $output .= $createTable['Create Table'] . ";\n\n";

            $rows = Database::fetchAll("SELECT * FROM `{$tableName}`");
            foreach ($rows as $row) {
                $values = array_map(fn($v) => $v === null ? 'NULL' : "'" . addslashes($v) . "'", array_values($row));
                $output .= "INSERT INTO `{$tableName}` VALUES (" . implode(',', $values) . ");\n";
            }
            $output .= "\n";
        }

        $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
        file_put_contents($filepath, $output);

        Logger::audit('admin.backup.created', Session::userId(), ['file' => $filename]);
        $this->redirect('/admin/backups', ['success' => "Backup created: {$filename} (" . round(filesize($filepath) / 1024, 1) . " KB)"]);
    }

    public function download(string $file): void
    {
        $filepath = ROOT_PATH . '/storage/backups/' . basename($file);
        if (!file_exists($filepath)) { $this->redirect('/admin/backups', ['error' => 'Backup not found']); return; }
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Content-Length: ' . filesize($filepath));
        readfile($filepath);
        exit;
    }

    public function delete(string $file): void
    {
        Csrf::check();
        $filepath = ROOT_PATH . '/storage/backups/' . basename($file);
        if (file_exists($filepath)) unlink($filepath);
        $this->redirect('/admin/backups', ['success' => 'Backup deleted']);
    }
}
