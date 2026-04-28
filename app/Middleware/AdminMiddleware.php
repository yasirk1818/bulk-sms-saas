<?php
namespace App\Middleware;

use App\Core\Session;

class AdminMiddleware
{
    public function handle(): void
    {
        (new AuthMiddleware())->handle();

        $role = Session::userRole();
        if (!in_array($role, ['super_admin', 'admin'])) {
            http_response_code(403);
            if ($this->isAjax()) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Access denied']);
            } else {
                Session::flash('error', 'Access denied');
                header('Location: /dashboard');
            }
            exit;
        }
    }

    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}
