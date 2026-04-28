<?php
namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            ini_set('session.cookie_httponly', '1');
            ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
            ini_set('session.cookie_samesite', 'Lax');
            ini_set('session.use_strict_mode', '1');
            ini_set('session.gc_maxlifetime', '7200');

            $savePath = STORAGE_PATH . '/sessions';
            if (is_dir($savePath) && is_writable($savePath)) {
                session_save_path($savePath);
            }

            session_name('BULKSMS_SESSION');
            session_start();

            // Regenerate session ID periodically
            if (!isset($_SESSION['_last_regenerate'])) {
                $_SESSION['_last_regenerate'] = time();
            } elseif (time() - $_SESSION['_last_regenerate'] > 300) {
                session_regenerate_id(true);
                $_SESSION['_last_regenerate'] = time();
            }
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    public static function getFlash(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function destroy(): void
    {
        session_unset();
        session_destroy();
    }

    public static function setUser(array $user): void
    {
        self::set('user', $user);
        self::set('user_id', $user['id']);
        self::set('user_role', $user['role']);
        self::set('logged_in', true);
    }

    public static function getUser(): ?array
    {
        return self::get('user');
    }

    public static function userId(): ?int
    {
        return self::get('user_id');
    }

    public static function userRole(): ?string
    {
        return self::get('user_role');
    }

    public static function isLoggedIn(): bool
    {
        return self::get('logged_in', false) === true;
    }
}
