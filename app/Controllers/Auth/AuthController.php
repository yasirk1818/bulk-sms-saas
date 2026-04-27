<?php
namespace App\Controllers\Auth;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Session;
use App\Core\Csrf;
use App\Core\Logger;

class AuthController extends Controller
{
    public function loginPage(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
        }
        $this->layout('auth', 'auth.login', ['pageTitle' => 'Login']);
    }

    public function login(): void
    {
        Csrf::check();

        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $errors = $this->validate(['email' => $email, 'password' => $password], [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (!empty($errors)) {
            $this->redirect('/login', ['error' => 'Please provide valid email and password']);
            return;
        }

        $user = Database::fetch("SELECT * FROM users WHERE email = ?", [$email]);

        if (!$user) {
            $this->logLoginAttempt(null, $email, 'failed', 'User not found');
            $this->redirect('/login', ['error' => 'Invalid email or password']);
            return;
        }

        // Check if locked
        if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
            $this->logLoginAttempt($user['id'], $email, 'locked', 'Account locked');
            $this->redirect('/login', ['error' => 'Account is temporarily locked. Try again later.']);
            return;
        }

        // Check status
        if ($user['status'] !== 'active') {
            $this->logLoginAttempt($user['id'], $email, 'blocked', 'Account ' . $user['status']);
            $this->redirect('/login', ['error' => 'Your account is ' . $user['status'] . '. Contact support.']);
            return;
        }

        if (!password_verify($password, $user['password'])) {
            $attempts = $user['login_attempts'] + 1;
            $maxAttempts = 5;
            $updateData = ['login_attempts' => $attempts];

            if ($attempts >= $maxAttempts) {
                $updateData['locked_until'] = date('Y-m-d H:i:s', time() + 1800);
                $updateData['login_attempts'] = 0;
            }

            Database::update('users', $updateData, 'id = ?', [$user['id']]);
            $this->logLoginAttempt($user['id'], $email, 'failed', 'Invalid password');
            $remaining = $maxAttempts - $attempts;
            $msg = $remaining > 0 ? "Invalid password. {$remaining} attempts remaining." : 'Account locked for 30 minutes.';
            $this->redirect('/login', ['error' => $msg]);
            return;
        }

        // Check if OTP is required
        $otpEnabled = Database::fetch("SELECT setting_value FROM settings WHERE setting_key = 'otp_login'");
        if ($otpEnabled && $otpEnabled['setting_value'] === '1') {
            Session::set('otp_user_id', $user['id']);
            Session::set('otp_purpose', 'login');
            // Generate and send OTP
            $this->generateOtp($user);
            $this->redirect('/verify-otp');
            return;
        }

        // Login successful
        $this->completeLogin($user);
    }

    public function completeLogin(array $user): void
    {
        $ip = Logger::getIp();

        Database::update('users', [
            'login_attempts' => 0,
            'locked_until' => null,
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $ip
        ], 'id = ?', [$user['id']]);

        unset($user['password'], $user['two_factor_secret']);
        Session::setUser($user);

        $this->logLoginAttempt($user['id'], $user['email'], 'success');
        Logger::audit('user.login', $user['id']);

        $isAdmin = in_array($user['role'], ['super_admin', 'admin']);
        $this->redirect($isAdmin ? '/admin/dashboard' : '/dashboard');
    }

    public function registerPage(): void
    {
        if (Session::isLoggedIn()) {
            $this->redirect('/dashboard');
        }

        $regEnabled = Database::fetch("SELECT setting_value FROM settings WHERE setting_key = 'registration_enabled'");
        if ($regEnabled && $regEnabled['setting_value'] !== '1') {
            $this->redirect('/login', ['error' => 'Registration is currently disabled']);
            return;
        }

        $this->layout('auth', 'auth.register', ['pageTitle' => 'Register']);
    }

    public function register(): void
    {
        Csrf::check();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirmation'] ?? '';

        $errors = $this->validate([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirm
        ], [
            'name' => 'required|min:2|max:100',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ]);

        if (!empty($errors)) {
            $errorMsg = '';
            foreach ($errors as $field => $msgs) {
                $errorMsg .= implode('. ', $msgs) . '. ';
            }
            $this->redirect('/register', ['error' => trim($errorMsg)]);
            return;
        }

        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff));

        // Get trial package
        $trialPackage = Database::fetch("SELECT * FROM packages WHERE is_trial = 1 AND is_active = 1 LIMIT 1");

        $userId = Database::insert('users', [
            'uuid' => $uuid,
            'name' => $name,
            'email' => $email,
            'phone' => $phone ?: null,
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
            'role' => 'user',
            'status' => 'active',
            'sms_balance' => $trialPackage ? $trialPackage['sms_quota'] : 0,
            'package_id' => $trialPackage ? $trialPackage['id'] : null,
            'package_expires_at' => $trialPackage ? date('Y-m-d H:i:s', strtotime('+' . $trialPackage['validity_days'] . ' days')) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        if ($trialPackage) {
            Database::insert('subscriptions', [
                'user_id' => $userId,
                'package_id' => $trialPackage['id'],
                'status' => 'active',
                'sms_remaining' => $trialPackage['sms_quota'],
                'starts_at' => date('Y-m-d H:i:s'),
                'expires_at' => date('Y-m-d H:i:s', strtotime('+' . $trialPackage['validity_days'] . ' days')),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        Logger::audit('user.register', $userId);

        $this->redirect('/login', ['success' => 'Account created successfully! Please login.']);
    }

    public function forgotPasswordPage(): void
    {
        $this->layout('auth', 'auth.forgot-password', ['pageTitle' => 'Forgot Password']);
    }

    public function forgotPassword(): void
    {
        Csrf::check();
        $email = trim($_POST['email'] ?? '');

        $user = Database::fetch("SELECT id, email, name FROM users WHERE email = ?", [$email]);
        if (!$user) {
            $this->redirect('/forgot-password', ['error' => 'No account found with this email']);
            return;
        }

        $token = bin2hex(random_bytes(32));
        Database::insert('password_resets', [
            'email' => $email,
            'token' => hash('sha256', $token),
            'expires_at' => date('Y-m-d H:i:s', time() + 3600),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        Logger::audit('password.reset_requested', $user['id']);

        $this->redirect('/forgot-password', ['success' => "Password reset link generated. Token: {$token} (In production, this would be emailed)"]);
    }

    public function resetPasswordPage(string $token): void
    {
        $this->layout('auth', 'auth.reset-password', ['pageTitle' => 'Reset Password', 'token' => $token]);
    }

    public function resetPassword(): void
    {
        Csrf::check();

        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirmation'] ?? '';

        if (strlen($password) < 8 || $password !== $passwordConfirm) {
            $this->redirect('/forgot-password', ['error' => 'Password must be at least 8 characters and match confirmation']);
            return;
        }

        $reset = Database::fetch(
            "SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW() ORDER BY id DESC LIMIT 1",
            [hash('sha256', $token)]
        );

        if (!$reset) {
            $this->redirect('/forgot-password', ['error' => 'Invalid or expired reset token']);
            return;
        }

        Database::update('users', [
            'password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])
        ], 'email = ?', [$reset['email']]);

        Database::update('password_resets', ['used' => 1], 'id = ?', [$reset['id']]);

        $this->redirect('/login', ['success' => 'Password reset successfully! Please login.']);
    }

    public function logout(): void
    {
        Logger::audit('user.logout', Session::userId());
        Session::destroy();
        session_start();
        $this->redirect('/login', ['success' => 'You have been logged out']);
    }

    private function generateOtp(array $user): void
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        Database::insert('otp_codes', [
            'user_id' => $user['id'],
            'identifier' => $user['email'],
            'code' => password_hash($code, PASSWORD_BCRYPT),
            'type' => 'email',
            'purpose' => 'login',
            'expires_at' => date('Y-m-d H:i:s', time() + 300),
            'created_at' => date('Y-m-d H:i:s')
        ]);
        // In production: send OTP via email/SMS
        Session::flash('otp_code_debug', $code);
    }

    private function logLoginAttempt(?int $userId, string $email, string $status, string $reason = ''): void
    {
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
        Database::insert('login_logs', [
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => Logger::getIp(),
            'user_agent' => $ua,
            'device_type' => $this->detectDevice($ua),
            'browser' => $this->detectBrowser($ua),
            'os' => $this->detectOS($ua),
            'status' => $status,
            'failure_reason' => $reason ?: null,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    private function detectDevice(string $ua): string
    {
        if (preg_match('/mobile|android|iphone/i', $ua)) return 'mobile';
        if (preg_match('/tablet|ipad/i', $ua)) return 'tablet';
        return 'desktop';
    }

    private function detectBrowser(string $ua): string
    {
        if (str_contains($ua, 'Chrome')) return 'Chrome';
        if (str_contains($ua, 'Firefox')) return 'Firefox';
        if (str_contains($ua, 'Safari')) return 'Safari';
        if (str_contains($ua, 'Edge')) return 'Edge';
        return 'Other';
    }

    private function detectOS(string $ua): string
    {
        if (str_contains($ua, 'Windows')) return 'Windows';
        if (str_contains($ua, 'Mac')) return 'macOS';
        if (str_contains($ua, 'Linux')) return 'Linux';
        if (str_contains($ua, 'Android')) return 'Android';
        if (str_contains($ua, 'iPhone') || str_contains($ua, 'iPad')) return 'iOS';
        return 'Other';
    }
}
