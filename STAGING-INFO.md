# HavunAdmin Staging Environment

**Status**: ✅ LIVE
**Datum**: 27 Oktober 2025

---

## Server Informatie

**Hetzner Server:**
- Type: CPX22 (2 vCPU, 4 GB RAM, 80 GB SSD)
- IP: `46.224.31.30`
- Hostname: `havun-server`
- Location: Falkenstein, Germany
- OS: Ubuntu 22.04 LTS

**Kosten:**
- Server: €7,25/maand
- Backups: €1,45/maand
- **Totaal**: €8,70/maand

---

## Toegang

**SSH:**
```bash
ssh root@46.224.31.30
```

**URL:**
- https://staging.admin.havun.nl

**Database:**
- Host: 127.0.0.1 (localhost)
- Database: `havunadmin_staging`
- User: `root`
- Password: `7Ut0xaLzh7s^T2!DmQKR`

---

## Stack

- **Webserver**: Apache 2.4
- **PHP**: 8.2
- **Database**: MySQL 8.0
- **Framework**: Laravel 11
- **SSL**: Let's Encrypt (auto-renew)

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
- Key: `live_aKqTeMsaKJWzzwq9C4aRgBqFcVZLf8` (staging key)

**Bunq API:**
- Status: Nog te configureren

---

## DNS

**mijn.host instellingen:**
```
A    staging.admin.havun.nl    46.224.31.30
A    admin.havun.nl            46.224.31.30
A    *.havun.nl                46.224.31.30
```

---

## Volgende Stappen

- [ ] Test Gmail OAuth flow
- [ ] Test duplicate matching functionaliteit
- [ ] Test reconciliation dashboard
- [ ] Deploy naar production (admin.havun.nl)

---

**Laatst bijgewerkt**: 27 Oktober 2025
