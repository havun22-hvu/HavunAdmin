# Technical Architecture - HavunAdmin

## Project Overview

**Project Name**: HavunAdmin
**Purpose**: Business administration and tax management system for Havun
**Owner**: Havun (Eenmanszaak)
**Target Users**: Business owner (primary), optional accountant access (future)
**Deployment**: admin.havun.nl on Hetzner server

## System Architecture

### Architecture Pattern
**Monolithic Laravel Application** with API integrations

```
┌─────────────────────────────────────────────────────────────┐
│                    HavunAdmin Application                    │
│                                                               │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐      │
│  │   Web UI     │  │   API Layer  │  │   Database   │      │
│  │  (Blade/Vue) │◄─┤   (Laravel)  │◄─┤    (MySQL)   │      │
│  └──────────────┘  └──────┬───────┘  └──────────────┘      │
│                            │                                  │
│                    ┌───────▼────────┐                        │
│                    │  Queue Worker  │                        │
│                    │   (Laravel)    │                        │
│                    └───────┬────────┘                        │
└────────────────────────────┼──────────────────────────────────┘
                             │
            ┌────────────────┼────────────────┐
            │                │                │
     ┌──────▼──────┐  ┌─────▼──────┐  ┌─────▼──────┐
     │  Gmail API  │  │ Mollie API │  │  Bunq API  │
     │  (Google)   │  │            │  │            │
     └─────────────┘  └────────────┘  └────────────┘
```

## Technology Stack

### Backend
- **Framework**: Laravel 11.x (latest stable)
- **PHP Version**: 8.2+
- **Database**: MySQL 8.0+
- **Queue System**: Database queue (can upgrade to Redis later)
- **Cache**: File cache (can upgrade to Redis later)
- **Session**: Database sessions

### Frontend
- **Template Engine**: Laravel Blade
- **JavaScript Framework**: Alpine.js (lightweight, no build step needed)
- **CSS Framework**: Tailwind CSS
- **Charts**: Chart.js for dashboard visualizations
- **Icons**: Heroicons or Font Awesome

### API Integrations
1. **Gmail API** (Google Cloud Platform)
   - Email reading
   - Attachment downloads
   - Label management

2. **Mollie API** (Payment processing)
   - Payment retrieval
   - Transaction history
   - Status checking

3. **Bunq API** (Banking)
   - Transaction retrieval
   - Account information
   - Payment details

4. **Herdenkingsportaal API** (Future - Optional)
   - Invoice synchronization
   - Customer data sharing

### Development Tools
- **Package Manager**: Composer (PHP), NPM (JavaScript)
- **Version Control**: Git
- **Code Style**: PSR-12 (PHP), Prettier (JavaScript)
- **Testing**: PHPUnit, Pest (optional)

## Server Infrastructure

### Hosting Provider
**Hetzner** (Same server as Herdenkingsportaal.nl)

### Server Specifications (Estimated)
- **OS**: Ubuntu 22.04 LTS
- **Web Server**: Nginx
- **PHP**: PHP-FPM 8.2+
- **Database**: MySQL 8.0+
- **SSL**: Let's Encrypt (via Certbot)
- **Estimated Cost**: ~€5/month (shared with Herdenkingsportaal)

### Domain Setup
- **Primary Domain**: havun.nl
- **Admin Subdomain**: admin.havun.nl
- **DNS Provider**: mijn.host

