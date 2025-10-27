# Hetzner Server Setup Guide - havun.nl

**Doel**: Eigen server voor havun.nl met meerdere applicaties (start met HavunAdmin)

**Server specs aanbeveling**:
- **Type**: Cloud Server (VPS)
- **Location**: Falkenstein (Duitsland) of Helsinki (Finland) - dichtstbij Nederland
- **CPU**: 2 vCPU (AMD)
- **RAM**: 4 GB
- **Storage**: 40 GB SSD
- **Traffic**: 20 TB
- **Prijs**: ~€4,51/maand (CX21)

---

## Stap 1: Hetzner Account & Server Aanmaken

### 1.1 Inloggen op Hetzner Cloud Console

1. Ga naar: https://console.hetzner.cloud/
2. Log in met je Hetzner account
3. Klik op **"+ New Project"** of selecteer bestaand project

### 1.2 Server Aanmaken

1. **Project naam**: `Havun Production` (of gewoon "Havun")
2. Klik **"Add Server"**

**Server Configuratie**:

| Setting | Waarde | Waarom |
|---------|--------|--------|
| **Location** | Falkenstein (fsn1) | Dichtste bij Nederland, goede prijs |
| **Image** | Ubuntu 24.04 LTS | Stabiel, lang ondersteund |
| **Type** | Shared vCPU | Goedkoper, voldoende voor meerdere apps |
| **Server Type** | CX22 (2 vCPU, 4GB RAM, 40GB) | €4,51/maand - perfect voor Laravel apps |
| **Networking** | IPv4 + IPv6 | Beide aanvinken |
| **SSH Keys** | Toevoegen (stap 1.3) | Veiliger dan wachtwoord |
| **Volumes** | Geen (nu nog niet) | Kan later toevoegen |
| **Firewalls** | Aanmaken (stap 1.4) | Security vanaf dag 1 |
| **Backups** | Optioneel (€0,90/maand) | Aanrader! |
| **Placement Groups** | Geen | Niet nodig |
| **Labels** | `env:production`, `app:havun` | Voor organisatie |
| **Cloud Config** | Geen | Handmatig configureren |
| **Server Name** | `havun-production` | Herkenbare naam |

### 1.3 SSH Key Toevoegen (BELANGRIJK!)

**Op je Windows machine (Git Bash of PowerShell)**:

```bash
# Check of je al een SSH key hebt
ls ~/.ssh/

# Als je al id_rsa.pub hebt, gebruik die
# Anders maak nieuwe aan:
ssh-keygen -t rsa -b 4096 -C "henk@havun.nl"
# Druk gewoon Enter voor default locatie
# Optioneel: wachtwoord toevoegen (aangeraden!)

# Kopieer de public key
cat ~/.ssh/id_rsa.pub
```

**In Hetzner Console**:
1. Klik bij SSH Keys op **"Add SSH Key"**
2. Plak je public key (hele output van `cat ~/.ssh/id_rsa.pub`)
3. Naam: `Henk Laptop` of `Development Machine`
4. Klik **"Add SSH Key"**

### 1.4 Firewall Aanmaken

**In Hetzner Console**:
1. Ga naar **Firewalls** in sidebar
2. Klik **"Create Firewall"**
3. Naam: `havun-production-firewall`

**Inbound Rules** (wat mag naar binnen):

| Direction | Protocol | Port | Source | Beschrijving |
|-----------|----------|------|--------|--------------|
| In | TCP | 22 | 0.0.0.0/0, ::/0 | SSH (later beperken tot jouw IP) |
| In | TCP | 80 | 0.0.0.0/0, ::/0 | HTTP (redirect naar HTTPS) |
| In | TCP | 443 | 0.0.0.0/0, ::/0 | HTTPS (apps) |
| In | ICMP | - | 0.0.0.0/0, ::/0 | Ping (optioneel) |

**Outbound Rules**: Standaard alles toestaan (default)

4. Klik **"Create Firewall"**
5. Apply naar je server (tijdens server aanmaak of later)

