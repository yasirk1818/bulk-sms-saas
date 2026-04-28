-- ======================================================
-- BulkSMS SaaS Platform - Database Schema
-- Enterprise-grade Bulk SMS Sender
-- ======================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES';

-- ======================================================
-- SETTINGS
-- ======================================================
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(191) NOT NULL UNIQUE,
    `setting_value` TEXT,
    `setting_group` VARCHAR(100) DEFAULT 'general',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_settings_group` (`setting_group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- USERS & AUTHENTICATION
-- ======================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(36) NOT NULL UNIQUE,
    `name` VARCHAR(191) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `phone` VARCHAR(20) DEFAULT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('super_admin','admin','reseller','staff','user') DEFAULT 'user',
    `status` ENUM('active','inactive','suspended','banned') DEFAULT 'active',
    `avatar` VARCHAR(500) DEFAULT NULL,
    `email_verified_at` DATETIME DEFAULT NULL,
    `phone_verified_at` DATETIME DEFAULT NULL,
    `two_factor_enabled` TINYINT(1) DEFAULT 0,
    `two_factor_secret` VARCHAR(255) DEFAULT NULL,
    `sms_balance` DECIMAL(15,4) DEFAULT 0.0000,
    `credit_limit` DECIMAL(15,4) DEFAULT 0.0000,
    `parent_id` INT UNSIGNED DEFAULT NULL,
    `package_id` INT UNSIGNED DEFAULT NULL,
    `package_expires_at` DATETIME DEFAULT NULL,
    `timezone` VARCHAR(100) DEFAULT 'UTC',
    `language` VARCHAR(10) DEFAULT 'en',
    `theme` ENUM('dark','light') DEFAULT 'dark',
    `daily_sms_limit` INT DEFAULT 0,
    `monthly_sms_limit` INT DEFAULT 0,
    `api_enabled` TINYINT(1) DEFAULT 1,
    `last_login_at` DATETIME DEFAULT NULL,
    `last_login_ip` VARCHAR(45) DEFAULT NULL,
    `login_attempts` INT DEFAULT 0,
    `locked_until` DATETIME DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_status` (`status`),
    INDEX `idx_users_parent` (`parent_id`),
    INDEX `idx_users_email` (`email`),
    INDEX `idx_users_package` (`package_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `api_keys` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `api_key` VARCHAR(64) NOT NULL UNIQUE,
    `api_secret` VARCHAR(128) NOT NULL,
    `permissions` JSON DEFAULT NULL,
    `ip_whitelist` TEXT DEFAULT NULL,
    `rate_limit` INT DEFAULT 60,
    `is_active` TINYINT(1) DEFAULT 1,
    `last_used_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_api_keys_user` (`user_id`),
    INDEX `idx_api_keys_key` (`api_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `email` VARCHAR(191) DEFAULT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `device_type` VARCHAR(50) DEFAULT NULL,
    `browser` VARCHAR(100) DEFAULT NULL,
    `os` VARCHAR(100) DEFAULT NULL,
    `location` VARCHAR(255) DEFAULT NULL,
    `status` ENUM('success','failed','blocked','locked') DEFAULT 'failed',
    `failure_reason` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_login_user` (`user_id`),
    INDEX `idx_login_ip` (`ip_address`),
    INDEX `idx_login_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(191) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `used` TINYINT(1) DEFAULT 0,
    `expires_at` DATETIME NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_reset_email` (`email`),
    INDEX `idx_reset_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- OTP SYSTEM
-- ======================================================
CREATE TABLE IF NOT EXISTS `otp_codes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `identifier` VARCHAR(191) NOT NULL,
    `code` VARCHAR(10) NOT NULL,
    `type` ENUM('sms','email') DEFAULT 'email',
    `purpose` ENUM('login','register','password_reset','verification','transaction') DEFAULT 'login',
    `attempts` INT DEFAULT 0,
    `max_attempts` INT DEFAULT 5,
    `is_used` TINYINT(1) DEFAULT 0,
    `expires_at` DATETIME NOT NULL,
    `verified_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_otp_identifier` (`identifier`),
    INDEX `idx_otp_code` (`code`),
    INDEX `idx_otp_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- GATEWAYS
-- ======================================================
CREATE TABLE IF NOT EXISTS `gateways` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(191) NOT NULL,
    `slug` VARCHAR(191) NOT NULL UNIQUE,
    `type` ENUM('http_get','http_post','json_api','smpp') DEFAULT 'json_api',
    `api_url` VARCHAR(500) NOT NULL,
    `api_key` VARCHAR(500) DEFAULT NULL,
    `api_secret` VARCHAR(500) DEFAULT NULL,
    `extra_params` JSON DEFAULT NULL,
    `headers` JSON DEFAULT NULL,
    `sender_id_param` VARCHAR(100) DEFAULT NULL,
    `recipient_param` VARCHAR(100) DEFAULT NULL,
    `message_param` VARCHAR(100) DEFAULT NULL,
    `success_response` VARCHAR(500) DEFAULT NULL,
    `dlr_url` VARCHAR(500) DEFAULT NULL,
    `default_sender_id` VARCHAR(20) DEFAULT NULL,
    `cost_per_sms` DECIMAL(10,6) DEFAULT 0.000000,
    `cost_currency` VARCHAR(3) DEFAULT 'USD',
    `max_parts` INT DEFAULT 10,
    `rate_limit` INT DEFAULT 30,
    `timeout` INT DEFAULT 30,
    `retry_attempts` INT DEFAULT 3,
    `retry_delay` INT DEFAULT 60,
    `supports_unicode` TINYINT(1) DEFAULT 1,
    `supports_dlr` TINYINT(1) DEFAULT 1,
    `supports_bulk` TINYINT(1) DEFAULT 1,
    `priority` INT DEFAULT 50,
    `status` ENUM('active','inactive','failing') DEFAULT 'active',
    `is_default` TINYINT(1) DEFAULT 0,
    `countries` JSON DEFAULT NULL,
    `total_sent` INT DEFAULT 0,
    `total_delivered` INT DEFAULT 0,
    `total_failed` INT DEFAULT 0,
    `avg_response_time` DECIMAL(10,2) DEFAULT 0,
    `uptime_percentage` DECIMAL(5,2) DEFAULT 100.00,
    `last_success_at` DATETIME DEFAULT NULL,
    `last_failure_at` DATETIME DEFAULT NULL,
    `last_error` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_gateways_status` (`status`),
    INDEX `idx_gateways_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gateway_routes` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `gateway_id` INT UNSIGNED NOT NULL,
    `country_code` VARCHAR(5) DEFAULT NULL,
    `operator` VARCHAR(100) DEFAULT NULL,
    `priority` INT DEFAULT 50,
    `cost_override` DECIMAL(10,6) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_route_gateway` (`gateway_id`),
    INDEX `idx_route_country` (`country_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gateway_health_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `gateway_id` INT UNSIGNED NOT NULL,
    `status` ENUM('up','down','degraded') DEFAULT 'up',
    `response_time` DECIMAL(10,2) DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    `checked_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_health_gateway` (`gateway_id`),
    INDEX `idx_health_checked` (`checked_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- CONTACTS
-- ======================================================
CREATE TABLE IF NOT EXISTS `contact_groups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `contacts_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_groups_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contacts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `name` VARCHAR(191) DEFAULT NULL,
    `email` VARCHAR(191) DEFAULT NULL,
    `country_code` VARCHAR(5) DEFAULT NULL,
    `company` VARCHAR(191) DEFAULT NULL,
    `tags` VARCHAR(500) DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `custom_fields` JSON DEFAULT NULL,
    `is_blacklisted` TINYINT(1) DEFAULT 0,
    `is_valid` TINYINT(1) DEFAULT 1,
    `opt_out` TINYINT(1) DEFAULT 0,
    `opt_out_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_contacts_user` (`user_id`),
    INDEX `idx_contacts_phone` (`phone`),
    INDEX `idx_contacts_country` (`country_code`),
    UNIQUE KEY `uk_user_phone` (`user_id`, `phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contact_group_members` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `contact_id` INT UNSIGNED NOT NULL,
    `group_id` INT UNSIGNED NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_contact_group` (`contact_id`, `group_id`),
    INDEX `idx_cgm_group` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- SENDER IDS
-- ======================================================
CREATE TABLE IF NOT EXISTS `sender_ids` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `sender_id` VARCHAR(20) NOT NULL,
    `purpose` TEXT DEFAULT NULL,
    `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
    `admin_notes` TEXT DEFAULT NULL,
    `approved_by` INT UNSIGNED DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `countries` JSON DEFAULT NULL,
    `usage_count` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sender_user` (`user_id`),
    INDEX `idx_sender_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- TEMPLATES
-- ======================================================
CREATE TABLE IF NOT EXISTS `sms_templates` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `content` TEXT NOT NULL,
    `category` VARCHAR(100) DEFAULT NULL,
    `variables` JSON DEFAULT NULL,
    `status` ENUM('pending','approved','rejected') DEFAULT 'pending',
    `admin_notes` TEXT DEFAULT NULL,
    `approved_by` INT UNSIGNED DEFAULT NULL,
    `approved_at` DATETIME DEFAULT NULL,
    `usage_count` INT DEFAULT 0,
    `version` INT DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_template_user` (`user_id`),
    INDEX `idx_template_status` (`status`),
    INDEX `idx_template_category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `template_versions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `template_id` INT UNSIGNED NOT NULL,
    `content` TEXT NOT NULL,
    `version` INT NOT NULL,
    `changed_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_tv_template` (`template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- CAMPAIGNS
-- ======================================================
CREATE TABLE IF NOT EXISTS `campaigns` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(191) NOT NULL,
    `message` TEXT NOT NULL,
    `sender_id` VARCHAR(20) DEFAULT NULL,
    `type` ENUM('instant','scheduled','recurring') DEFAULT 'instant',
    `status` ENUM('draft','scheduled','running','paused','completed','cancelled','failed') DEFAULT 'draft',
    `gateway_id` INT UNSIGNED DEFAULT NULL,
    `template_id` INT UNSIGNED DEFAULT NULL,
    `group_ids` JSON DEFAULT NULL,
    `contact_source` ENUM('groups','import','manual') DEFAULT 'manual',
    `total_recipients` INT DEFAULT 0,
    `total_sent` INT DEFAULT 0,
    `total_delivered` INT DEFAULT 0,
    `total_failed` INT DEFAULT 0,
    `total_pending` INT DEFAULT 0,
    `total_cost` DECIMAL(15,4) DEFAULT 0.0000,
    `scheduled_at` DATETIME DEFAULT NULL,
    `recurring_pattern` VARCHAR(100) DEFAULT NULL,
    `recurring_end_at` DATETIME DEFAULT NULL,
    `started_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_campaign_user` (`user_id`),
    INDEX `idx_campaign_status` (`status`),
    INDEX `idx_campaign_scheduled` (`scheduled_at`),
    INDEX `idx_campaign_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- SMS QUEUE & MESSAGES
-- ======================================================
CREATE TABLE IF NOT EXISTS `sms_queue` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(36) NOT NULL UNIQUE,
    `user_id` INT UNSIGNED NOT NULL,
    `campaign_id` INT UNSIGNED DEFAULT NULL,
    `gateway_id` INT UNSIGNED DEFAULT NULL,
    `sender_id` VARCHAR(20) DEFAULT NULL,
    `recipient` VARCHAR(20) NOT NULL,
    `message` TEXT NOT NULL,
    `message_type` ENUM('plain','unicode') DEFAULT 'plain',
    `parts` INT DEFAULT 1,
    `priority` TINYINT DEFAULT 5,
    `status` ENUM('queued','processing','sent','delivered','failed','cancelled','expired') DEFAULT 'queued',
    `gateway_message_id` VARCHAR(255) DEFAULT NULL,
    `gateway_response` TEXT DEFAULT NULL,
    `cost` DECIMAL(10,6) DEFAULT 0.000000,
    `retry_count` INT DEFAULT 0,
    `max_retries` INT DEFAULT 3,
    `error_message` TEXT DEFAULT NULL,
    `scheduled_at` DATETIME DEFAULT NULL,
    `processing_at` DATETIME DEFAULT NULL,
    `sent_at` DATETIME DEFAULT NULL,
    `delivered_at` DATETIME DEFAULT NULL,
    `failed_at` DATETIME DEFAULT NULL,
    `locked_by` VARCHAR(100) DEFAULT NULL,
    `locked_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_queue_status` (`status`),
    INDEX `idx_queue_user` (`user_id`),
    INDEX `idx_queue_campaign` (`campaign_id`),
    INDEX `idx_queue_gateway` (`gateway_id`),
    INDEX `idx_queue_priority_status` (`priority`, `status`),
    INDEX `idx_queue_scheduled` (`scheduled_at`),
    INDEX `idx_queue_locked` (`locked_by`, `locked_at`),
    INDEX `idx_queue_created` (`created_at`),
    INDEX `idx_queue_recipient` (`recipient`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `sms_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `queue_id` BIGINT UNSIGNED DEFAULT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `campaign_id` INT UNSIGNED DEFAULT NULL,
    `gateway_id` INT UNSIGNED DEFAULT NULL,
    `sender_id` VARCHAR(20) DEFAULT NULL,
    `recipient` VARCHAR(20) NOT NULL,
    `message` TEXT NOT NULL,
    `parts` INT DEFAULT 1,
    `status` ENUM('sent','delivered','failed','rejected') DEFAULT 'sent',
    `gateway_message_id` VARCHAR(255) DEFAULT NULL,
    `gateway_response` TEXT DEFAULT NULL,
    `cost` DECIMAL(10,6) DEFAULT 0.000000,
    `dlr_status` VARCHAR(50) DEFAULT NULL,
    `dlr_received_at` DATETIME DEFAULT NULL,
    `error_code` VARCHAR(50) DEFAULT NULL,
    `error_message` TEXT DEFAULT NULL,
    `sent_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_smslog_user` (`user_id`),
    INDEX `idx_smslog_campaign` (`campaign_id`),
    INDEX `idx_smslog_gateway` (`gateway_id`),
    INDEX `idx_smslog_status` (`status`),
    INDEX `idx_smslog_recipient` (`recipient`),
    INDEX `idx_smslog_created` (`created_at`),
    INDEX `idx_smslog_msgid` (`gateway_message_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- PACKAGES / SUBSCRIPTIONS
-- ======================================================
CREATE TABLE IF NOT EXISTS `packages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(191) NOT NULL,
    `slug` VARCHAR(191) NOT NULL UNIQUE,
    `description` TEXT DEFAULT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `currency` VARCHAR(3) DEFAULT 'USD',
    `sms_quota` INT DEFAULT 0,
    `daily_limit` INT DEFAULT 0,
    `monthly_limit` INT DEFAULT 0,
    `validity_days` INT DEFAULT 30,
    `features` JSON DEFAULT NULL,
    `gateway_access` JSON DEFAULT NULL,
    `api_access` TINYINT(1) DEFAULT 1,
    `max_contacts` INT DEFAULT 1000,
    `max_groups` INT DEFAULT 10,
    `max_templates` INT DEFAULT 20,
    `max_campaigns` INT DEFAULT 10,
    `is_trial` TINYINT(1) DEFAULT 0,
    `is_active` TINYINT(1) DEFAULT 1,
    `sort_order` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_packages_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `subscriptions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `package_id` INT UNSIGNED NOT NULL,
    `status` ENUM('active','expired','cancelled','suspended') DEFAULT 'active',
    `sms_used` INT DEFAULT 0,
    `sms_remaining` INT DEFAULT 0,
    `starts_at` DATETIME NOT NULL,
    `expires_at` DATETIME NOT NULL,
    `cancelled_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_sub_user` (`user_id`),
    INDEX `idx_sub_status` (`status`),
    INDEX `idx_sub_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- BILLING & TRANSACTIONS
-- ======================================================
CREATE TABLE IF NOT EXISTS `transactions` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `uuid` VARCHAR(36) NOT NULL UNIQUE,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('credit','debit','refund','adjustment') DEFAULT 'credit',
    `amount` DECIMAL(15,4) NOT NULL,
    `balance_before` DECIMAL(15,4) DEFAULT 0.0000,
    `balance_after` DECIMAL(15,4) DEFAULT 0.0000,
    `currency` VARCHAR(3) DEFAULT 'USD',
    `description` VARCHAR(500) DEFAULT NULL,
    `reference` VARCHAR(255) DEFAULT NULL,
    `payment_method` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('pending','completed','failed','reversed') DEFAULT 'completed',
    `metadata` JSON DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_txn_user` (`user_id`),
    INDEX `idx_txn_type` (`type`),
    INDEX `idx_txn_status` (`status`),
    INDEX `idx_txn_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `invoices` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `invoice_number` VARCHAR(50) NOT NULL UNIQUE,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(3) DEFAULT 'USD',
    `status` ENUM('draft','sent','paid','overdue','cancelled') DEFAULT 'draft',
    `due_date` DATE DEFAULT NULL,
    `paid_at` DATETIME DEFAULT NULL,
    `items` JSON DEFAULT NULL,
    `notes` TEXT DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_invoice_user` (`user_id`),
    INDEX `idx_invoice_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- WEBHOOKS
-- ======================================================
CREATE TABLE IF NOT EXISTS `webhooks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `url` VARCHAR(500) NOT NULL,
    `events` JSON NOT NULL,
    `secret` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) DEFAULT 1,
    `retry_count` INT DEFAULT 3,
    `last_triggered_at` DATETIME DEFAULT NULL,
    `last_status` VARCHAR(50) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_webhook_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `webhook_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `webhook_id` INT UNSIGNED NOT NULL,
    `event` VARCHAR(100) NOT NULL,
    `payload` JSON DEFAULT NULL,
    `response_code` INT DEFAULT NULL,
    `response_body` TEXT DEFAULT NULL,
    `attempt` INT DEFAULT 1,
    `status` ENUM('success','failed','pending') DEFAULT 'pending',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_whlog_webhook` (`webhook_id`),
    INDEX `idx_whlog_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- SUPPORT TICKETS
-- ======================================================
CREATE TABLE IF NOT EXISTS `tickets` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `ticket_number` VARCHAR(20) NOT NULL UNIQUE,
    `subject` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) DEFAULT 'general',
    `priority` ENUM('low','medium','high','urgent') DEFAULT 'medium',
    `status` ENUM('open','in_progress','waiting','resolved','closed') DEFAULT 'open',
    `assigned_to` INT UNSIGNED DEFAULT NULL,
    `last_reply_at` DATETIME DEFAULT NULL,
    `last_reply_by` ENUM('user','staff') DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_ticket_user` (`user_id`),
    INDEX `idx_ticket_status` (`status`),
    INDEX `idx_ticket_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ticket_replies` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `message` TEXT NOT NULL,
    `attachments` JSON DEFAULT NULL,
    `is_staff_reply` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_reply_ticket` (`ticket_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- NOTIFICATIONS
-- ======================================================
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `title` VARCHAR(255) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info','success','warning','danger') DEFAULT 'info',
    `icon` VARCHAR(50) DEFAULT 'bell',
    `link` VARCHAR(500) DEFAULT NULL,
    `is_read` TINYINT(1) DEFAULT 0,
    `is_global` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_notif_user` (`user_id`),
    INDEX `idx_notif_read` (`is_read`),
    INDEX `idx_notif_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `announcements` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `content` TEXT NOT NULL,
    `type` ENUM('info','warning','maintenance','update') DEFAULT 'info',
    `is_active` TINYINT(1) DEFAULT 1,
    `starts_at` DATETIME DEFAULT NULL,
    `ends_at` DATETIME DEFAULT NULL,
    `created_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- BLACKLIST
-- ======================================================
CREATE TABLE IF NOT EXISTS `blacklist` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `phone` VARCHAR(20) NOT NULL,
    `reason` VARCHAR(255) DEFAULT NULL,
    `is_global` TINYINT(1) DEFAULT 0,
    `added_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_blacklist_phone` (`phone`),
    INDEX `idx_blacklist_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- IP BLOCKING
-- ======================================================
CREATE TABLE IF NOT EXISTS `blocked_ips` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `reason` VARCHAR(255) DEFAULT NULL,
    `blocked_by` INT UNSIGNED DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_blocked_ip` (`ip_address`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- AUDIT LOGS
-- ======================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(255) NOT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `data` JSON DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_action` (`action`),
    INDEX `idx_audit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- SYSTEM LOGS
-- ======================================================
CREATE TABLE IF NOT EXISTS `system_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `level` ENUM('info','warning','error','critical') DEFAULT 'info',
    `channel` VARCHAR(100) DEFAULT 'system',
    `message` TEXT NOT NULL,
    `context` JSON DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_syslog_level` (`level`),
    INDEX `idx_syslog_channel` (`channel`),
    INDEX `idx_syslog_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- IMPORT HISTORY
-- ======================================================
CREATE TABLE IF NOT EXISTS `import_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `file_name` VARCHAR(255) NOT NULL,
    `file_size` INT DEFAULT 0,
    `type` ENUM('contacts','numbers','blacklist') DEFAULT 'contacts',
    `total_rows` INT DEFAULT 0,
    `imported` INT DEFAULT 0,
    `skipped` INT DEFAULT 0,
    `failed` INT DEFAULT 0,
    `errors` JSON DEFAULT NULL,
    `status` ENUM('processing','completed','failed') DEFAULT 'processing',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_import_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- CONSENT / GDPR
-- ======================================================
CREATE TABLE IF NOT EXISTS `consent_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `consent_type` VARCHAR(100) NOT NULL,
    `action` ENUM('granted','revoked') NOT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_consent_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `data_requests` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('export','deletion') NOT NULL,
    `status` ENUM('pending','processing','completed','rejected') DEFAULT 'pending',
    `file_path` VARCHAR(500) DEFAULT NULL,
    `processed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_datareq_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- CREDIT TRANSACTIONS (Atomic Balance)
-- ======================================================
CREATE TABLE IF NOT EXISTS `credit_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `type` ENUM('deduct','refund','topup','adjustment','bonus') NOT NULL,
    `amount` DECIMAL(15,4) NOT NULL,
    `balance_before` DECIMAL(15,4) NOT NULL,
    `balance_after` DECIMAL(15,4) NOT NULL,
    `reference_type` VARCHAR(50) DEFAULT NULL,
    `reference_id` BIGINT UNSIGNED DEFAULT NULL,
    `description` VARCHAR(500) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_credit_user` (`user_id`),
    INDEX `idx_credit_type` (`type`),
    INDEX `idx_credit_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- LANGUAGES
-- ======================================================
CREATE TABLE IF NOT EXISTS `languages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(10) NOT NULL UNIQUE,
    `name` VARCHAR(100) NOT NULL,
    `native_name` VARCHAR(100) DEFAULT NULL,
    `direction` ENUM('ltr','rtl') DEFAULT 'ltr',
    `is_active` TINYINT(1) DEFAULT 1,
    `is_default` TINYINT(1) DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- BACKUPS
-- ======================================================
CREATE TABLE IF NOT EXISTS `backups` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `file_name` VARCHAR(255) NOT NULL,
    `file_size` BIGINT DEFAULT 0,
    `type` ENUM('full','database','files') DEFAULT 'database',
    `status` ENUM('pending','running','completed','failed') DEFAULT 'pending',
    `created_by` INT UNSIGNED DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_backup_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ======================================================
-- DEFAULT DATA INSERTS
-- ======================================================

-- Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_group`) VALUES
('site_name', 'BulkSMS Pro', 'general'),
('site_tagline', 'Enterprise SMS Platform', 'general'),
('site_email', 'admin@bulksmspro.com', 'general'),
('site_phone', '', 'general'),
('timezone', 'UTC', 'general'),
('date_format', 'Y-m-d', 'general'),
('currency', 'USD', 'general'),
('currency_symbol', '$', 'general'),
('registration_enabled', '1', 'auth'),
('email_verification', '1', 'auth'),
('default_role', 'user', 'auth'),
('max_login_attempts', '5', 'auth'),
('lockout_duration', '30', 'auth'),
('otp_enabled', '0', 'otp'),
('otp_login', '0', 'otp'),
('otp_register', '0', 'otp'),
('otp_password_reset', '0', 'otp'),
('otp_expiry', '300', 'otp'),
('otp_resend_limit', '3', 'otp'),
('otp_max_attempts', '5', 'otp'),
('otp_type', 'email', 'otp'),
('default_sender_id', 'BulkSMS', 'sms'),
('sender_id_approval', '1', 'sms'),
('template_approval', '1', 'sms'),
('max_sms_per_request', '10000', 'sms'),
('default_sms_cost', '0.0100', 'sms'),
('unicode_auto_detect', '1', 'sms'),
('duplicate_filter', '1', 'sms'),
('blacklist_filter', '1', 'sms'),
('maintenance_mode', '0', 'system'),
('maintenance_message', 'We are performing scheduled maintenance. Please try again later.', 'system'),
('api_enabled', '1', 'api'),
('api_rate_limit', '60', 'api'),
('webhook_retry_count', '3', 'webhook'),
('webhook_timeout', '30', 'webhook'),
('backup_enabled', '1', 'backup'),
('backup_frequency', 'daily', 'backup'),
('backup_retention_days', '30', 'backup'),
('gdpr_enabled', '0', 'privacy'),
('privacy_policy_url', '', 'privacy'),
('terms_url', '', 'privacy'),
('fraud_detection', '1', 'security'),
('spam_keywords', 'spam,scam,fraud,lottery,winner', 'security'),
('daily_send_limit_default', '10000', 'limits'),
('monthly_send_limit_default', '100000', 'limits');

-- Default Languages
INSERT INTO `languages` (`code`, `name`, `native_name`, `direction`, `is_active`, `is_default`) VALUES
('en', 'English', 'English', 'ltr', 1, 1),
('ar', 'Arabic', 'العربية', 'rtl', 1, 0),
('es', 'Spanish', 'Español', 'ltr', 1, 0),
('fr', 'French', 'Français', 'ltr', 1, 0),
('de', 'German', 'Deutsch', 'ltr', 1, 0),
('ur', 'Urdu', 'اردو', 'rtl', 1, 0),
('hi', 'Hindi', 'हिन्दी', 'ltr', 1, 0),
('pt', 'Portuguese', 'Português', 'ltr', 1, 0),
('tr', 'Turkish', 'Türkçe', 'ltr', 1, 0),
('zh', 'Chinese', '中文', 'ltr', 1, 0);

-- Default Package
INSERT INTO `packages` (`name`, `slug`, `description`, `price`, `sms_quota`, `daily_limit`, `monthly_limit`, `validity_days`, `is_trial`, `is_active`, `sort_order`, `features`) VALUES
('Free Trial', 'free-trial', 'Try our platform with 100 free SMS', 0.00, 100, 50, 100, 7, 1, 1, 0, '["single_sms","bulk_sms","contact_management","basic_reports"]'),
('Starter', 'starter', 'Perfect for small businesses', 29.99, 5000, 500, 5000, 30, 0, 1, 1, '["single_sms","bulk_sms","group_sms","contact_management","campaigns","reports","api_access"]'),
('Professional', 'professional', 'For growing businesses', 79.99, 20000, 2000, 20000, 30, 0, 1, 2, '["single_sms","bulk_sms","group_sms","scheduled_sms","contact_management","campaigns","templates","reports","api_access","webhooks","priority_support"]'),
('Enterprise', 'enterprise', 'Full-featured enterprise solution', 199.99, 100000, 10000, 100000, 30, 0, 1, 3, '["single_sms","bulk_sms","group_sms","scheduled_sms","recurring_sms","contact_management","campaigns","templates","custom_sender_id","reports","api_access","webhooks","dedicated_support","white_label"]');

-- Preloaded Gateway Templates
INSERT INTO `gateways` (`name`, `slug`, `type`, `api_url`, `api_key`, `api_secret`, `recipient_param`, `message_param`, `sender_id_param`, `cost_per_sms`, `status`, `extra_params`) VALUES
('Twilio', 'twilio', 'json_api', 'https://api.twilio.com/2010-04-01/Accounts/{ACCOUNT_SID}/Messages.json', '', '', 'To', 'Body', 'From', 0.0075, 'inactive', '{"auth_type":"basic","account_sid":"","auth_token":""}'),
('Vonage (Nexmo)', 'vonage', 'json_api', 'https://rest.nexmo.com/sms/json', '', '', 'to', 'text', 'from', 0.0068, 'inactive', '{"api_key_param":"api_key","api_secret_param":"api_secret"}'),
('Plivo', 'plivo', 'json_api', 'https://api.plivo.com/v1/Account/{AUTH_ID}/Message/', '', '', 'dst', 'text', 'src', 0.0050, 'inactive', '{"auth_type":"basic"}'),
('MessageBird', 'messagebird', 'json_api', 'https://rest.messagebird.com/messages', '', '', 'recipients', 'body', 'originator', 0.0065, 'inactive', '{"auth_type":"bearer"}'),
('Clickatell', 'clickatell', 'json_api', 'https://platform.clickatell.com/messages/http/send', '', '', 'to', 'content', 'from', 0.0080, 'inactive', '{"auth_type":"header","header_name":"Authorization"}'),
('Infobip', 'infobip', 'json_api', 'https://api.infobip.com/sms/2/text/advanced', '', '', 'to', 'text', 'from', 0.0070, 'inactive', '{"auth_type":"bearer","base_url":""}'),
('Textbelt', 'textbelt', 'json_api', 'https://textbelt.com/text', '', '', 'phone', 'message', '', 0.0100, 'inactive', '{"key_param":"key"}'),
('Fast2SMS', 'fast2sms', 'json_api', 'https://www.fast2sms.com/dev/bulkV2', '', '', 'numbers', 'message', 'sender_id', 0.0020, 'inactive', '{"auth_type":"bearer","route":"v3"}'),
('SendPK', 'sendpk', 'http_get', 'https://sendpk.com/api/sms.php', '', '', 'to', 'msg', 'sender', 0.0015, 'inactive', '{}'),
('Jazz SMS', 'jazz-sms', 'json_api', 'https://connect.jazzcmt.com/sendsms_url.html', '', '', 'to', 'text', 'mask', 0.0012, 'inactive', '{}');

SET FOREIGN_KEY_CHECKS = 1;
