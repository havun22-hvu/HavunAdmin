# HavunAdmin - Server Deployment Guide

Complete handleiding voor het deployen van HavunAdmin naar een dedicated server.

## 📋 Overzicht

**Omgevingen:**
- **Staging**: https://staging-admin.havun.nl
- **Production**: https://admin.havun.nl

**Server Requirements:**
- Ubuntu 22.04+ / Debian 11+
- Apache2 (of Nginx)
- PHP 8.2+
- MySQL 8.0+
- Composer 2.x
- Node.js 20+
- Git

---

## 🚀 Eerste Keer Setup (One-time)

### 1. DNS Configuratie

Voeg A-records toe bij je DNS provider (waar je havun.nl hebt geregistreerd):

```
A    admin.havun.nl          -> [YOUR-SERVER-IP]
A    staging-admin.havun.nl  -> [YOUR-SERVER-IP]
```

**Wachttijd:** ~5-60 minuten voor DNS propagatie (kan tot 24 uur duren)

**Test DNS:**
```bash
nslookup staging-admin.havun.nl
# Moet jouw server IP tonen
```

---

### 2. Server Voorbereiding

SSH naar de server:

```bash
ssh root@[server-ip]
```

#### 2.1 Maak directories aan

```bash
# Staging
sudo mkdir -p /var/www/havunadmin-staging
sudo chown -R www-data:www-data /var/www/havunadmin-staging

# Production
sudo mkdir -p /var/www/havunadmin
sudo chown -R www-data:www-data /var/www/havunadmin
```

#### 2.2 MySQL databases aanmaken

```bash
sudo mysql
```

```sql
-- Staging database
CREATE DATABASE havunadmin_staging CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'havunadmin_staging'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON havunadmin_staging.* TO 'havunadmin_staging'@'localhost';

-- Production database
CREATE DATABASE havunadmin_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'havunadmin_prod'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON havunadmin_production.* TO 'havunadmin_prod'@'localhost';

FLUSH PRIVILEGES;
EXIT;
```

**Herdenkingsportaal Database Toegang (Remote):**

HavunAdmin heeft read-only toegang nodig tot de Herdenkingsportaal database voor het importeren van facturen.

**Optie A: Remote MySQL Connectie (Eenvoudigst)**

Op de **Herdenkingsportaal server** (188.245.159.115):

```bash
ssh root@188.245.159.115
mysql
```

```sql
-- Create remote readonly user
CREATE USER IF NOT EXISTS 'havunadmin_readonly'@'[YOUR-HAVUNADMIN-SERVER-IP]' IDENTIFIED BY 'STRONG_PASSWORD_HERE';
GRANT SELECT ON herdenkingsportaal_staging.* TO 'havunadmin_readonly'@'[YOUR-HAVUNADMIN-SERVER-IP]';
GRANT SELECT ON herdenkingsportaal_production.* TO 'havunadmin_readonly'@'[YOUR-HAVUNADMIN-SERVER-IP]';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Configure MySQL for remote access
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
# Zoek: bind-address = 127.0.0.1
# Wijzig naar: bind-address = 0.0.0.0

# Restart MySQL
sudo systemctl restart mysql

# Firewall regel toevoegen (alleen voor HavunAdmin server)
sudo ufw allow from [YOUR-HAVUNADMIN-SERVER-IP] to any port 3306
```

**Optie B: API Endpoint (Veiligst - Future)**

Later te implementeren: Bouw een API endpoint in Herdenkingsportaal voor het ophalen van facturen.

---

**⚠️ BELANGRIJK: Welke optie je ook kiest, noteer de credentials! Je hebt deze nodig voor `.env` configuratie.**

---

### 3. Git Repository Clonen

#### Staging:

```bash
cd /var/www/havunadmin-staging
sudo git clone https://github.com/havun22-hvu/HavunAdmin.git .
sudo chown -R www-data:www-data /var/www/havunadmin-staging
```

#### Production:

```bash
cd /var/www/havunadmin
sudo git clone https://github.com/havun22-hvu/HavunAdmin.git .
sudo chown -R www-data:www-data /var/www/havunadmin
```

---

### 4. Applicatie Setup

Voor **beide** omgevingen:

```bash
cd /var/www/havunadmin-staging  # of /var/www/havunadmin voor production

# Dependencies installeren
sudo -u www-data composer install --no-dev --optimize-autoloader
sudo -u www-data npm ci
sudo -u www-data npm run build

# .env file aanmaken
sudo -u www-data cp .env.staging.example .env  # of .env.production.example
sudo -u www-data nano .env

# APP_KEY genereren
sudo -u www-data php artisan key:generate

# Database migreren en seeden
sudo -u www-data php artisan migrate --seed --force

# Storage link
sudo -u www-data php artisan storage:link

# Permissies
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

### 5. Apache VirtualHost Configuratie

#### Staging:

```bash
# Kopieer config
sudo cp deployment/apache/staging-admin.havun.nl.conf /etc/apache2/sites-available/

# Enable site
sudo a2ensite staging-admin.havun.nl

# Test config
sudo apache2ctl configtest

# Reload Apache
sudo systemctl reload apache2
```

#### Production:

```bash
# Kopieer config
sudo cp deployment/apache/admin.havun.nl.conf /etc/apache2/sites-available/

# Enable site
sudo a2ensite admin.havun.nl

# Test config
sudo apache2ctl configtest

# Reload Apache
sudo systemctl reload apache2
```

---

### 6. SSL Certificaten (Let's Encrypt)

**Belangrijk:** Wacht tot DNS is gepropageerd voordat je SSL installeert!

```bash
# Test DNS eerst
ping staging-admin.havun.nl
ping admin.havun.nl

