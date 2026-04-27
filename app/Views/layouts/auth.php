<?php
use App\Core\Csrf;

$pageTitle = $pageTitle ?? 'Login';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Csrf::token() ?>">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= defined('APP_NAME') ? APP_NAME : 'BulkSMS Pro' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/public/css/app.css" rel="stylesheet">
    <style>
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: var(--bg-primary);
            position: relative;
            overflow: hidden;
        }
        .auth-wrapper::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99,102,241,0.08) 0%, transparent 70%);
            top: -100px;
            right: -100px;
            border-radius: 50%;
        }
        .auth-wrapper::after {
            content: '';
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(139,92,246,0.06) 0%, transparent 70%);
            bottom: -100px;
            left: -100px;
            border-radius: 50%;
        }
        .auth-card {
            width: 100%;
            max-width: 440px;
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-xl);
            padding: 40px;
            box-shadow: var(--shadow-lg);
            position: relative;
            z-index: 1;
        }
        .auth-logo {
            text-align: center;
            margin-bottom: 32px;
        }
        .auth-logo-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 12px;
            box-shadow: 0 4px 20px rgba(99,102,241,0.3);
        }
        .auth-logo h1 {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #6366f1, #8b5cf6, #a855f7);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .auth-logo p { color: var(--text-secondary); font-size: 0.88rem; }
        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            color: var(--text-tertiary);
            font-size: 0.8rem;
        }
        .auth-divider::before, .auth-divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border-color);
        }
        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-logo">
                <div class="auth-logo-icon"><i class="bi bi-chat-dots-fill"></i></div>
                <h1><?= defined('APP_NAME') ? APP_NAME : 'BulkSMS Pro' ?></h1>
                <p><?= $pageSubtitle ?? 'Enterprise SMS Platform' ?></p>
            </div>

            <?php
            $flash_error = \App\Core\Session::getFlash('error');
            $flash_success = \App\Core\Session::getFlash('success');
            ?>
            <?php if ($flash_error): ?>
                <div class="alert alert-danger"><i class="bi bi-x-circle-fill"></i> <?= htmlspecialchars($flash_error) ?></div>
            <?php endif; ?>
            <?php if ($flash_success): ?>
                <div class="alert alert-success"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($flash_success) ?></div>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/public/js/app.js"></script>
</body>
</html>
