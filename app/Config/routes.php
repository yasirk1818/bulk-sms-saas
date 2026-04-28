<?php
/**
 * BulkSMS Pro - Route Definitions
 */

/** @var \App\Core\Router $router */

// ==========================================
// PUBLIC ROUTES
// ==========================================
$router->get('/', 'Auth\AuthController@loginPage');
$router->get('/login', 'Auth\AuthController@loginPage');
$router->post('/login', 'Auth\AuthController@login');
$router->get('/register', 'Auth\AuthController@registerPage');
$router->post('/register', 'Auth\AuthController@register');
$router->get('/forgot-password', 'Auth\AuthController@forgotPasswordPage');
$router->post('/forgot-password', 'Auth\AuthController@forgotPassword');
$router->get('/reset-password/{token}', 'Auth\AuthController@resetPasswordPage');
$router->post('/reset-password', 'Auth\AuthController@resetPassword');
$router->get('/logout', 'Auth\AuthController@logout');

// OTP
$router->get('/verify-otp', 'Auth\OtpController@verifyPage');
$router->post('/verify-otp', 'Auth\OtpController@verify');
$router->post('/resend-otp', 'Auth\OtpController@resend');

// ==========================================
// DASHBOARD (Auth Required)
// ==========================================
$router->group(['prefix' => '', 'middleware' => [\App\Middleware\AuthMiddleware::class]], function ($router) {
    $router->get('/dashboard', 'DashboardController@index');

    // AJAX routes
    $router->post('/ajax/theme', 'AjaxController@updateTheme');
    $router->get('/ajax/notifications/unread-count', 'AjaxController@unreadNotificationCount');
    $router->get('/ajax/notifications', 'AjaxController@getNotifications');
    $router->post('/ajax/notifications/mark-read', 'AjaxController@markNotificationsRead');
    $router->get('/ajax/dashboard/stats', 'AjaxController@dashboardStats');

    // Profile
    $router->get('/profile', 'User\ProfileController@index');
    $router->post('/profile', 'User\ProfileController@update');
    $router->post('/profile/password', 'User\ProfileController@changePassword');

    // SMS Sending
    $router->get('/sms/send', 'User\SmsController@sendPage');
    $router->post('/sms/send', 'User\SmsController@send');
    $router->get('/sms/bulk', 'User\SmsController@bulkPage');
    $router->post('/sms/bulk', 'User\SmsController@sendBulk');
    $router->get('/sms/history', 'User\SmsController@history');
    $router->get('/sms/scheduled', 'User\SmsController@scheduled');
    $router->post('/sms/schedule', 'User\SmsController@schedule');

    // Campaigns
    $router->get('/campaigns', 'User\CampaignController@index');
    $router->get('/campaigns/create', 'User\CampaignController@create');
    $router->post('/campaigns', 'User\CampaignController@store');
    $router->get('/campaigns/{id}', 'User\CampaignController@show');
    $router->get('/campaigns/{id}/edit', 'User\CampaignController@edit');
    $router->post('/campaigns/{id}', 'User\CampaignController@update');
    $router->post('/campaigns/{id}/delete', 'User\CampaignController@delete');

    // Contacts
    $router->get('/contacts', 'User\ContactController@index');
    $router->post('/contacts', 'User\ContactController@store');
    $router->post('/contacts/{id}/update', 'User\ContactController@update');
    $router->post('/contacts/{id}/delete', 'User\ContactController@delete');
    $router->get('/contacts/groups', 'User\ContactController@groups');
    $router->post('/contacts/groups', 'User\ContactController@storeGroup');
    $router->post('/contacts/import', 'User\ContactController@import');
    $router->get('/contacts/export', 'User\ContactController@export');

    // Templates
    $router->get('/templates', 'User\TemplateController@index');
    $router->post('/templates', 'User\TemplateController@store');
    $router->post('/templates/{id}/update', 'User\TemplateController@update');
    $router->post('/templates/{id}/delete', 'User\TemplateController@delete');

    // Sender IDs
    $router->get('/sender-ids', 'User\SenderIdController@index');
    $router->post('/sender-ids/request', 'User\SenderIdController@request');

    // Reports
    $router->get('/reports', 'User\ReportController@index');
    $router->get('/reports/export', 'User\ReportController@export');

    // API Keys
    $router->get('/api-keys', 'User\ApiKeyController@index');
    $router->post('/api-keys/generate', 'User\ApiKeyController@generate');
    $router->post('/api-keys/{id}/revoke', 'User\ApiKeyController@revoke');

    // Webhooks
    $router->get('/webhooks', 'User\WebhookController@index');
    $router->post('/webhooks', 'User\WebhookController@store');
    $router->post('/webhooks/{id}/update', 'User\WebhookController@update');
    $router->post('/webhooks/{id}/delete', 'User\WebhookController@delete');

    // Support Tickets
    $router->get('/tickets', 'User\TicketController@index');
    $router->get('/tickets/create', 'User\TicketController@create');
    $router->post('/tickets', 'User\TicketController@store');
    $router->get('/tickets/{id}', 'User\TicketController@show');
    $router->post('/tickets/{id}/reply', 'User\TicketController@reply');
});

