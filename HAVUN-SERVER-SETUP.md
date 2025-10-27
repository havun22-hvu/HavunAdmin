# Havun Server Setup - Complete Installatie Guide

> **🎯 Doel:** Aparte Hetzner server voor HavunAdmin + toekomstige apps
> **📅 Gemaakt:** 26 Oktober 2025
> **💰 Kosten:** Hetzner CX11 - €4.51/maand

---

## 📋 Inhoudsopgave

1. [Server Architectuur](#server-architectuur)
2. [Hetzner Server Aanmaken](#hetzner-server-aanmaken)
3. [Basis Server Setup](#basis-server-setup)
4. [SSH Key Configuratie](#ssh-key-configuratie)
5. [LAMP Stack Installatie](#lamp-stack-installatie)
6. [HavunAdmin Deployment](#havunadmin-deployment)
7. [Remote Database Connectie](#remote-database-connectie)
8. [DNS & SSL Setup](#dns--ssl-setup)
9. [Security Hardening](#security-hardening)
10. [Toekomstige Apps](#toekomstige-apps)

---

## Server Architectuur

### **Waarom Aparte Server?**

**Herdenkingsportaal Server (188.245.159.115):**
- ✅ Dedicated voor klant-facing website
- ✅ Performance gegarandeerd
- ✅ Geen risico van andere apps
- ✅ Clean en gefocust

**Havun Server (nieuwe server):**
- 🔵 Al jouw eigen tools en apps
- 🔵 Experimenteren zonder productie risico
- 🔵 Schaalbaar voor meerdere projecten
- 🔵 Financiële data gescheiden

### **Server Specificaties**

```
Hetzner CX11
├─ CPU: 1 vCPU (AMD/Intel)
├─ RAM: 2 GB
├─ Storage: 20 GB SSD
├─ Traffic: 20 TB/maand
└─ Kosten: €4.51/maand

Geschikt voor:
✅ 5-10 kleine Laravel apps
✅ MySQL database
✅ Apache webserver
✅ SSL certificaten
```

---

## Hetzner Server Aanmaken

### Stap 1: Inloggen op Hetzner Cloud Console

1. Ga naar: https://console.hetzner.cloud/
2. Login met je account
3. Selecteer je project (of maak nieuwe: "Havun Apps")

### Stap 2: Nieuwe Server Aanmaken

```
Klik: "Add Server"

Location:
└─ Falkenstein, Germany (of Nuremberg) - dichtstbij Nederland

Image:
└─ Ubuntu 22.04 LTS

Type:
└─ Shared vCPU
    └─ CX11 (€4.51/maand)

Volume:
└─ None (20 GB SSD is genoeg)

Networking:
└─ Public IPv4 & IPv6

SSH Keys:
└─ Add je public key (zie volgende sectie)

Name:
└─ havun-server

Firewall:
└─ Create new firewall tijdens setup
```

### Stap 3: Server Starten

- Klik "Create & Buy Now"
- Wacht 30-60 seconden tot server online is
- Noteer het IP adres (bijv. 192.168.1.100)

---

## Basis Server Setup

### Eerste Login

```bash
# SSH naar nieuwe server (gebruik Hetzner console of terminal)
ssh root@[server-ip]

# Update systeem
apt update && apt upgrade -y

# Reboot indien kernel update
reboot

# Login opnieuw na reboot
ssh root@[server-ip]
```

### Hostname Instellen

```bash
# Zet hostname
hostnamectl set-hostname havun-server

# Edit hosts file
nano /etc/hosts

# Voeg toe:
127.0.0.1 localhost
127.0.1.1 havun-server
[server-ip] havun-server

# Verifieer
hostname
hostname -f
```

### Timezone Instellen

```bash
# Zet timezone naar Amsterdam
timedatectl set-timezone Europe/Amsterdam

# Verifieer
timedatectl
```

---

## SSH Key Configuratie

### Optie A: Zelfde SSH Key als Herdenkingsportaal

**Voordeel:** Eén key voor beide servers

```bash
# Lokaal (Windows PowerShell):
cat C:\Users\henkv\.ssh\id_ed25519.pub

# Copy de output

# Op Havun server:
mkdir -p ~/.ssh
chmod 700 ~/.ssh
nano ~/.ssh/authorized_keys

# Paste je public key
# Save & exit (Ctrl+X, Y, Enter)

chmod 600 ~/.ssh/authorized_keys

# Test connectie (nieuwe terminal):
ssh root@[havun-server-ip]
```

### Optie B: Nieuwe Dedicated SSH Key

**Voordeel:** Aparte key per server (veiliger)

```bash
# Lokaal (Windows PowerShell):
ssh-keygen -t ed25519 -C "havun@havun-server" -f C:\Users\henkv\.ssh\id_ed25519_havun

# Copy public key naar server:
type C:\Users\henkv\.ssh\id_ed25519_havun.pub | ssh root@[havun-server-ip] "mkdir -p ~/.ssh && cat >> ~/.ssh/authorized_keys"

# SSH config (C:\Users\henkv\.ssh\config):
Host havun
    HostName [havun-server-ip]
    User root
    IdentityFile C:\Users\henkv\.ssh\id_ed25519_havun
    PubkeyAuthentication yes
    PasswordAuthentication no

# Test:
ssh havun
```

### SSH Security Hardening

```bash
# Disable password authentication
nano /etc/ssh/sshd_config

# Zet deze waardes:
PasswordAuthentication no
PubkeyAuthentication yes
PermitRootLogin prohibit-password
ChallengeResponseAuthentication no

# Restart SSH
systemctl restart sshd

# Test in NIEUWE terminal (oude open houden als backup!)
ssh havun
```

---

## LAMP Stack Installatie

### PHP 8.2 Installatie

```bash
# Add PHP repository
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update

# Install PHP 8.2 + extensions
apt install -y php8.2 php8.2-{cli,fpm,mysql,xml,mbstring,curl,zip,gd,bcmath,intl,readline,sqlite3}

# Verifieer
php -v
# PHP 8.2.x should appear
```

### MySQL Installatie

```bash
# Install MySQL 8.0
apt install -y mysql-server

# Secure installation
mysql_secure_installation

# Antwoorden:
# - VALIDATE PASSWORD COMPONENT? → Y
# - Password validation policy level? → 2 (STRONG)
# - Set root password? → Y (kies sterk wachtwoord)
# - Remove anonymous users? → Y
# - Disallow root login remotely? → Y
# - Remove test database? → Y
# - Reload privilege tables? → Y

# Login test
mysql -u root -p
```

### Apache Installatie

```bash
# Install Apache
apt install -y apache2

# Enable modules
a2enmod rewrite
a2enmod ssl
a2enmod headers

# Restart Apache
systemctl restart apache2

# Test
curl http://localhost
# Should show Apache default page
```

### Composer Installatie

```bash
# Download installer
curl -sS https://getcomposer.org/installer | php

# Move to global location
mv composer.phar /usr/local/bin/composer

# Verifieer
composer --version
```

### Node.js & NPM (voor Laravel Mix/Vite)

```bash
# Install Node.js 20.x LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt install -y nodejs

# Verifieer
node --version
npm --version
```

### Git Installatie

```bash
apt install -y git

# Configureer
git config --global user.name "Havun"
git config --global user.email "havun22@gmail.com"

# Verifieer
git --version
```

---

## HavunAdmin Deployment

### Stap 1: Directory Structuur

```bash
# Create web root
mkdir -p /var/www/havunadmin
cd /var/www

# Set ownership
chown -R www-data:www-data /var/www
```

### Stap 2: Git Repository Setup

**Optie A: Clone van GitHub (als repo al bestaat)**

```bash
cd /var/www
git clone https://github.com/havun22-hvu/HavunAdmin.git havunadmin
cd havunadmin
```

**Optie B: Nieuwe Repository (als nog niet bestaat)**

```bash
# Lokaal (Windows):
cd D:\GitHub\
laravel new HavunAdmin
cd HavunAdmin
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/havun22-hvu/HavunAdmin.git
git push -u origin main

# Op server:
cd /var/www
git clone https://github.com/havun22-hvu/HavunAdmin.git havunadmin
cd havunadmin
```

### Stap 3: Laravel Setup

```bash
cd /var/www/havunadmin

# Install dependencies
composer install --no-dev --optimize-autoloader

# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# Edit .env
nano .env
```

**HavunAdmin .env configuratie:**

```env
APP_NAME=HavunAdmin
APP_ENV=production
APP_KEY=base64:... # Generated by artisan key:generate
APP_DEBUG=false
APP_URL=https://admin.havun.nl

# HavunAdmin eigen database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=havunadmin
DB_USERNAME=root
DB_PASSWORD=jouw_mysql_root_password

# Herdenkingsportaal PRODUCTION database (remote)
HERDENKINGSPORTAAL_PROD_CONNECTION=mysql
HERDENKINGSPORTAAL_PROD_HOST=188.245.159.115
HERDENKINGSPORTAAL_PROD_PORT=3306
HERDENKINGSPORTAAL_PROD_DATABASE=herdenkingsportaal
HERDENKINGSPORTAAL_PROD_USERNAME=havunadmin_readonly
HERDENKINGSPORTAAL_PROD_PASSWORD=secure_password_here

# Herdenkingsportaal STAGING database (remote)
# Note: SQLite over SSH niet direct mogelijk, gebruik MySQL replica of API
# Voor nu: alleen production data importeren

# Mail (optioneel, gebruik SendGrid of SMTP)
MAIL_MAILER=log
```

### Stap 4: Database Setup

```bash
# Login MySQL
mysql -u root -p

# Create database
CREATE DATABASE havunadmin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Exit
exit

# Run migrations
cd /var/www/havunadmin
php artisan migrate --force
```

### Stap 5: Permissions

```bash
cd /var/www/havunadmin

# Set ownership
chown -R www-data:www-data .

# Set permissions
chmod -R 755 .
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Storage link (voor uploads indien nodig)
php artisan storage:link
```

### Stap 6: Database Config (HavunAdmin)

**config/database.php:**

```php
<?php

return [
    'default' => env('DB_CONNECTION', 'mysql'),

    'connections' => [

        // HavunAdmin eigen database
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'havunadmin'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        // Herdenkingsportaal PRODUCTION (remote read-only)
        'herdenkingsportaal_prod' => [
            'driver' => 'mysql',
            'host' => env('HERDENKINGSPORTAAL_PROD_HOST', '127.0.0.1'),
            'port' => env('HERDENKINGSPORTAAL_PROD_PORT', 3306),
            'database' => env('HERDENKINGSPORTAAL_PROD_DATABASE', 'herdenkingsportaal'),
            'username' => env('HERDENKINGSPORTAAL_PROD_USERNAME', 'root'),
            'password' => env('HERDENKINGSPORTAAL_PROD_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
        ],
    ],
];
```

---

## Remote Database Connectie

### Stap 1: MySQL Gebruiker Aanmaken (Herdenkingsportaal Server)

```bash
# SSH naar Herdenkingsportaal server
ssh root@188.245.159.115

# Login MySQL
mysql -u root -p

# Create read-only user voor HavunAdmin
# Vervang [havun-server-ip] met IP van nieuwe server!
CREATE USER 'havunadmin_readonly'@'[havun-server-ip]' IDENTIFIED BY 'GenereerSterkWachtwoord123!';

# Grant SELECT permissions op specifieke tabellen
GRANT SELECT ON herdenkingsportaal.invoices TO 'havunadmin_readonly'@'[havun-server-ip]';
GRANT SELECT ON herdenkingsportaal.payment_transactions TO 'havunadmin_readonly'@'[havun-server-ip]';
GRANT SELECT ON herdenkingsportaal.memorials TO 'havunadmin_readonly'@'[havun-server-ip]';
GRANT SELECT ON herdenkingsportaal.users TO 'havunadmin_readonly'@'[havun-server-ip]';

FLUSH PRIVILEGES;

# Verifieer
SHOW GRANTS FOR 'havunadmin_readonly'@'[havun-server-ip]';

# Exit
exit
```

### Stap 2: MySQL External Access Configureren

```bash
# Edit MySQL config
nano /etc/mysql/mysql.conf.d/mysqld.cnf

# Find:
bind-address = 127.0.0.1

# Change to (allow external connections):
bind-address = 0.0.0.0

# Save & exit

# Restart MySQL
systemctl restart mysql
```

### Stap 3: Firewall Regel (Herdenkingsportaal Server)

```bash
# Allow MySQL (port 3306) ALLEEN van HavunAdmin server
ufw allow from [havun-server-ip] to any port 3306

# Verifieer
ufw status numbered

# Should show regel zoals:
# [X] 3306       ALLOW IN    [havun-server-ip]
```

### Stap 4: Test Connectie (vanaf Havun Server)

```bash
# SSH naar Havun server
ssh havun

# Test MySQL connectie
mysql -h 188.245.159.115 -u havunadmin_readonly -p -e "SHOW DATABASES;"

# Enter password: GenereerSterkWachtwoord123!

# Should show:
# +--------------------+
# | Database           |
# +--------------------+
# | herdenkingsportaal |
# | information_schema |
# +--------------------+

# Test query
mysql -h 188.245.159.115 -u havunadmin_readonly -p herdenkingsportaal -e "SELECT COUNT(*) FROM invoices;"
```

### Stap 5: Laravel Database Test

```bash
cd /var/www/havunadmin

# Test connectie via Laravel
php artisan tinker

# In tinker:
DB::connection('herdenkingsportaal_prod')->table('invoices')->count();
# Should return invoice count

# Test query
$invoices = DB::connection('herdenkingsportaal_prod')
    ->table('invoices')
    ->join('payment_transactions', 'invoices.payment_transaction_id', '=', 'payment_transactions.id')
    ->where('payment_transactions.status', 'paid')
    ->select('invoices.*', 'payment_transactions.paid_at')
    ->limit(5)
    ->get();

dd($invoices);

# Exit tinker
exit
```

---

## DNS & SSL Setup

### Stap 1: DNS Records Instellen

**Bij je DNS provider (bijv. Cloudflare, TransIP, etc.):**

```
Type    Name              Value                  TTL
────────────────────────────────────────────────────
A       admin.havun.nl    [havun-server-ip]     300
A       havun.nl          [havun-server-ip]     300
A       *.havun.nl        [havun-server-ip]     300  (wildcard voor toekomstige apps)
```

**Wacht 5-10 minuten op DNS propagatie, test:**

```bash
# Lokaal (Windows PowerShell):
nslookup admin.havun.nl
nslookup havun.nl

# Should return havun-server-ip
```

### Stap 2: Apache VirtualHost (HTTP - tijdelijk)

```bash
# Create VirtualHost config
nano /etc/apache2/sites-available/havunadmin.conf
```

**havunadmin.conf:**

```apache
<VirtualHost *:80>
    ServerName admin.havun.nl
    ServerAdmin havun22@gmail.com

    DocumentRoot /var/www/havunadmin/public

    <Directory /var/www/havunadmin/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/havunadmin-error.log
    CustomLog ${APACHE_LOG_DIR}/havunadmin-access.log combined
</VirtualHost>
```

**Activeer site:**

```bash
# Enable site
a2ensite havunadmin

# Disable default site
a2dissite 000-default

# Test config
apache2ctl configtest

# Reload Apache
systemctl reload apache2
```

**Test HTTP:**

```bash
# Lokaal browser:
http://admin.havun.nl

# Should show Laravel page (mogelijk error zonder .env setup, maar dat is OK)
```

### Stap 3: SSL Certificaat (Let's Encrypt)

```bash
# Install Certbot
apt install -y certbot python3-certbot-apache

# Generate certificate (interactief)
certbot --apache -d admin.havun.nl

# Vragen:
# Email: havun22@gmail.com
# Terms: A (Agree)
# Share email: N (No)
# Redirect HTTP to HTTPS: 2 (Yes)

# Test auto-renewal
certbot renew --dry-run
```

**Verifieer HTTPS:**

```bash
# Browser:
https://admin.havun.nl

# Should show green lock icon
```

### Stap 4: Firewall (UFW)

```bash
# Allow HTTP & HTTPS
ufw allow 'Apache Full'

# Allow SSH (BELANGRIJK!)
ufw allow OpenSSH

# Enable firewall
ufw enable

# Verifieer
ufw status
```

---

## Security Hardening

### 1. Fail2Ban (Brute Force Bescherming)

```bash
# Install
apt install -y fail2ban

# Create local config
cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
nano /etc/fail2ban/jail.local

# Find [sshd] section, zorg dat enabled = true
[sshd]
enabled = true
port = ssh
logpath = %(sshd_log)s
maxretry = 3
bantime = 3600

# Restart
systemctl restart fail2ban

# Check status
fail2ban-client status sshd
```

### 2. Automatic Security Updates

```bash
# Install unattended-upgrades
apt install -y unattended-upgrades

# Enable
dpkg-reconfigure -plow unattended-upgrades
# Select: Yes

# Verifieer
systemctl status unattended-upgrades
```

### 3. File Upload Limits (indien nodig)

```bash
# Edit PHP config
nano /etc/php/8.2/fpm/php.ini

# Adjust:
upload_max_filesize = 50M
post_max_size = 50M
max_execution_time = 300

# Restart PHP-FPM
systemctl restart php8.2-fpm
```

### 4. Laravel Security

```bash
cd /var/www/havunadmin

# Optimize voor productie
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Disable debug mode (check .env)
APP_DEBUG=false
```

---

## Toekomstige Apps

### Nieuwe App Toevoegen - Template

**Voorbeeld: Time Tracking App**

```bash
# 1. Clone/create app
cd /var/www
git clone https://github.com/havun22-hvu/TimeTracking.git timetracking
cd timetracking

# 2. Setup
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
nano .env  # Configure database

# 3. Database
mysql -u root -p
CREATE DATABASE timetracking;
exit
php artisan migrate --force

# 4. Permissions
chown -R www-data:www-data .
chmod -R 755 .
chmod -R 775 storage bootstrap/cache

# 5. VirtualHost
nano /etc/apache2/sites-available/timetracking.conf
```

**timetracking.conf:**

```apache
<VirtualHost *:80>
    ServerName time.havun.nl
    DocumentRoot /var/www/timetracking/public

    <Directory /var/www/timetracking/public>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/timetracking-error.log
    CustomLog ${APACHE_LOG_DIR}/timetracking-access.log combined
</VirtualHost>
```

```bash
# 6. Activeer & SSL
a2ensite timetracking
systemctl reload apache2
certbot --apache -d time.havun.nl

# 7. Test
curl https://time.havun.nl
```

### DNS Records voor Nieuwe Apps

**Add deze records bij DNS provider:**

```
A    time.havun.nl       [havun-server-ip]
A    projects.havun.nl   [havun-server-ip]
A    crm.havun.nl        [havun-server-ip]
```

**Wildcard werkt ook (al toegevoegd!):**
```
A    *.havun.nl          [havun-server-ip]
```

---

## Monitoring & Maintenance

### Disk Space Check

```bash
# Check disk usage
df -h

# Check largest directories
du -sh /var/www/*
du -sh /var/log/*

# Clean old logs
find /var/log -type f -name "*.log" -mtime +30 -delete
```

### Database Backups

```bash
# Create backup script
nano /root/backup-databases.sh
```

**backup-databases.sh:**

```bash
#!/bin/bash
BACKUP_DIR="/root/backups"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Backup HavunAdmin database
mysqldump -u root -p[password] havunadmin > $BACKUP_DIR/havunadmin_$DATE.sql

# Keep only last 7 days
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete

echo "Backup completed: $DATE"
```

```bash
# Make executable
chmod +x /root/backup-databases.sh

# Test
/root/backup-databases.sh

# Add to crontab (dagelijks om 3:00)
crontab -e

# Add:
0 3 * * * /root/backup-databases.sh > /dev/null 2>&1
```

### Laravel Logs

```bash
# Check logs
tail -f /var/www/havunadmin/storage/logs/laravel.log

# Clean old logs (ouder dan 30 dagen)
find /var/www/havunadmin/storage/logs -name "*.log" -mtime +30 -delete
```

### Apache Logs

```bash
# Real-time monitoring
tail -f /var/log/apache2/havunadmin-access.log
tail -f /var/log/apache2/havunadmin-error.log

# Disk usage
du -sh /var/log/apache2/

# Clean old logs (handled by logrotate automatisch)
```

---

## Deployment Workflow (Updates)

### HavunAdmin Update Deployen

```bash
# Lokaal (Windows):
cd D:\GitHub\HavunAdmin
git add .
git commit -m "Feature X"
git push origin main

# Server:
ssh havun
cd /var/www/havunadmin

# Pull updates
git pull origin main

# Update dependencies
composer install --no-dev --optimize-autoloader

# Run migrations (indien nodig)
php artisan migrate --force

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Re-cache voor performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart PHP-FPM (optioneel maar aanbevolen)
systemctl restart php8.2-fpm

# Test
curl https://admin.havun.nl
```

---

## Troubleshooting

### Apache niet bereikbaar

```bash
# Check Apache status
systemctl status apache2

# Check errors
tail -50 /var/log/apache2/error.log

# Restart Apache
systemctl restart apache2

# Test config
apache2ctl configtest
```

### Laravel 500 Error

```bash
# Check Laravel logs
tail -50 /var/www/havunadmin/storage/logs/laravel.log

# Check permissions
ls -la /var/www/havunadmin/storage
ls -la /var/www/havunadmin/bootstrap/cache

# Fix permissions
cd /var/www/havunadmin
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Database Connectie Faalt

```bash
# Test MySQL local
mysql -u root -p

# Test remote connection
mysql -h 188.245.159.115 -u havunadmin_readonly -p

# Check firewall Herdenkingsportaal server
ssh root@188.245.159.115
ufw status numbered

# Check MySQL bind address
grep bind-address /etc/mysql/mysql.conf.d/mysqld.cnf
# Should be: 0.0.0.0

# Check user exists
mysql -u root -p
SELECT User, Host FROM mysql.user WHERE User='havunadmin_readonly';
```

### SSL Certificaat Expired

```bash
# Check expiry
certbot certificates

# Renew manually
certbot renew

# Test auto-renewal
certbot renew --dry-run
```

---

## Quick Reference Commands

```bash
# SSH
ssh havun                                    # Login naar Havun server

# Laravel
cd /var/www/havunadmin
php artisan migrate --force                  # Run migrations
php artisan config:cache                     # Cache config
php artisan cache:clear                      # Clear cache

# Apache
systemctl restart apache2                    # Restart webserver
apache2ctl configtest                        # Test config
a2ensite sitename                           # Enable site
tail -f /var/log/apache2/error.log          # Watch error log

# MySQL
mysql -u root -p                            # Local MySQL
mysql -h 188.245.159.115 -u user -p         # Remote MySQL

# Permissions
chown -R www-data:www-data /var/www/app     # Fix ownership
chmod -R 775 storage bootstrap/cache        # Fix permissions

# Monitoring
df -h                                       # Disk usage
htop                                        # Process monitor
ufw status                                  # Firewall status
systemctl status apache2                    # Apache status
```

---

## Checklist - Setup Compleet

- [ ] Hetzner server aangemaakt (CX11)
- [ ] SSH key geconfigureerd
- [ ] LAMP stack geïnstalleerd (PHP 8.2, MySQL, Apache)
- [ ] HavunAdmin gedeployed
- [ ] Database connections getest (local + remote)
- [ ] DNS records ingesteld (admin.havun.nl, havun.nl)
- [ ] SSL certificaten geïnstalleerd (Let's Encrypt)
- [ ] Firewall geconfigureerd (UFW)
- [ ] Fail2Ban actief
- [ ] Automatic updates enabled
- [ ] Backup script geconfigureerd
- [ ] Test deployment gedaan
- [ ] Herdenkingsportaal firewall regel toegevoegd
- [ ] Remote database connectie getest

---

## Support & Documentatie

**Hetzner Docs:**
- https://docs.hetzner.com/cloud/

**Laravel Deployment:**
- https://laravel.com/docs/10.x/deployment

**Let's Encrypt:**
- https://certbot.eff.org/

**Related Docs:**
- [DEPLOYMENT-PROTOCOL.md](DEPLOYMENT-PROTOCOL.md) - General deployment rules
- [SERVER-ACCESS.md](SERVER-ACCESS.md) - SSH access Herdenkingsportaal server

---

**📅 Laatste Update:** 26 Oktober 2025
**✅ Status:** Production Ready Template
**👤 Auteur:** Claude + Henk van Unen