### Nginx Configuration
```nginx
# admin.havun.nl
server {
    listen 80;
    listen [::]:80;
    server_name admin.havun.nl;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name admin.havun.nl;

    root /var/www/havunadmin/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/admin.havun.nl/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/admin.havun.nl/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## Database Strategy

### Database Server
- Shared MySQL instance with Herdenkingsportaal
- Separate database: `havunadmin_production`
- Separate user with limited privileges

### Backup Strategy
1. **Daily Automated Backups**
   - MySQL dumps via cron job
   - Stored locally and off-site
   - 7-year retention (legal requirement)

2. **Manual Backup Feature**
   - "Export Yearly Backup" button in UI
   - ZIP with database dump + PDFs + Excel reports
   - Download to local storage

### Database Optimization
- Indexes on foreign keys
- Indexes on frequently queried columns (date, status, amount)
- Regular OPTIMIZE TABLE maintenance

## Security Architecture

### Authentication
- **Method**: Laravel Breeze (email/password)
- **Session Management**: Secure, HTTP-only cookies
- **Password Hashing**: Bcrypt (Laravel default)
- **2FA**: Optional (future enhancement)

### Authorization
- **Current**: Single admin user (business owner)
- **Future**: Role-based access control (Admin, Accountant, Read-only)

### API Security
1. **API Keys Storage**
   - Environment variables (.env file)
   - Never committed to Git
   - Encrypted at rest (Laravel encryption)

2. **API Rate Limiting**
   - Gmail API: Respect Google quotas
   - Mollie API: Standard rate limiting
   - Bunq API: Respect Bunq quotas

3. **CSRF Protection**
   - Laravel CSRF tokens on all forms
   - API endpoints use Sanctum tokens

4. **SQL Injection Prevention**
   - Eloquent ORM (parameterized queries)
   - Never raw SQL with user input

5. **XSS Prevention**
   - Blade template escaping by default
   - Content Security Policy headers

### Data Protection
- **Encryption**: Laravel encryption for sensitive data
- **HTTPS**: Enforced on all pages
- **File Uploads**: Validation and virus scanning (future)
- **Audit Log**: Track all data modifications

### Compliance
- **GDPR**: Privacy by design, data minimization
- **Dutch Tax Law**: 7-year data retention
- **PCI DSS**: N/A (no credit card storage, handled by Mollie)

## Performance Considerations

### Optimization Strategies
1. **Database Queries**
   - Eager loading relationships (avoid N+1)
   - Query result caching for reports
   - Pagination for large datasets

2. **Frontend Performance**
   - Minified CSS/JS assets
   - Image optimization
   - Lazy loading for charts

3. **API Calls**
   - Background queue jobs for sync operations
   - Rate limiting to avoid API throttling
   - Caching API responses where appropriate

4. **Caching Strategy**
   - Dashboard statistics (cache for 1 hour)
   - Project lists (cache until modified)
   - Configuration values (cache indefinitely)

### Performance Targets
- **Dashboard Load**: < 2 seconds
- **Invoice List (100 items)**: < 1 second
- **Report Export**: < 5 seconds
- **API Sync (Mollie)**: < 10 seconds

## Scalability Considerations

### Current Phase (MVP)
- Single server deployment
- Database queue for background jobs
- File-based caching
- Shared hosting with Herdenkingsportaal

### Future Growth (If Needed)
- Separate dedicated server
- Redis for caching and queues
- CDN for static assets
- Database read replicas
- Horizontal scaling with load balancer

## Development Environments

### Local Development
- **URL**: http://localhost:8000
- **Database**: SQLite or local MySQL
- **Queue**: Sync driver (no background processing)
- **Mail**: Log driver (emails to log file)
- **API Keys**: Test/sandbox credentials

### Staging Environment
- **URL**: https://staging.admin.havun.nl (optional)
- **Database**: Separate staging database
- **Queue**: Database queue
- **Mail**: Mailtrap or log
- **API Keys**: Test/sandbox credentials

### Production Environment
- **URL**: https://admin.havun.nl
- **Database**: Production MySQL database
- **Queue**: Database queue with worker
- **Mail**: SMTP (transactional email service)
- **API Keys**: Live production credentials

## Error Handling & Logging

### Error Tracking
- **Laravel Log Files**: storage/logs/laravel.log
- **Log Level**: Error, Warning, Info
- **Log Rotation**: Daily rotation, 14 days retention
- **Future**: Consider Sentry or similar error tracking service

### User-Facing Errors
- Friendly error messages
- No sensitive information exposed
- Helpful guidance for resolution

### API Error Handling
- Graceful degradation when APIs are down
- Retry logic for transient failures
- Clear error messages for sync failures

## Monitoring & Maintenance

### Health Checks
- Database connectivity
- API availability (Gmail, Mollie, Bunq)
- Disk space monitoring
- Queue worker status

### Maintenance Tasks
1. **Daily**
   - Database backup
   - API synchronization (cron)
   - Log rotation

2. **Weekly**
   - Review error logs
   - Check disk space
   - Verify backups

3. **Monthly**
   - Security updates (Laravel, PHP, OS)
   - Database optimization
   - Performance review

4. **Yearly**
   - Archive old data
   - Review and update dependencies
   - Security audit

## Deployment Strategy

### Deployment Process
1. Git push to repository
2. SSH to server
3. Pull latest code
4. Run migrations
5. Clear caches
6. Restart queue worker
7. Verify deployment

### Zero-Downtime Deployment (Future)
- Use Laravel Envoy or Deployer
- Automated deployment scripts
- Backup before deployment
- Rollback capability

## Dependencies & Package Management

### Core Laravel Packages (Included)
- Laravel Breeze (Authentication)
- Laravel Sanctum (API tokens)
- Laravel Queue (Background jobs)
- Laravel Excel (Export functionality)

### Third-Party Packages (Required)
```json
{
  "require": {
    "laravel/framework": "^11.0",
    "google/apiclient": "^2.0",
    "mollie/laravel-mollie": "^2.0",
    "bunq/sdk_php": "^1.0",
    "maatwebsite/excel": "^3.1",
    "barryvdh/laravel-dompdf": "^2.0"
  }
}
```

### JavaScript Dependencies
```json
{
  "devDependencies": {
    "alpinejs": "^3.13",
    "tailwindcss": "^3.4",
    "chart.js": "^4.4",
    "@tailwindcss/forms": "^0.5"
  }
}
```

## File Structure

```
havunadmin/
├── app/
│   ├── Console/
│   │   └── Commands/          # Artisan commands (sync, backup)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── DashboardController.php
│   │   │   ├── InvoiceController.php
│   │   │   ├── ExpenseController.php
│   │   │   ├── ProjectController.php
│   │   │   ├── ReportController.php
│   │   │   └── SyncController.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── Invoice.php
│   │   ├── Expense.php
│   │   ├── Project.php
│   │   ├── Transaction.php
│   │   └── User.php
│   ├── Services/              # Business logic
│   │   ├── GmailService.php
│   │   ├── MollieService.php
│   │   ├── BunqService.php
│   │   ├── ExportService.php
│   │   └── CategoryService.php
│   └── Jobs/                  # Queue jobs
│       ├── SyncMolliePayments.php
│       ├── SyncBunqTransactions.php
│       └── SyncGmailInvoices.php
├── database/
│   ├── migrations/
│   └── seeders/
├── resources/
│   ├── views/
│   │   ├── dashboard.blade.php
│   │   ├── invoices/
│   │   ├── expenses/
│   │   ├── projects/
│   │   └── reports/
│   ├── css/
│   └── js/
├── routes/
│   ├── web.php
│   └── api.php
├── storage/
│   ├── app/
│   │   └── invoices/          # PDF storage
│   └── logs/
├── tests/
├── .env.example
├── composer.json
└── package.json
```

## API Integration Architecture

### Gmail Integration Flow
```
User clicks "Scan Gmail"
    ↓