### 1.5 Server Aanmaken - Finish

1. Review je configuratie
2. Kosten: ~€4,51/maand (CX22) of ~€5,83/maand (met backups)
3. Klik **"Create & Buy Now"**

**Noteer**:
- ✅ Server IP adres (bijv. `95.217.123.456`)
- ✅ Root wachtwoord (wordt gemaild, maar niet nodig als je SSH key hebt)

---

## Stap 2: Eerste Login & Basis Security

### 2.1 SSH Verbinden

```bash
# Vervang IP_ADDRESS met je server IP
ssh root@95.217.123.456

# Eerste keer: bevestig fingerprint (yes)
# Als je SSH key wachtwoord hebt, voer in
```

**Je bent nu ingelogd als root!** 🎉

### 2.2 Systeem Updaten

```bash
# Update package lists
apt update

# Upgrade alle packages
apt upgrade -y

# Herstart als kernel update (optioneel)
# reboot
```

### 2.3 Non-Root User Aanmaken

**BELANGRIJK**: Nooit als root werken!

```bash
# Maak user 'havun' aan
adduser havun

# Vul in:
# - Password: [STERK WACHTWOORD - bewaar veilig!]
# - Full Name: Havun Admin (of leeg laten)
# - Overige: Enter drukken (leeg laten)

# Geef sudo rechten
usermod -aG sudo havun

# Test: switch naar havun user
su - havun

# Test sudo
sudo apt update
# Voer havun password in

# Terug naar root
exit
```

### 2.4 SSH Key Kopiëren naar Havun User

```bash
# Als root:
mkdir -p /home/havun/.ssh
cp ~/.ssh/authorized_keys /home/havun/.ssh/
chown -R havun:havun /home/havun/.ssh
chmod 700 /home/havun/.ssh
chmod 600 /home/havun/.ssh/authorized_keys
```

**Test nieuwe user**:
```bash
# Log uit van server
exit

# Log in als havun (vanaf je laptop)
ssh havun@95.217.123.456

# Zou moeten werken zonder wachtwoord (SSH key)
```

### 2.5 SSH Hardening (Optioneel maar Aangeraden)

```bash
# Als havun user
sudo nano /etc/ssh/sshd_config
```

**Wijzig/voeg toe**:
```conf
# Disable root login
PermitRootLogin no

# Disable password authentication (alleen SSH keys)
PasswordAuthentication no

# Alleen havun user mag inloggen
AllowUsers havun

# Optioneel: wijzig SSH port (vermijd 22)
# Port 2222
```

**Herstart SSH**:
```bash
sudo systemctl restart sshd

# ⚠️ NIET UITLOGGEN NOG! Test in nieuw terminal venster:
# ssh havun@95.217.123.456
# Als dit werkt, is het veilig om uit te loggen
```

### 2.6 Firewall via UFW (Extra Laag)

```bash
# Installeer UFW (Ubuntu Firewall)
sudo apt install ufw -y

# Default policies
sudo ufw default deny incoming
sudo ufw default allow outgoing

# Allow SSH (BELANGRIJK!)
sudo ufw allow 22/tcp
# Of als je port wijzigde: sudo ufw allow 2222/tcp

# Allow HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Enable firewall
sudo ufw enable

# Check status
sudo ufw status verbose
```

---

## Stap 3: LEMP Stack Installeren (Linux, Nginx, MySQL, PHP)

### 3.1 NGINX Installeren

```bash
sudo apt install nginx -y

# Start en enable
sudo systemctl start nginx
sudo systemctl enable nginx

# Check status
sudo systemctl status nginx

# Test: ga naar http://95.217.123.456 in browser
# Zou "Welcome to nginx!" moeten tonen
```

### 3.2 MySQL Installeren

```bash
# Installeer MySQL Server
sudo apt install mysql-server -y

# Start en enable
sudo systemctl start mysql
sudo systemctl enable mysql

# Secure installation
sudo mysql_secure_installation
```

