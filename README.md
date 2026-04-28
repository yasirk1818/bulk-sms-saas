# BulkSMS Pro - Enterprise Bulk SMS Sender Platform

A complete, enterprise-grade Bulk SMS SaaS platform built with Core PHP 8+. Production-ready, scalable, secure, and optimized for high-volume SMS delivery. Works perfectly on shared hosting (cPanel).

## Features

### Core
- **SMS Sending**: Single, bulk, group, scheduled, campaign, personalized SMS
- **Smart Routing**: Cheapest route, country-based routing, auto failover
- **Queue Engine**: Priority queues, retry logic, concurrent worker support
- **Campaign System**: Draft, scheduled, analytics, delivery reports
- **Contact Management**: Contact books, groups, import/export, duplicate detection
- **Template System**: Templates with admin approval workflow
- **DLR Reports**: Real-time delivery tracking with webhook callbacks

### Admin Panel
- **Dashboard**: Analytics, revenue, gateway performance, queue monitoring
- **User Management**: CRUD, role-based access, credit management
- **Gateway Management**: Add/edit/delete, health monitoring, load balancing
- **Package System**: Monthly plans, SMS quotas, trial packages
- **Billing**: Manual payments, transaction logs, wallet system
- **Reports**: SMS analytics, revenue, user reports, CSV export
- **Settings**: Full system configuration, maintenance mode
- **GDPR**: Data export/deletion, consent logs, privacy compliance
- **Backup Manager**: Create, download, delete database backups

### User Panel
- **Send SMS**: Single and bulk SMS with templates and scheduling
- **Campaigns**: Create, manage, track campaign performance
- **Contacts**: Manage contacts and groups, CSV import/export
- **Templates**: Create and manage SMS templates
- **Sender IDs**: Request custom sender IDs
- **API Keys**: Generate and manage API access keys
- **Webhooks**: Configure delivery event callbacks
- **Reports**: SMS delivery analytics with date range filtering
- **Support Tickets**: Submit and track support requests

### Security
- CSRF protection on all forms
- XSS filtering
- Prepared statements (SQL injection prevention)
- Secure session handling with IP binding
- Rate limiting per user and API key
- IP whitelist for API access
- Audit logs and system logs
- Account lockout after failed login attempts

### Design
- Premium Apple/iOS-inspired UI with glassmorphism
- Dark/Light theme with smooth switching
- Responsive design (desktop, tablet, mobile)
- Bootstrap 5 with custom premium styling
- Chart.js analytics widgets
- PWA support (installable web app)

## Tech Stack

- **Backend**: Core PHP 8+ (custom MVC framework)
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Frontend**: Bootstrap 5, jQuery, Chart.js
- **Architecture**: MVC-like modular structure
- **Background Jobs**: Cron-based queue processing

## Quick Start

1. Upload files to your server
2. Navigate to `/install/` to run the auto-installer
3. Configure gateways in admin panel
4. Set up cron jobs (see [Setup Guide](docs/SETUP.md))

## Documentation

- [Setup Guide](docs/SETUP.md) - Installation and configuration
- [API Documentation](docs/API.md) - REST API endpoints and usage

## License

Proprietary software. All rights reserved.
