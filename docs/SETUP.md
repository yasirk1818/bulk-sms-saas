# BulkSMS Pro - Setup Guide

## Server Requirements

- **PHP 8.0+** (8.1+ recommended)
- **MySQL 5.7+** or **MariaDB 10.3+**
- **Apache** with `mod_rewrite` enabled
- **cPanel** shared hosting compatible

### Required PHP Extensions:
- PDO + PDO_MySQL
- cURL
- JSON
- OpenSSL
- Mbstring
- FileInfo
- GD

## Installation

### 1. Upload Files
Upload all project files to your server. The `public` folder should be your web root.

For cPanel:
- Upload to `public_html/bulksms/` (or your desired directory)
- Point your domain to the `public` subfolder (or use `.htaccess` redirect)

### 2. Run Auto-Installer
Navigate to `https://yourdomain.com/install/` in your browser.

The installer will guide you through:
1. **Server Requirements Check** - Verifies PHP version, extensions, and folder permissions
2. **Database Configuration** - Enter your MySQL credentials
3. **Admin Account Setup** - Create the super admin account
4. **Completion** - Configuration file generated and installation locked

### 3. Configure Cron Jobs

Add the following cron jobs in your cPanel Cron Jobs section:

```bash
# Queue Worker - Process SMS queue (every minute)
* * * * * /usr/bin/php /home/user/public_html/bulksms/cron/queue_worker.php >> /home/user/public_html/bulksms/storage/logs/queue.log 2>&1

# Campaign Scheduler - Process scheduled campaigns (every minute)
* * * * * /usr/bin/php /home/user/public_html/bulksms/cron/campaign_scheduler.php >> /home/user/public_html/bulksms/storage/logs/campaigns.log 2>&1

# Subscription Checker - Check expired subscriptions (hourly)
0 * * * * /usr/bin/php /home/user/public_html/bulksms/cron/subscription_checker.php >> /home/user/public_html/bulksms/storage/logs/subscriptions.log 2>&1

# Gateway Health Monitor (every 5 minutes)
*/5 * * * * /usr/bin/php /home/user/public_html/bulksms/cron/gateway_health.php >> /home/user/public_html/bulksms/storage/logs/health.log 2>&1
```

### 4. Configure Gateways
1. Login to admin panel
2. Go to **Gateways** > **Add Gateway**
3. Configure your SMS provider (Twilio, Vonage, etc.)
4. Test the gateway connection
5. Set it as active

## Directory Structure

```
bulksms/
├── app/
│   ├── Config/         # Configuration files
│   ├── Controllers/    # Request handlers
│   ├── Core/           # Framework core (Router, DB, Session, etc.)
│   ├── Helpers/        # Utility classes
│   ├── Middleware/      # Request middleware
│   └── Views/          # Template files
├── cron/               # Cron job scripts
├── database/           # SQL schema
├── docs/               # Documentation
├── install/            # Auto-installer
├── public/             # Web root (CSS, JS, images)
└── storage/            # Logs, backups, cache
```

## Security Notes

- Change `SECURITY_KEY` in `app/Config/config.php` after installation
- Use HTTPS in production
- Set proper file permissions (755 for directories, 644 for files)
- `storage/` directory should not be web-accessible
- Rate limiting is enabled by default
- CSRF protection on all forms
- Prepared statements for all database queries

## Troubleshooting

### 404 errors
Ensure `mod_rewrite` is enabled and `.htaccess` files are being read.

### Permission denied
```bash
chmod -R 755 storage/
chmod 644 app/Config/config.php
```

### Queue not processing
Check cron job is running: `crontab -l`
Check queue log: `tail -f storage/logs/queue.log`
