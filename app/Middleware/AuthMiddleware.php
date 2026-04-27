<?php
namespace App\Middleware;

use App\Core\Session;

class AuthMiddleware
{
    public function handle(): void
    {
        if (!Session::isLoggedIn()) {
            if ($this->isAjax()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Unauthorized']);
                exit;
            }
            Session::flash('error', 'Please login to continue');
            header('Location: /login');
            exit;
        }
    }

    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
