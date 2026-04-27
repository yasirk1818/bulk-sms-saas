<?php
namespace App\Core;

class Logger
{
    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        if (defined('APP_DEBUG') && APP_DEBUG) {
            self::log('DEBUG', $message, $context);
        }
    }

    public static function audit(string $action, ?int $userId = null, array $data = []): void
    {
        try {
            Database::insert('audit_logs', [
                'user_id' => $userId ?? Session::userId(),
                'action' => $action,
                'ip_address' => self::getIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'data' => json_encode($data),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            self::error('Audit log failed: ' . $e->getMessage());
        }
    }

    private static function log(string $level, string $message, array $context = []): void
    {
        $logDir = STORAGE_PATH . '/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $logFile = $logDir . '/' . date('Y-m-d') . '.log';
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $entry = "[{$timestamp}] [{$level}] {$message}{$contextStr}" . PHP_EOL;

        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    public static function getIp(): string
    {
        $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = explode(',', $_SERVER[$header])[0];
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }
}
