# HavunAdmin Staging Environment

**Status**: ✅ LIVE & OPERATIONEEL
**Deployment Datum**: 28 Oktober 2025
**Laatste Update**: 28 Oktober 2025

---

## Server Informatie

**Dedicated Havun Server (Hetzner):**
- Type: CPX22 (2 vCPU, 4 GB RAM, 80 GB SSD)
- IP: `46.224.31.30`
- Hostname: `havun-server`
- Location: Falkenstein, Germany
- OS: Ubuntu 22.04 LTS

**Kosten:**
- Server: €7,25/maand
- Backups: €1,45/maand
- **Totaal**: €8,70/maand

**Waarom dedicated server:**
- Gescheiden van Herdenkingsportaal productie
- Voor alle Havun business tools
- Schaalbaarheid voor toekomstige apps

---

## Toegang

**SSH:**
```bash
ssh root@46.224.31.30
```

**Applicatie URL:**
- https://staging.admin.havun.nl

**Admin Login:**
- Email: havun22@gmail.com
- Password: 9TD@GYB6!J@rvMkC*tmZ

**Database:**
- Host: 127.0.0.1 (localhost)
- Database: `havunadmin_staging`
- User: `root`
- Password: `7Ut0xaLzh7s^T2!DmQKR`
- Port: 3306

---

## Stack

- **Webserver**: Apache 2.4.58
- **PHP**: 8.2 (PHP-FPM)
- **Database**: MySQL 8.0
- **Framework**: Laravel 12.x
- **SSL**: Let's Encrypt (geldig tot: 2026-01-25, auto-renew enabled)

---

## Deployment

**Directory**: `/var/www/staging`

**Update code:**
```bash
ssh root@46.224.31.30
cd /var/www/staging
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan view:clear
chown -R www-data:www-data storage bootstrap/cache
systemctl restart php8.2-fpm
```

---

## Services

**Apache:**
```bash
systemctl status apache2
systemctl restart apache2
```

**MySQL:**
```bash
systemctl status mysql
```

**PHP-FPM:**
```bash
systemctl status php8.2-fpm
systemctl restart php8.2-fpm
```

---

## Logs

**Laravel:**
```bash
tail -f /var/www/staging/storage/logs/laravel.log
```

**Apache Error:**
```bash
tail -f /var/log/apache2/staging-error.log
```

**Apache Access:**
```bash
tail -f /var/log/apache2/staging-access.log
```

---

## API Configuratie

**Gmail API:**
- Client ID: Geconfigureerd ✅
- Redirect URI: `https://staging.admin.havun.nl/gmail/callback`

**Mollie API:**
- Key: `live_aKqTeJbFeuzARSeapNE3A2Tc8B2V3S` (staging key)
- Status: ✅ Geconfigureerd in .env

**Bunq API:**
- Status: ⏳ Nog te configureren (wacht op deployment completion)

**Herdenkingsportaal Database (Remote):**
- Host: 188.245.159.115 (Herdenkingsportaal server)
- Status: ⚠️ Nog te configureren (remote read-only access)
- Vereist: Firewall regel + remote MySQL user

---

## DNS

**mijn.host instellingen:**
```
A    staging.admin.havun.nl    46.224.31.30
A    admin.havun.nl            46.224.31.30
```

**Status**: ✅ DNS geconfigureerd en propagated

---

## Deployment Status

**Voltooid:**
- ✅ Server opgezet en geconfigureerd
- ✅ LAMP stack geïnstalleerd (Apache, MySQL, PHP 8.2)
- ✅ Laravel applicatie deployed
- ✅ Database migrations (14 tabellen)
- ✅ SSL certificaat geïnstalleerd
- ✅ Admin user aangemaakt
- ✅ PHP-FPM configuratie
- ✅ Apache VirtualHost setup
- ✅ Permissions correct ingesteld
- ✅ Dashboard SQL fix (SQLite → MySQL conversie)
- ✅ Composer dependencies installed
- ✅ NPM assets gebuild

**🚨 URGENT - Security Hardening (Morgen!):**
- [ ] **UFW Firewall** - Alleen SSH/HTTP/HTTPS toestaan (10 min)
- [ ] **Fail2ban** - Brute force protection (10 min)
- [ ] **.env Security** - APP_DEBUG=false, MOLLIE_KEY, permissions 600 (15 min)
- [ ] **Apache Security** - Hide version, security headers (10 min)
- [ ] **MySQL Dedicated User** - App niet als root (15 min)
- [ ] **Security Verificatie** - Alle checks groen (5 min)

**Nog Te Doen (Deze Week):**
- [ ] Mollie sync testen (nu MOLLIE_KEY toevoegen)
- [ ] Dashboard volledig testen (alle 6 charts)
- [ ] CRUD testen (invoices, expenses)
- [ ] Reports testen (CSV exports)
- [ ] Backup script installeren
- [ ] Herdenkingsportaal remote database access configureren
- [ ] Gmail OAuth flow testen
- [ ] Duplicate matching functionaliteit testen
- [ ] Reconciliation dashboard testen
- [ ] Bunq API configureren

**Later (Volgende Week):**
- [ ] Deploy naar production (admin.havun.nl)

---

## Belangrijke Fixes

**SQL Syntax Fix (28 Okt 2025):**
- **Probleem**: Dashboard gebruikte SQLite `strftime()` functie op MySQL database
- **Fout**: `SQLSTATE[42000]: Syntax error... near 'INTEGER)'`
- **Oplossing**: Alle `CAST(strftime('%m', invoice_date) AS INTEGER)` vervangen door `MONTH(invoice_date)`
- **Files gewijzigd**: `app/Http/Controllers/DashboardController.php`
- **Methodes**: `getMonthlyRevenue()`, `getMonthlyIncomeVsExpenses()`, `getMonthlyProfit()`
- **Status**: ✅ Gefixt en gedeployed

---

## Volgende Stappen

**Korte termijn (deze week):**
1. Test alle dashboard charts en grafieken
2. Test invoice/expense CRUD functionaliteit
3. Configureer Herdenkingsportaal remote database access
4. Test Gmail OAuth flow en factuur import

**Middellange termijn (volgende week):**
1. Configureer Bunq API (LET OP: 3-uurs regel!)
2. Test complete sync flow (Herdenkingsportaal → Gmail → Mollie → Bunq)
3. Test duplicate matching en reconciliation
4. Production deployment voorbereiden

**Lange termijn:**
1. Deploy naar production (admin.havun.nl)
2. Monitoring en logging setup
3. Backup strategie implementeren
4. Cron jobs voor automatische sync

---

**Laatst bijgewerkt**: 28 Oktober 2025
**Deploy Geschiedenis**: Zie DEPLOYMENT.md voor volledige deployment guide