Job queued: SyncGmailInvoices
    ↓
Gmail API: Search emails with attachments
    ↓
Filter: PDFs with keywords "factuur", "invoice"
    ↓
Download PDFs to storage/app/invoices/
    ↓
Create Expense records (status: draft)
    ↓
Notify user: "X new invoices found"
    ↓
User reviews and approves drafts
```

### Mollie Integration Flow
```
Cron job runs daily (or user clicks "Sync Mollie")
    ↓
Job queued: SyncMolliePayments
    ↓
Mollie API: Get payments since last sync
    ↓
For each payment:
    ├── Try to match existing invoice (by reference)
    ├── If matched: Update status to "paid"
    └── If not matched: Create new invoice (status: paid)
    ↓
Log sync results
```

### Bunq Integration Flow
```
Cron job runs daily (or user clicks "Sync Bunq")
    ↓
Job queued: SyncBunqTransactions
    ↓
Bunq API: Get transactions since last sync
    ↓
For each transaction:
    ├── Incoming: Try to match invoice or create new
    └── Outgoing: Create expense with counterparty info
    ↓
Auto-categorize based on description/counterparty
    ↓
User reviews and adjusts categories
```

## Data Flow Architecture

```
External Sources          HavunAdmin           Output
─────────────────         ──────────           ──────

Gmail (PDFs)    ─────►   Expenses
Mollie          ─────►   Invoices   ─────►   Dashboard
Bunq            ─────►   Transactions         ↓
Manual Entry    ─────►                        Reports
                                               ↓
                                              Excel/PDF Export
                                               ↓
                                              Tax Filing
```

## Future Enhancements (Post-MVP)

### Phase 2 Features
- Advanced charts and visualizations
- Automatic categorization with ML
- OCR for invoice text extraction
- Email notifications for important events

### Phase 3 Features
- Multi-user support with roles
- Client/contact management
- Invoice generation and sending
- Recurring invoice automation

### Phase 4 Features
- Mobile app (Flutter or React Native)
- Advanced reporting with custom filters
- Integration with accounting software
- API for third-party integrations

## Documentation Standards

### Code Documentation
- PHPDoc blocks for all classes and methods
- Inline comments for complex logic
- README files in each major directory

### API Documentation
- OpenAPI/Swagger for API endpoints (if needed)
- Postman collections for testing

### User Documentation
- In-app help tooltips
- Video tutorials (future)
- FAQ section

## Conclusion

This architecture is designed to be:
- **Simple**: Easy to understand and maintain
- **Secure**: Following Laravel and industry best practices
- **Scalable**: Can grow with business needs
- **Cost-effective**: Minimal hosting costs, open-source stack
- **Maintainable**: Clean code, good documentation, standard patterns

The system will help Havun (business owner) manage finances efficiently, comply with Dutch tax requirements, and prepare for future growth.