# Installeer SSL voor staging
sudo certbot --apache -d staging-admin.havun.nl

# Installeer SSL voor production
sudo certbot --apache -d admin.havun.nl
```

Certbot configureert automatisch:
- HTTPS redirect
- SSL certificaten
- Auto-renewal

---

### 7. Mollie Package Installeren (Production Only)

Op de server werkt SSL wel, dus we kunnen het echte Mollie package installeren:

```bash
cd /var/www/havunadmin  # ALLEEN PRODUCTION

# Verwijder stub autoload
sudo -u www-data nano composer.json
# Wijzig: "Mollie\\": "app/Stubs/Mollie/"
# Naar:   "Mollie\\": "vendor/mollie/mollie-api-php/src/"

# Installeer echte package
sudo -u www-data composer require mollie/mollie-api-php
sudo -u www-data composer dump-autoload

# Rebuild assets
sudo -u www-data npm run build

# Cache clearen
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan cache:clear
```

---

### 8. Bunq API Configuratie

**BELANGRIJK:** Bunq API key moet binnen 3 uur gekoppeld worden!

**Timing:**
1. Wacht tot applicatie 100% klaar is
2. Genereer API key in Bunq app
3. Voeg METEEN toe aan `.env`
4. Test binnen 3 uur

```bash
sudo -u www-data nano /var/www/havunadmin/.env

# Voeg toe:
BUNQ_API_KEY=sandbox_... # of production key
BUNQ_ACCOUNT_ID=12345
BUNQ_ENVIRONMENT=production # of sandbox

# Test connectie
sudo -u www-data php artisan tinker
>>> app(App\Services\BunqService::class)->testConnection();
```

---

## 🔄 Updates Deployen

Na de eerste setup kun je het deployment script gebruiken:

### Staging:

```bash
cd /var/www/havunadmin-staging
sudo chmod +x deployment/scripts/deploy.sh
sudo -u www-data ./deployment/scripts/deploy.sh staging
```

### Production:

```bash
cd /var/www/havunadmin
sudo chmod +x deployment/scripts/deploy.sh
sudo -u www-data ./deployment/scripts/deploy.sh production
```

**Het script doet:**
1. Maintenance mode aan
2. Git pull
3. Composer install
4. NPM build
5. Database migrations
6. Cache clearen
7. Permissions fixen
8. Maintenance mode uit

---

## 🔐 Security Checklist

- [ ] `.env` heeft sterke wachtwoorden
- [ ] `APP_DEBUG=false` in production
- [ ] SSL certificaten geïnstalleerd
- [ ] Database users hebben minimal permissions
- [ ] Firewall configured (ufw)
- [ ] Fail2ban actief
- [ ] Backup strategie geconfigureerd

---

## 📊 Monitoring & Logs

### Laravel Logs:

```bash
# Staging
tail -f /var/www/havunadmin-staging/storage/logs/laravel.log

# Production
tail -f /var/www/havunadmin/storage/logs/laravel.log
```

### Apache Logs:

```bash
# Staging
tail -f /var/log/apache2/havunadmin-staging-error.log
tail -f /var/log/apache2/havunadmin-staging-access.log

# Production
tail -f /var/log/apache2/havunadmin-error.log
tail -f /var/log/apache2/havunadmin-access.log
```

---

## 🆘 Troubleshooting

### Website toont 500 error:

```bash
# Check Laravel logs
tail -n 50 /var/www/havunadmin/storage/logs/laravel.log

# Check Apache error log
sudo tail -n 50 /var/log/apache2/havunadmin-error.log

# Check permissions
sudo chown -R www-data:www-data /var/www/havunadmin/storage
sudo chmod -R 775 /var/www/havunadmin/storage
```

### Database connection failed:

```bash
# Test MySQL connection
sudo mysql -u havunadmin_prod -p havunadmin_production

# Check .env database credentials
sudo -u www-data nano /var/www/havunadmin/.env
```

### SSL certificaat problemen:

```bash
# Test SSL
sudo certbot certificates

# Renew manually
sudo certbot renew

# Check auto-renewal
sudo systemctl status certbot.timer
```

---

## 🔄 Cron Jobs (Automatische Sync)

Voeg toe aan crontab:

```bash
sudo crontab -e -u www-data
```

```cron
# HavunAdmin - Production
# Dagelijkse sync om 2:00 AM
0 2 * * * cd /var/www/havunadmin && php artisan sync:herdenkingsportaal >> /var/www/havunadmin/storage/logs/cron.log 2>&1
0 2 * * * cd /var/www/havunadmin && php artisan sync:mollie >> /var/www/havunadmin/storage/logs/cron.log 2>&1

# Laravel scheduler (runs every minute)
* * * * * cd /var/www/havunadmin && php artisan schedule:run >> /dev/null 2>&1
```

---

## 📱 Post-Deployment Checklist

Na deployment, test:

- [ ] Website laadt (https://admin.havun.nl)
- [ ] Login werkt
- [ ] Dashboard toont data
- [ ] Grafieken renderen
- [ ] Herdenkingsportaal sync werkt
- [ ] Mollie sync werkt (production only)
- [ ] Reports kunnen gegenereerd worden
- [ ] SSL certificaat is geldig
- [ ] Logs tonen geen errors

---

## 🔗 Links

- **Staging**: https://staging-admin.havun.nl
- **Production**: https://admin.havun.nl
- **GitHub**: https://github.com/havun22-hvu/HavunAdmin
- **Server**: SSH root@[server-ip]

---

**Laatste update:** 27 oktober 2025
