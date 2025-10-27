# HavunAdmin - Bedrijfsadministratie Systeem

> **Status**: ✅ STAGING LIVE | **Laatst bijgewerkt**: 28 Oktober 2025

**Live URL**: https://staging.admin.havun.nl

Professioneel bedrijfsadministratie systeem voor Havun, gebouwd met Laravel 12. Automatiseert belastingaangifte, factuur import, en financiële rapportage.

---

## 🎯 Doel

Een all-in-one administratie platform dat:
- ✅ Facturen importeert uit meerdere bronnen (Herdenkingsportaal, Gmail, Mollie, Bunq)
- ✅ Duplicate detection met memorial reference matching
- ✅ Kwartaal/jaar rapportages genereert voor de Belastingdienst
- ✅ Projecten en categorieën beheert
- ✅ Dashboard met financiële statistieken en grafieken
- ✅ 7 jaar bewaarplicht ondersteunt

---

## 🚀 Deployment Status

### Staging Environment ✅ LIVE
- **URL**: https://staging.admin.havun.nl
- **Server**: Hetzner CPX22 (46.224.31.30)
- **Stack**: Laravel 12 + Apache + PHP 8.2-FPM + MySQL 8.0
- **SSL**: Let's Encrypt (geldig tot 2026-01-25)
- **Database**: 14 tabellen, all migrations completed
- **Status**: Fully operational, ready for testing

### Production Environment ⏳ Nog Te Deployen
- **URL**: admin.havun.nl (gepland)
- **Status**: Wacht op staging testing completion

---

## 📁 Project Structuur

```
HavunAdmin/
├── app/
│   ├── Http/Controllers/
│   │   ├── DashboardController.php      # Dashboard met 6 charts
│   │   ├── InvoiceController.php        # Factuur management
│   │   ├── ProjectController.php        # Project management
│   │   ├── CategoryController.php       # Categorie management
│   │   ├── ReportController.php         # Tax exports (CSV)
│   │   ├── SyncController.php           # Gmail/Mollie sync
│   │   └── ReconciliationController.php # Duplicate matching UI
│   ├── Models/
│   │   ├── Invoice.php                  # Core model met scopes
│   │   ├── Project.php
│   │   ├── Customer.php
│   │   ├── Supplier.php
│   │   └── Category.php
│   └── Services/
│       ├── GmailService.php             # Gmail API integration
│       ├── MollieService.php            # Mollie API integration
│       ├── TransactionMatchingService.php # Duplicate detection
│       └── TaxExportService.php         # Belastingdienst exports
├── database/
│   ├── migrations/                      # 14 migrations
│   └── seeders/                         # User, Projects, Categories
├── resources/views/
│   ├── dashboard.blade.php              # Main dashboard (Chart.js)
│   ├── invoices/                        # Invoice CRUD
│   ├── reports/                         # Tax export forms
│   └── reconciliation/                  # Duplicate matching UI
└── docs/
    ├── PROJECT-STATUS.md                # Complete project status
    ├── STAGING-INFO.md                  # Staging server info + credentials
    ├── DEPLOYMENT.md                    # Deployment guide
    ├── BUSINESS-INFO.md                 # KvK, BTW, API keys
    └── DATABASE-DESIGN.md               # Complete schema documentation
```

---

## 🛠️ Tech Stack

| Component | Technology | Version |
|-----------|-----------|---------|
| **Framework** | Laravel | 12.x |
| **Language** | PHP | 8.2 |
| **Database** | MySQL | 8.0 |
| **Webserver** | Apache | 2.4.58 |
| **Process Manager** | PHP-FPM | 8.2 |
| **Frontend** | Blade + Tailwind CSS + Alpine.js | - |
| **Charts** | Chart.js | 4.x |
| **Build Tool** | Vite | 7.x |
| **Package Manager** | Composer + NPM | 2.x / 10.x |

---

## 📊 Features

### ✅ Implemented

#### Core Functionaliteit
- ✅ **Dashboard** - 6 interactive charts (revenue, expenses, profit, YoY)
- ✅ **Invoice Management** - CRUD voor inkomsten en uitgaven
- ✅ **Project Management** - Koppel facturen aan projecten
- ✅ **Category Management** - Categoriseer uitgaven
- ✅ **Customer/Supplier Management** - Contact beheer

