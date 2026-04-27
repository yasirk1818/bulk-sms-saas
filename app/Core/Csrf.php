<?php
namespace App\Core;

class Csrf
{
    public static function init(): void
    {
        if (!Session::has('csrf_token')) {
            self::regenerate();
        }
    }

    public static function regenerate(): string
    {
        $token = bin2hex(random_bytes(32));
        Session::set('csrf_token', $token);
        return $token;
    }

    public static function token(): string
    {
        return Session::get('csrf_token', '');
    }

    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="' . self::token() . '">';
    }

    public static function validate(?string $token = null): bool
    {
        $token = $token ?? ($_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        $sessionToken = self::token();

        if (empty($token) || empty($sessionToken)) {
            return false;
        }

        return hash_equals($sessionToken, $token);
    }

    public static function check(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !self::validate()) {
            http_response_code(403);
            die('CSRF token validation failed');
        }
    }
}