// ==========================================
// ADMIN ROUTES
// ==========================================
$router->group(['prefix' => '/admin', 'middleware' => [\App\Middleware\AdminMiddleware::class]], function ($router) {
    $router->get('/', 'Admin\DashboardController@index');
    $router->get('/dashboard', 'Admin\DashboardController@index');

    // User Management
    $router->get('/users', 'Admin\UserController@index');
    $router->get('/users/create', 'Admin\UserController@create');
    $router->post('/users', 'Admin\UserController@store');
    $router->get('/users/{id}', 'Admin\UserController@show');
    $router->get('/users/{id}/edit', 'Admin\UserController@edit');
    $router->post('/users/{id}', 'Admin\UserController@update');
    $router->post('/users/{id}/delete', 'Admin\UserController@delete');
    $router->post('/users/{id}/toggle-status', 'Admin\UserController@toggleStatus');
    $router->post('/users/{id}/add-credits', 'Admin\UserController@addCredits');

    // Gateway Management
    $router->get('/gateways', 'Admin\GatewayController@index');
    $router->get('/gateways/create', 'Admin\GatewayController@create');
    $router->post('/gateways', 'Admin\GatewayController@store');
    $router->get('/gateways/{id}/edit', 'Admin\GatewayController@edit');
    $router->post('/gateways/{id}', 'Admin\GatewayController@update');
    $router->post('/gateways/{id}/delete', 'Admin\GatewayController@delete');
    $router->post('/gateways/{id}/toggle', 'Admin\GatewayController@toggle');
    $router->post('/gateways/{id}/test', 'Admin\GatewayController@test');

    // SMS Queue
    $router->get('/queue', 'Admin\QueueController@index');
    $router->post('/queue/pause', 'Admin\QueueController@pause');
    $router->post('/queue/resume', 'Admin\QueueController@resume');
    $router->post('/queue/retry-all', 'Admin\QueueController@retryAll');
    $router->post('/queue/purge', 'Admin\QueueController@purge');
    $router->post('/queue/{id}/retry', 'Admin\QueueController@retry');


    // Packages
    $router->get('/packages', 'Admin\PackageController@index');
    $router->get('/packages/create', 'Admin\PackageController@create');
    $router->post('/packages', 'Admin\PackageController@store');
    $router->get('/packages/{id}/edit', 'Admin\PackageController@edit');
    $router->post('/packages/{id}', 'Admin\PackageController@update');
    $router->post('/packages/{id}/delete', 'Admin\PackageController@delete');

    // Templates Approval
    $router->get('/templates', 'Admin\TemplateController@index');
    $router->post('/templates/{id}/approve', 'Admin\TemplateController@approve');
    $router->post('/templates/{id}/reject', 'Admin\TemplateController@reject');

    // Sender ID Approval
    $router->get('/sender-ids', 'Admin\SenderIdController@index');
    $router->post('/sender-ids/{id}/approve', 'Admin\SenderIdController@approve');
    $router->post('/sender-ids/{id}/reject', 'Admin\SenderIdController@reject');

    // Reports
    $router->get('/reports', 'Admin\ReportController@index');

    $router->get('/reports/export', 'Admin\ReportController@export');

    // Support Tickets
    $router->get('/tickets', 'Admin\TicketController@index');
    $router->get('/tickets/{id}', 'Admin\TicketController@show');
    $router->post('/tickets/{id}/reply', 'Admin\TicketController@reply');
    $router->post('/tickets/{id}/close', 'Admin\TicketController@close');

    // Settings
    $router->get('/settings', 'Admin\SettingsController@index');
    $router->post('/settings', 'Admin\SettingsController@update');


    // Logs
    $router->get('/logs/audit', 'Admin\LogController@audit');
    $router->get('/logs/system', 'Admin\LogController@system');
    $router->get('/logs/api', 'Admin\LogController@api');

    // Blacklist
    $router->get('/blacklist', 'Admin\BlacklistController@index');
    $router->post('/blacklist', 'Admin\BlacklistController@store');
    $router->post('/blacklist/{id}/delete', 'Admin\BlacklistController@delete');

    // Announcements
    $router->get('/announcements', 'Admin\AnnouncementController@index');
    $router->post('/announcements', 'Admin\AnnouncementController@store');
    $router->post('/announcements/{id}/delete', 'Admin\AnnouncementController@delete');

    // Backup
    $router->get('/backups', 'Admin\BackupController@index');
    $router->post('/backups/create', 'Admin\BackupController@create');
    $router->get('/backups/{id}/download', 'Admin\BackupController@download');

    $router->post('/backups/{id}/delete', 'Admin\BackupController@delete');

    // Maintenance
    $router->post('/maintenance/toggle', 'Admin\SettingsController@maintenance');

    // GDPR
    $router->get('/gdpr', 'Admin\GdprController@index');
    $router->post('/gdpr/{id}/process', 'Admin\GdprController@processRequest');

    // Billing
    $router->get('/billing', 'Admin\BillingController@index');

    $router->post('/billing/manual-payment', 'Admin\BillingController@manualPayment');
});

// ==========================================
// API ROUTES
// ==========================================
$router->group(['prefix' => '/api/v1'], function ($router) {
    $router->post('/sms/send', 'Api\SmsApiController@send');
    $router->post('/sms/bulk', 'Api\SmsApiController@sendBulk');
    $router->get('/sms/status/{id}', 'Api\SmsApiController@status');
    $router->get('/balance', 'Api\SmsApiController@balance');
    $router->get('/contacts', 'Api\ContactApiController@index');
    $router->post('/contacts', 'Api\ContactApiController@store');
    $router->get('/groups', 'Api\ContactApiController@groups');
    $router->get('/health', 'Api\HealthController@check');
});

// DLR Callback
$router->post('/dlr/callback/{gateway}', 'Api\DlrController@callback');
$router->get('/dlr/callback/{gateway}', 'Api\DlrController@callback');