#### Sync & Import
- ✅ **Gmail API Integration** - OAuth2 authenticated
- ✅ **Mollie API Integration** - Payment sync
- ✅ **Transaction Matching** - Duplicate detection algoritme
- ✅ **Memorial Reference Tracking** - Link naar Herdenkingsportaal monuments
- ✅ **Reconciliation Dashboard** - UI voor duplicate review

#### Belastingdienst Export
- ✅ **Kwartaaloverzicht** - CSV export per kwartaal
- ✅ **Jaaroverzicht** - CSV export per jaar
- ✅ **BTW Aangifte** - CSV voor BTW aangifte (wanneer nodig)

### 🔜 Nog Te Implementeren

- [ ] **Bunq API Integration** - Bank transaction sync (wacht op deployment)
- [ ] **Herdenkingsportaal Database Sync** - Remote readonly access (nog te configureren)
- [ ] **PDF Export** - Facturen als PDF
- [ ] **Automatische Categorisering** - AI-powered expense categorization
- [ ] **Cron Jobs** - Automatische daily sync
- [ ] **Email Notifications** - Alerts voor belangrijke events

---

## 🔑 Access & Credentials

**Staging Environment:**
- **URL**: https://staging.admin.havun.nl
- **Admin Email**: havun22@gmail.com
- **Admin Password**: 9TD@GYB6!J@rvMkC*tmZ

**SSH Access:**
```bash
ssh root@46.224.31.30
cd /var/www/staging
```

**Database:**
- Host: localhost
- Database: havunadmin_staging
- User: root
- Password: 7Ut0xaLzh7s^T2!DmQKR

⚠️ **BELANGRIJK**: Credentials zijn gedocumenteerd in [STAGING-INFO.md](STAGING-INFO.md) en [BUSINESS-INFO.md](BUSINESS-INFO.md)

---

## 🚀 Quick Start (Local Development)

### 1. Clone Repository

```bash
git clone https://github.com/havun22-hvu/HavunAdmin.git
cd HavunAdmin
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` met je database credentials en API keys (zie BUSINESS-INFO.md).

### 4. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

### 5. Build Assets

```bash
npm run dev
```

### 6. Start Development Server

```bash
php artisan serve
```

Open http://localhost:8000 in je browser.

---

## 📚 Documentatie

| Bestand | Beschrijving |
|---------|--------------|
| [PROJECT-STATUS.md](PROJECT-STATUS.md) | Complete project status, deployment history, lessons learned |
| [STAGING-INFO.md](STAGING-INFO.md) | Staging server info, credentials, deployment checklist |
| [DEPLOYMENT.md](DEPLOYMENT.md) | Complete deployment guide voor staging + production |
| [BUSINESS-INFO.md](BUSINESS-INFO.md) | KvK, BTW-id, API credentials, kosten overzicht |
| [DATABASE-DESIGN.md](DATABASE-DESIGN.md) | Complete database schema (11 tabellen) |
| [FUNCTIONAL-REQUIREMENTS.md](FUNCTIONAL-REQUIREMENTS.md) | Feature lijst en requirements |
| [TECHNICAL-ARCHITECTURE.md](TECHNICAL-ARCHITECTURE.md) | Technische architectuur en design decisions |
| [API-SETUP-GUIDE.md](API-SETUP-GUIDE.md) | Stapsgewijze API setup (Gmail, Mollie, Bunq) |
| [TAX-REQUIREMENTS.md](TAX-REQUIREMENTS.md) | Nederlandse belasting eisen en verplichtingen |

---

## 🔥 Recent Updates

### 28 Oktober 2025 - Staging Deployment COMPLEET ✅