**MySQL Secure Installation Wizard**:
```
VALIDATE PASSWORD COMPONENT? [y/N]: N (tenzij je sterke wachtwoord check wilt)

New password: [STERK MYSQL ROOT WACHTWOORD - BEWAAR VEILIG!]
Re-enter: [HERHAAL]

Remove anonymous users? Y
Disallow root login remotely? Y
Remove test database? Y
Reload privilege tables? Y
```

**MySQL User voor HavunAdmin**:
```bash
# Login als root
sudo mysql

# In MySQL prompt:
CREATE DATABASE havunadmin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'havunadmin'@'localhost' IDENTIFIED BY 'STERK_WACHTWOORD_HIER';
GRANT ALL PRIVILEGES ON havunadmin.* TO 'havunadmin'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Test login
mysql -u havunadmin -p havunadmin
# Voer wachtwoord in
# Als je inlogt, werkt het!
# EXIT om uit te gaan
```

### 3.3 PHP 8.3 Installeren (voor Laravel 11)

```bash
# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.3 + extensions
sudo apt install -y php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring \
  php8.3-xml php8.3-bcmath php8.3-curl php8.3-zip php8.3-gd \
  php8.3-intl php8.3-soap php8.3-redis

# Check versie
php -v
# Zou moeten tonen: PHP 8.3.x

# Start PHP-FPM
sudo systemctl start php8.3-fpm
sudo systemctl enable php8.3-fpm
```

### 3.4 Composer Installeren

```bash
# Download Composer installer
cd ~
curl -sS https://getcomposer.org/installer -o composer-setup.php

# Install globally
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Check
composer --version

# Cleanup
rm composer-setup.php
```

### 3.5 Node.js & NPM Installeren (voor Laravel Mix/Vite)

```bash
# Install Node.js 20.x LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Check versies
node -v  # Should be v20.x
npm -v   # Should be 10.x
```

---

## Stap 4: Domain & DNS Configuratie

### 4.1 DNS Records Toevoegen (bij jouw domain provider)

**Ga naar je domain registrar** (bijv. mijn.host, TransIP, etc.)

**DNS Records voor havun.nl**:

| Type | Name | Value | TTL |
|------|------|-------|-----|
| A | @ | 95.217.123.456 | 3600 |
| A | www | 95.217.123.456 | 3600 |
| A | admin | 95.217.123.456 | 3600 |
| A | *.havun.nl | 95.217.123.456 | 3600 |
| AAAA | @ | [IPv6 van server] | 3600 |

**Uitleg**:
- `@` = havun.nl (root domain)
- `www` = www.havun.nl
- `admin` = admin.havun.nl (HavunAdmin)
- `*` = wildcard voor alle subdomeinen

### 4.2 Check DNS Propagatie

```bash
# Op je laptop (Git Bash)
nslookup admin.havun.nl

# Of online: https://www.whatsmydns.net/
```

DNS propagatie kan 5 minuten tot 48 uur duren (meestal binnen 1-2 uur).

---

## Stap 5: NGINX Configuratie voor HavunAdmin

### 5.1 Maak Directory Structuur

```bash
# Als havun user
sudo mkdir -p /var/www/havun.nl/admin
sudo chown -R havun:www-data /var/www/havun.nl
sudo chmod -R 755 /var/www/havun.nl
```

### 5.2 NGINX Server Block voor admin.havun.nl

```bash
sudo nano /etc/nginx/sites-available/admin.havun.nl
```

**Plak deze configuratie**:
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name admin.havun.nl;

    root /var/www/havun.nl/admin/public;
    index index.php index.html;

    access_log /var/log/nginx/admin.havun.nl-access.log;
    error_log /var/log/nginx/admin.havun.nl-error.log;

    # Laravel rewrite rules
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM configuration
    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to sensitive files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Prevent access to storage folder
    location ~* /(storage|bootstrap/cache) {
        deny all;
    }
}
```

**Enable site**:
```bash
# Create symlink
sudo ln -s /etc/nginx/sites-available/admin.havun.nl /etc/nginx/sites-enabled/

# Test config
sudo nginx -t

