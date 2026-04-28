<?php
namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;

class OtpController extends Controller
{
    public function verifyPage(): void
    {
        if (!Session::has('otp_user_id')) {
            $this->redirect('/login');
            return;
        }
        $this->layout('auth', 'auth.verify-otp', [
            'pageTitle' => 'Verify OTP',
            'pageSubtitle' => 'Enter the verification code'
        ]);
    }

    public function verify(): void
    {
        Csrf::check();

        $userId = Session::get('otp_user_id');
        if (!$userId) {
            $this->redirect('/login', ['error' => 'Session expired']);
            return;
        }

        $code = trim($_POST['code'] ?? '');
        if (empty($code)) {
            $this->redirect('/verify-otp', ['error' => 'Please enter the OTP code']);
            return;
        }

        $otp = Database::fetch(
            "SELECT * FROM otp_codes WHERE user_id = ? AND purpose = ? AND is_used = 0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1",
            [$userId, Session::get('otp_purpose', 'login')]
        );

        if (!$otp || !password_verify($code, $otp['code'])) {
            if ($otp) {
                Database::update('otp_codes', ['attempts' => $otp['attempts'] + 1], 'id = ?', [$otp['id']]);
                if ($otp['attempts'] + 1 >= $otp['max_attempts']) {
                    Database::update('otp_codes', ['is_used' => 1], 'id = ?', [$otp['id']]);
                    Session::remove('otp_user_id');
                    $this->redirect('/login', ['error' => 'Too many failed attempts. Please login again.']);
                    return;
                }
            }
            $this->redirect('/verify-otp', ['error' => 'Invalid OTP code']);
            return;
        }

        Database::update('otp_codes', ['is_used' => 1, 'verified_at' => date('Y-m-d H:i:s')], 'id = ?', [$otp['id']]);

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        Session::remove('otp_user_id');
        Session::remove('otp_purpose');

        $auth = new AuthController();
        $auth->completeLogin($user);
    }

    public function resend(): void
    {
        Csrf::check();

        $userId = Session::get('otp_user_id');
        if (!$userId) {
            $this->json(['error' => 'Session expired'], 401);
            return;
        }

        $lastOtp = Database::fetch(
            "SELECT created_at FROM otp_codes WHERE user_id = ? ORDER BY id DESC LIMIT 1",
            [$userId]
        );

        if ($lastOtp && (time() - strtotime($lastOtp['created_at'])) < 60) {
            $this->json(['error' => 'Please wait before requesting a new code'], 429);
            return;
        }

        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Database::insert('otp_codes', [
            'user_id' => $userId,
            'identifier' => $user['email'],
            'code' => password_hash($code, PASSWORD_BCRYPT),
            'type' => 'email',
            'purpose' => Session::get('otp_purpose', 'login'),
            'expires_at' => date('Y-m-d H:i:s', time() + 300),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->json(['success' => true, 'message' => 'New OTP sent', 'debug_code' => $code]);
    }
}