**Deployment Highlights:**
- ✅ Dedicated Hetzner server opgezet (46.224.31.30)
- ✅ LAMP stack geconfigureerd (Apache + PHP-FPM + MySQL)
- ✅ SSL certificaat geïnstalleerd (Let's Encrypt)
- ✅ 14 database migrations uitgevoerd
- ✅ Admin user aangemaakt
- ✅ Dashboard SQL fix (SQLite → MySQL conversie)
- ✅ Alle dependencies geïnstalleerd en assets gebuild

**Critical Fix:**
- **SQL Syntax Error**: Dashboard gebruikte SQLite `strftime()` functie op MySQL database
- **Oplossing**: Alle date queries geconverteerd naar MySQL `MONTH()` functie
- **Impact**: Dashboard crashes opgelost, alle 6 charts werken nu

**Lessons Learned:**
1. Altijd server keuze afstemmen met gebruiker
2. SQLite development ≠ MySQL production (date functions verschillen!)
3. Test dashboard na deployment op productie database
4. PHP-FPM restart vereist na code changes

Zie [PROJECT-STATUS.md](PROJECT-STATUS.md) voor complete deployment history.

---

## 🔧 Deployment

### Staging Deployment (Manual)

```bash
# SSH naar server
ssh root@46.224.31.30

# Navigeer naar project
cd /var/www/staging

# Pull laatste changes
git pull origin main

# Update dependencies (indien nodig)
composer install --no-dev --optimize-autoloader

# Run migrations (indien nieuwe migrations)
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Fix permissions
chown -R www-data:www-data storage bootstrap/cache

# Restart PHP-FPM
systemctl restart php8.2-fpm
```

⚠️ **BELANGRIJK**: Gebruik ALTIJD `git pull` voor deployment, NOOIT `scp` of `rsync`! Zie [DEPLOYMENT-PROTOCOL.md](DEPLOYMENT-PROTOCOL.md) voor details.

---

## 🛡️ Security

- ✅ SSL/TLS encryption (Let's Encrypt)
- ✅ Environment variables voor sensitive data (.env NEVER in git)
- ✅ OAuth2 voor API authenticatie (Gmail)
- ✅ Laravel Breeze authenticatie
- ✅ CSRF protection
- ✅ SQL injection prevention (Eloquent ORM)
- ✅ XSS protection (Blade escaping)

---

## 📊 Database Schema

11 tabellen, volledige documentatie in [DATABASE-DESIGN.md](DATABASE-DESIGN.md):

| Tabel | Doel | Records (staging) |
|-------|------|-------------------|
| users | Admin users | 1 |
| projects | Projecten (Herdenkingsportaal, IDSee, etc) | 4 |
| categories | Uitgaven categorieën | 6 |
| customers | Klanten | 0 |
| suppliers | Leveranciers | 0 |
| invoices | Facturen (income + expense) | 0 |
| invoice_items | Factuurregels | 0 |
| transactions | Bank transacties | 0 |
| api_syncs | Sync logs | 0 |
| oauth_tokens | OAuth tokens (Gmail) | 0 |
| settings | App settings | 0 |

---

## 🔗 API Integraties

| API | Status | Gebruik |
|-----|--------|---------|
| **Gmail API** | ✅ Configured | Factuur PDF's importeren van leveranciers |
| **Mollie API** | ✅ Configured | Klant betalingen synchroniseren |
| **Bunq API** | ⏳ Pending | Bank transacties synchroniseren |
| **Herdenkingsportaal DB** | ⏳ Pending | Factuur kopieën remote ophalen |

---

## 💰 Kosten

**Maandelijks:**
- Hetzner HavunAdmin server: €8,70/maand
- Bunq abonnement: €13,99/maand
- Claude AI development: €108,90/maand
- **Totaal**: ~€137,60/maand

**Jaarlijks:**
- Domain registraties: €12,08/jaar
- **Totaal**: ~€1.651,20/jaar

Zie [BUSINESS-INFO.md](BUSINESS-INFO.md) voor complete kosten overzicht.

---

## 🤝 Contributing

Dit is een private project voor Havun business administratie.

---

## 📞 Support

- **Email**: havun22@gmail.com
- **GitHub Issues**: https://github.com/havun22-hvu/HavunAdmin/issues
- **Documentatie**: Zie MD files in root directory

---

## 📝 License

Private - Havun © 2025

---

**Built with ❤️ using Laravel 12 and Claude Code**