# Reload NGINX
sudo systemctl reload nginx
```

---

## Stap 6: HavunAdmin Deployen

### 6.1 Git Repository Clonen

```bash
# Als havun user
cd /var/www/havun.nl/

# Clone repository (gebruik je GitHub repo)
git clone https://github.com/jouw-username/HavunAdmin.git admin

# Of als private repo:
git clone git@github.com:jouw-username/HavunAdmin.git admin

cd admin
```

### 6.2 Composer Dependencies Installeren

```bash
cd /var/www/havun.nl/admin

# Install dependencies (production)
composer install --no-dev --optimize-autoloader
```

### 6.3 Environment File Setup

```bash
# Copy example
cp .env.example .env

# Edit .env
nano .env
```

**Belangrijke settings in .env**:
```env
APP_NAME="Havun Admin"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://admin.havun.nl

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=havunadmin
DB_USERNAME=havunadmin
DB_PASSWORD=JOUW_MYSQL_WACHTWOORD_HIER

# Gmail API
GMAIL_CLIENT_ID=361603632927-rj4pkh18eq8un4vgq9l5ckk70murd1v6.apps.googleusercontent.com
GMAIL_CLIENT_SECRET=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
GMAIL_REDIRECT_URI=https://admin.havun.nl/gmail/callback

# Mollie API
MOLLIE_API_KEY=live_aKqTe...

# Bunq API (zodra geconfigureerd)
# BUNQ_API_KEY=...
```

**Generate app key**:
```bash
php artisan key:generate
```

### 6.4 Database Migratie

```bash
# Run migrations
php artisan migrate --force

# ⚠️ --force is nodig in production
```

### 6.5 Storage Permissions

```bash
# Storage en cache writable maken
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 6.6 NPM Build (Frontend Assets)

```bash
# Install dependencies
npm install

# Build for production
npm run build

# Verwijder node_modules (niet nodig in productie)
rm -rf node_modules
```

### 6.7 Optimize Laravel

```bash
# Cache config
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

---

## Stap 7: SSL Certificaat (Let's Encrypt)

### 7.1 Certbot Installeren

```bash
# Install Certbot
sudo apt install certbot python3-certbot-nginx -y
```

### 7.2 SSL Certificaat Verkrijgen

```bash
# Verkrijg certificaat voor admin.havun.nl
sudo certbot --nginx -d admin.havun.nl

# Volg de wizard:
# Email: havun22@gmail.com
# Terms: Agree
# Email sharing: Your choice (N is ok)
# Redirect HTTP to HTTPS: Yes (2)
```

**Automatische vernieuwing**:
```bash
# Test renewal
sudo certbot renew --dry-run

# Certbot maakt automatisch cron job voor vernieuwing
# Check: sudo systemctl status certbot.timer
```

### 7.3 Test HTTPS

Ga naar: **https://admin.havun.nl**

Zou je Laravel app moeten tonen met groene slot! 🔒✅

---

## Stap 8: Eerste Login & Testen

### 8.1 Create Admin User

```bash
cd /var/www/havun.nl/admin

# Tinker
php artisan tinker

# In tinker:
App\Models\User::create([
    'name' => 'Henk van Unen',
    'email' => 'henk@havun.nl',
    'password' => bcrypt('JOUW_STERKE_WACHTWOORD')
]);

# Exit tinker
exit
```

### 8.2 Login Testen

1. Ga naar: https://admin.havun.nl/login
2. Login met henk@havun.nl + jouw wachtwoord
3. Check of dashboard werkt!

### 8.3 Gmail OAuth Testen

1. Ga naar Sync pagina
2. Update Google Cloud Console redirect URI:
   - https://admin.havun.nl/gmail/callback
3. Klik "Gmail Koppelen"
4. Autoriseer
5. Test "Scan Inkomsten"

---

## Stap 9: Monitoring & Backups

### 9.1 Hetzner Backups (Aangeraden)

**In Hetzner Console**:
1. Ga naar je server
2. Tab "Backups"
3. Enable Backups (€0,90/maand extra)
4. Kies schema: Weekly op zondag 03:00 (default is prima)

### 9.2 Database Backups (Extra)

**Maak backup script**:
```bash
nano ~/backup-database.sh
```

**Script inhoud**:
```bash
#!/bin/bash
BACKUP_DIR="/home/havun/backups"
DATE=$(date +%Y%m%d_%H%M%S)
FILENAME="havunadmin_$DATE.sql"

