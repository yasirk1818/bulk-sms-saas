<?php
use App\Core\Session;
use App\Core\Csrf;

$user = Session::getUser();
$role = Session::userRole();
$isAdmin = in_array($role, ['super_admin', 'admin']);
$pageTitle = $pageTitle ?? 'Dashboard';
$currentPath = '/' . trim($_GET['url'] ?? '', '/');
?>
<!DOCTYPE html>
<html lang="<?= $user['language'] ?? 'en' ?>" data-theme="<?= $user['theme'] ?? 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="csrf-token" content="<?= Csrf::token() ?>">
    <title><?= htmlspecialchars($pageTitle) ?> - <?= defined('APP_NAME') ? APP_NAME : 'BulkSMS Pro' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="/public/css/app.css" rel="stylesheet">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ((array)$extraCss as $css): ?>
            <link href="<?= $css ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
    <link rel="manifest" href="/public/manifest.json">
    <meta name="theme-color" content="#0a0a0f">
</head>
<body>
    <div class="app-wrapper">
        <!-- Sidebar Overlay (Mobile) -->
        <div class="sidebar-overlay"></div>

        <!-- Sidebar -->
        <aside class="app-sidebar" id="sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-brand-icon"><i class="bi bi-chat-dots-fill"></i></div>
                <span class="sidebar-brand-text"><?= defined('APP_NAME') ? APP_NAME : 'BulkSMS Pro' ?></span>
            </div>

            <nav class="sidebar-nav">
                <?php if ($isAdmin && str_starts_with($currentPath, '/admin')): ?>
                    <!-- Admin Navigation -->
                    <div class="nav-section-title">Main</div>
                    <a href="/admin/dashboard" class="nav-item <?= $currentPath === '/admin/dashboard' || $currentPath === '/admin' ? 'active' : '' ?>">
                        <i class="bi bi-grid-1x2-fill"></i><span class="nav-item-text">Dashboard</span>
                    </a>

                    <div class="nav-section-title">Management</div>
                    <a href="/admin/users" class="nav-item <?= str_starts_with($currentPath, '/admin/users') ? 'active' : '' ?>">
                        <i class="bi bi-people-fill"></i><span class="nav-item-text">Users</span>
                    </a>
                    <a href="/admin/gateways" class="nav-item <?= str_starts_with($currentPath, '/admin/gateways') ? 'active' : '' ?>">
                        <i class="bi bi-hdd-network-fill"></i><span class="nav-item-text">Gateways</span>
                    </a>
                    <a href="/admin/queue" class="nav-item <?= str_starts_with($currentPath, '/admin/queue') ? 'active' : '' ?>">
                        <i class="bi bi-collection-fill"></i><span class="nav-item-text">SMS Queue</span>
                    </a>
                    <a href="/admin/packages" class="nav-item <?= str_starts_with($currentPath, '/admin/packages') ? 'active' : '' ?>">
                        <i class="bi bi-box-fill"></i><span class="nav-item-text">Packages</span>
                    </a>

                    <div class="nav-section-title">Approval</div>
                    <a href="/admin/templates" class="nav-item <?= str_starts_with($currentPath, '/admin/templates') ? 'active' : '' ?>">
                        <i class="bi bi-file-earmark-text-fill"></i><span class="nav-item-text">Templates</span>
                    </a>
                    <a href="/admin/sender-ids" class="nav-item <?= str_starts_with($currentPath, '/admin/sender-ids') ? 'active' : '' ?>">
                        <i class="bi bi-person-badge-fill"></i><span class="nav-item-text">Sender IDs</span>
                    </a>

                    <div class="nav-section-title">Analytics</div>
                    <a href="/admin/reports" class="nav-item <?= str_starts_with($currentPath, '/admin/reports') ? 'active' : '' ?>">
                        <i class="bi bi-graph-up"></i><span class="nav-item-text">Reports</span>
                    </a>
                    <a href="/admin/billing" class="nav-item <?= str_starts_with($currentPath, '/admin/billing') ? 'active' : '' ?>">
                        <i class="bi bi-wallet2"></i><span class="nav-item-text">Billing</span>
                    </a>

                    <div class="nav-section-title">Support</div>
                    <a href="/admin/tickets" class="nav-item <?= str_starts_with($currentPath, '/admin/tickets') ? 'active' : '' ?>">
                        <i class="bi bi-ticket-detailed-fill"></i><span class="nav-item-text">Tickets</span>
                    </a>
                    <a href="/admin/announcements" class="nav-item <?= str_starts_with($currentPath, '/admin/announcements') ? 'active' : '' ?>">
                        <i class="bi bi-megaphone-fill"></i><span class="nav-item-text">Announcements</span>
                    </a>

                    <div class="nav-section-title">System</div>
                    <a href="/admin/settings" class="nav-item <?= str_starts_with($currentPath, '/admin/settings') ? 'active' : '' ?>">
                        <i class="bi bi-gear-fill"></i><span class="nav-item-text">Settings</span>
                    </a>
                    <a href="/admin/blacklist" class="nav-item <?= str_starts_with($currentPath, '/admin/blacklist') ? 'active' : '' ?>">
                        <i class="bi bi-shield-fill-x"></i><span class="nav-item-text">Blacklist</span>
                    </a>
                    <a href="/admin/logs/audit" class="nav-item <?= str_starts_with($currentPath, '/admin/logs') ? 'active' : '' ?>">
                        <i class="bi bi-journal-text"></i><span class="nav-item-text">Logs</span>
                    </a>
                    <a href="/admin/backups" class="nav-item <?= str_starts_with($currentPath, '/admin/backups') ? 'active' : '' ?>">
                        <i class="bi bi-cloud-arrow-down-fill"></i><span class="nav-item-text">Backups</span>
                    </a>
                    <a href="/admin/gdpr" class="nav-item <?= str_starts_with($currentPath, '/admin/gdpr') ? 'active' : '' ?>">
                        <i class="bi bi-shield-lock-fill"></i><span class="nav-item-text">GDPR</span>
                    </a>

                    <div class="nav-section-title">Switch</div>
                    <a href="/dashboard" class="nav-item">
                        <i class="bi bi-arrow-left-circle"></i><span class="nav-item-text">User Panel</span>
                    </a>
                <?php else: ?>
                    <!-- User Navigation -->
                    <div class="nav-section-title">Main</div>
                    <a href="/dashboard" class="nav-item <?= $currentPath === '/dashboard' ? 'active' : '' ?>">
                        <i class="bi bi-grid-1x2-fill"></i><span class="nav-item-text">Dashboard</span>
                    </a>

                    <div class="nav-section-title">Messaging</div>
                    <a href="/sms/send" class="nav-item <?= $currentPath === '/sms/send' ? 'active' : '' ?>">
                        <i class="bi bi-send-fill"></i><span class="nav-item-text">Send SMS</span>
                    </a>
                    <a href="/sms/bulk" class="nav-item <?= $currentPath === '/sms/bulk' ? 'active' : '' ?>">
                        <i class="bi bi-envelope-paper-fill"></i><span class="nav-item-text">Bulk SMS</span>
                    </a>
                    <a href="/campaigns" class="nav-item <?= str_starts_with($currentPath, '/campaigns') ? 'active' : '' ?>">
                        <i class="bi bi-megaphone-fill"></i><span class="nav-item-text">Campaigns</span>
                    </a>
                    <a href="/sms/scheduled" class="nav-item <?= $currentPath === '/sms/scheduled' ? 'active' : '' ?>">
                        <i class="bi bi-clock-fill"></i><span class="nav-item-text">Scheduled</span>
                    </a>
                    <a href="/sms/history" class="nav-item <?= $currentPath === '/sms/history' ? 'active' : '' ?>">
                        <i class="bi bi-clock-history"></i><span class="nav-item-text">SMS History</span>
                    </a>

                    <div class="nav-section-title">Manage</div>
                    <a href="/contacts" class="nav-item <?= str_starts_with($currentPath, '/contacts') ? 'active' : '' ?>">
                        <i class="bi bi-person-lines-fill"></i><span class="nav-item-text">Contacts</span>
                    </a>
                    <a href="/contacts/groups" class="nav-item <?= $currentPath === '/contacts/groups' ? 'active' : '' ?>">
                        <i class="bi bi-people-fill"></i><span class="nav-item-text">Groups</span>
                    </a>
                    <a href="/templates" class="nav-item <?= str_starts_with($currentPath, '/templates') ? 'active' : '' ?>">
                        <i class="bi bi-file-earmark-text-fill"></i><span class="nav-item-text">Templates</span>
                    </a>
                    <a href="/sender-ids" class="nav-item <?= str_starts_with($currentPath, '/sender-ids') ? 'active' : '' ?>">
                        <i class="bi bi-person-badge-fill"></i><span class="nav-item-text">Sender IDs</span>
                    </a>

                    <div class="nav-section-title">Analytics</div>
                    <a href="/reports" class="nav-item <?= str_starts_with($currentPath, '/reports') ? 'active' : '' ?>">
                        <i class="bi bi-graph-up"></i><span class="nav-item-text">Reports</span>
                    </a>

                    <div class="nav-section-title">Developer</div>
                    <a href="/api-keys" class="nav-item <?= str_starts_with($currentPath, '/api-keys') ? 'active' : '' ?>">
                        <i class="bi bi-key-fill"></i><span class="nav-item-text">API Keys</span>
                    </a>
                    <a href="/webhooks" class="nav-item <?= str_starts_with($currentPath, '/webhooks') ? 'active' : '' ?>">
                        <i class="bi bi-link-45deg"></i><span class="nav-item-text">Webhooks</span>
                    </a>

                    <div class="nav-section-title">Support</div>
                    <a href="/tickets" class="nav-item <?= str_starts_with($currentPath, '/tickets') ? 'active' : '' ?>">
                        <i class="bi bi-ticket-detailed-fill"></i><span class="nav-item-text">Support</span>
                    </a>

                    <?php if ($isAdmin): ?>
                        <div class="nav-section-title">Admin</div>
                        <a href="/admin/dashboard" class="nav-item">
                            <i class="bi bi-speedometer2"></i><span class="nav-item-text">Admin Panel</span>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>

            <div class="sidebar-footer">
                <a href="/profile" class="nav-item">
                    <i class="bi bi-person-circle"></i><span class="nav-item-text">Profile</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="app-main">
            <!-- Header -->
            <header class="app-header">
                <button class="header-toggle" id="sidebarToggle">
                    <i class="bi bi-list"></i>
                </button>

                <div class="header-search">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Search anything..." autocomplete="off">
                </div>

                <div class="header-actions">
                    <!-- Theme Toggle -->
                    <div class="theme-switch theme-toggle" title="Toggle theme"></div>

                    <!-- Notifications -->
                    <div class="dropdown">
                        <button class="header-action-btn" data-bs-toggle="dropdown">
                            <i class="bi bi-bell"></i>
                            <span class="badge-dot notification-dot" style="display:none"></span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notifications-dropdown">
                            <div class="dropdown-header">
                                <span>Notifications</span>
                                <a href="#" class="fs-xs text-accent" onclick="App.ajax('/ajax/notifications/mark-read',{method:'POST'})">Mark all read</a>
                            </div>
                            <div id="notificationsList">
                                <div class="empty-state py-4">
                                    <p class="fs-sm text-muted">No new notifications</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <div class="user-dropdown" data-bs-toggle="dropdown">
                            <div class="user-avatar"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
                            <div class="user-info d-none d-md-block">
                                <div class="user-info-name"><?= htmlspecialchars($user['name'] ?? 'User') ?></div>
                                <div class="user-info-role"><?= str_replace('_', ' ', $role ?? 'user') ?></div>
                            </div>
                        </div>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="/profile"><i class="bi bi-person"></i> Profile</a>
                            <?php if ($isAdmin): ?>
                                <a class="dropdown-item" href="/admin/settings"><i class="bi bi-gear"></i> Settings</a>
                            <?php endif; ?>
                            <div class="dropdown-divider"></div>
                            <div class="dropdown-item text-muted fs-xs">
                                <i class="bi bi-wallet2"></i> Balance: <?= number_format((float)($user['sms_balance'] ?? 0), 2) ?> SMS
                            </div>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item text-danger" href="/logout"><i class="bi bi-box-arrow-right"></i> Logout</a>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Flash Messages -->
            <?php $flash_success = Session::getFlash('success'); ?>
            <?php $flash_error = Session::getFlash('error'); ?>
            <?php $flash_warning = Session::getFlash('warning'); ?>
            <?php if ($flash_success): ?>
                <div class="alert alert-success alert-auto-dismiss m-3 mb-0 fade-in"><i class="bi bi-check-circle-fill"></i> <?= htmlspecialchars($flash_success) ?></div>
            <?php endif; ?>
            <?php if ($flash_error): ?>
                <div class="alert alert-danger alert-auto-dismiss m-3 mb-0 fade-in"><i class="bi bi-x-circle-fill"></i> <?= htmlspecialchars($flash_error) ?></div>
            <?php endif; ?>
            <?php if ($flash_warning): ?>
                <div class="alert alert-warning alert-auto-dismiss m-3 mb-0 fade-in"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($flash_warning) ?></div>
            <?php endif; ?>

            <!-- Page Content -->
            <main class="app-content fade-in">
                <?= $content ?? '' ?>
            </main>

            <!-- Footer -->
            <footer class="app-footer">
                <span>&copy; <?= date('Y') ?> <?= defined('APP_NAME') ? APP_NAME : 'BulkSMS Pro' ?>. All rights reserved.</span>
                <span>v<?= defined('APP_VERSION') ? APP_VERSION : '1.0.0' ?></span>
            </footer>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="/public/js/app.js"></script>
    <?php if (!empty($extraJs)): ?>
        <?php foreach ((array)$extraJs as $js): ?>
            <script src="<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
    <?php if (!empty($inlineJs)): ?>
        <script><?= $inlineJs ?></script>
    <?php endif; ?>
</body>
</html>