mkdir -p $BACKUP_DIR

mysqldump -u havunadmin -p'JOUW_MYSQL_WACHTWOORD' havunadmin > $BACKUP_DIR/$FILENAME

# Compress
gzip $BACKUP_DIR/$FILENAME

# Keep alleen laatste 7 dagen
find $BACKUP_DIR -name "havunadmin_*.sql.gz" -mtime +7 -delete

echo "Backup created: $FILENAME.gz"
```

**Maak executable en test**:
```bash
chmod +x ~/backup-database.sh
./backup-database.sh
```

**Cron job (dagelijks om 3:00)**:
```bash
crontab -e

# Add deze regel:
0 3 * * * /home/havun/backup-database.sh >> /home/havun/backup.log 2>&1
```

### 9.3 Logs Monitoring

```bash
# NGINX error log
sudo tail -f /var/log/nginx/admin.havun.nl-error.log

# Laravel log
tail -f /var/www/havun.nl/admin/storage/logs/laravel.log

# PHP-FPM log
sudo tail -f /var/log/php8.3-fpm.log
```

---

## Volgende Apps Toevoegen

Voor toekomstige apps (IDSee, Judotoernooi):

```bash
# 1. Nieuwe directory
sudo mkdir -p /var/www/havun.nl/idsee

# 2. Clone repository
cd /var/www/havun.nl
git clone https://github.com/... idsee

# 3. Nieuwe NGINX config
sudo nano /etc/nginx/sites-available/idsee.havun.nl
# Copy van admin.havun.nl, pas paths aan

# 4. Enable site
sudo ln -s /etc/nginx/sites-available/idsee.havun.nl /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx

# 5. SSL certificaat
sudo certbot --nginx -d idsee.havun.nl

# 6. Deploy zoals HavunAdmin (stap 6)
```

---

## Troubleshooting

### NGINX 502 Bad Gateway
```bash
# Check PHP-FPM status
sudo systemctl status php8.3-fpm

# Check socket file exists
ls -la /var/run/php/php8.3-fpm.sock

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

### Permission Errors
```bash
# Fix storage permissions
cd /var/www/havun.nl/admin
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Database Connection Error
```bash
# Test MySQL connection
mysql -u havunadmin -p havunadmin

# Check .env database credentials
cd /var/www/havun.nl/admin
cat .env | grep DB_
```

### Can't Update Code (Git Pull Fails)
```bash
# Cache clear eerst
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Git pull
git pull origin main

# Opnieuw cache opbouwen
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## Kosten Overzicht

| Item | Kosten | Frequentie |
|------|--------|------------|
| Hetzner CX22 VPS | €4,51 | /maand |
| Hetzner Backups | €0,90 | /maand |
| SSL Certificaat | €0,00 | Gratis (Let's Encrypt) |
| **Totaal** | **€5,41** | **/maand** |

---

## Security Checklist

- [x] SSH keys only (geen password login)
- [x] Non-root user (havun)
- [x] Firewall (Hetzner + UFW)
- [x] SSL certificaat (HTTPS)
- [x] MySQL secure installation
- [x] NGINX security headers
- [x] Laravel in production mode (APP_DEBUG=false)
- [x] Regular backups (Hetzner + database)
- [ ] Regular updates: `sudo apt update && sudo apt upgrade`
- [ ] Monitor logs regelmatig

---

## Support

**Voor vragen**:
- Hetzner Docs: https://docs.hetzner.com/
- Laravel Deployment: https://laravel.com/docs/11.x/deployment
- NGINX Docs: https://nginx.org/en/docs/

**Contact**: henk@havun.nl
