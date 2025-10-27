# HavunAdmin - Security Hardening Guide

> **Doel**: Server maximaal beveiligen tegen inbrekers en aanvallen
> **Laatst bijgewerkt**: 28 Oktober 2025

---

## ⚠️ KRITIEKE SECURITY ISSUES

### Huidige Status
- ⚠️ **ROOT LOGIN ACTIEF** - Root kan direct inloggen (groot risico!)
- ⚠️ **FIREWALL NIET GECONFIGUREERD** - Alle poorten staan open
- ⚠️ **GEEN FAIL2BAN** - Geen bescherming tegen brute force attacks
- ⚠️ **DATABASE ROOT USER** - App draait met MySQL root account
- ⚠️ **APP_DEBUG=true** - Debug mode toont sensitive info

**Deze moeten DIRECT gefixt worden!**

---

## 🔒 Security Checklist

### Level 1: KRITIEK (Direct implementeren)
- [ ] Firewall configureren (UFW)
- [ ] Fail2ban installeren
- [ ] SSH hardening (disable root password login)
- [ ] MySQL dedicated user aanmaken
- [ ] APP_DEBUG=false in .env
- [ ] .env file permissions (600)
- [ ] Disable Apache directory listing

### Level 2: BELANGRIJK (Deze week)
- [ ] SSH port wijzigen (van 22 naar custom)
- [ ] Apache security headers
- [ ] Rate limiting
- [ ] Force HTTPS redirects
- [ ] Hide server version info
- [ ] Automated backups

### Level 3: RECOMMENDED (Volgende week)
- [ ] Intrusion detection (AIDE)
- [ ] Log monitoring (Logwatch)
- [ ] Security updates automation
- [ ] 2FA voor SSH (optioneel)
- [ ] IP whitelist voor SSH (optioneel)

---

## 🛡️ LEVEL 1: KRITIEKE SECURITY FIXES

### 1. Firewall Configureren (UFW)

**WAT**: Alleen essentiële poorten open, rest dicht.

```bash
# SSH naar server
ssh root@46.224.31.30

# UFW installeren (indien nog niet geïnstalleerd)
apt update
apt install ufw -y

# Default policies: deny incoming, allow outgoing
ufw default deny incoming
ufw default allow outgoing

# Sta ALLEEN deze poorten toe:
ufw allow 22/tcp        # SSH (later wijzigen naar custom port)
ufw allow 80/tcp        # HTTP
ufw allow 443/tcp       # HTTPS

# Enable firewall
ufw enable

# Check status
ufw status verbose
```

**Verwachte output:**
```
Status: active
To                         Action      From
--                         ------      ----
22/tcp                     ALLOW       Anywhere
80/tcp                     ALLOW       Anywhere
443/tcp                    ALLOW       Anywhere
```

**Wat dit doet:**
- ✅ Blokkeert ALLE andere poorten (MySQL, FTP, etc.)
- ✅ Voorkomt dat hackers random services kunnen scannen
- ✅ SSH, HTTP, HTTPS blijven werken

---

### 2. Fail2ban Installeren

**WAT**: Automatisch IP's bannen die te vaak foutieve login proberen.

```bash
# Installeer fail2ban
apt install fail2ban -y

# Kopieer default config
cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local

# Edit config
nano /etc/fail2ban/jail.local
```

**Configuratie toevoegen:**
```ini
[DEFAULT]
# Ban IP voor 1 uur na 5 pogingen binnen 10 minuten
bantime  = 3600
findtime = 600
maxretry = 5

# Email alerts (optioneel)
destemail = havun22@gmail.com
sendername = Fail2Ban-HavunAdmin
action = %(action_mwl)s

[sshd]
enabled = true
port = 22
logpath = /var/log/auth.log

[apache-auth]
enabled = true
port = http,https
logpath = /var/log/apache2/*error.log

[apache-badbots]
enabled = true
port = http,https
logpath = /var/log/apache2/*access.log

[apache-noscript]
enabled = true
port = http,https
logpath = /var/log/apache2/*error.log
```

**Start fail2ban:**
```bash
systemctl enable fail2ban
systemctl start fail2ban
systemctl status fail2ban

# Check banned IPs
fail2ban-client status sshd
```

**Wat dit doet:**
- ✅ Automatisch IPs bannen na 5 foutieve SSH login pogingen
- ✅ Beschermt Apache tegen brute force
- ✅ Blokkeert bad bots en scanners
- ✅ Email notificaties bij ban (optioneel)

---

### 3. SSH Hardening

**WAT**: Disable root password login, alleen SSH keys toestaan.

```bash
# Backup SSH config
cp /etc/ssh/sshd_config /etc/ssh/sshd_config.backup

# Edit SSH config
nano /etc/ssh/sshd_config
```

**Wijzigingen maken:**
```bash
# Vind en wijzig deze regels:
PermitRootLogin prohibit-password    # Root alleen via SSH key
PasswordAuthentication no             # Disable password login
PubkeyAuthentication yes              # Enable SSH key login
PermitEmptyPasswords no               # Geen lege passwords
MaxAuthTries 3                        # Max 3 login pogingen
ClientAliveInterval 300               # Disconnect na 5 min inactivity
ClientAliveCountMax 2                 # 2 keer timeout = disconnect

# Optioneel: Verander SSH port (ALLEEN als je zeker weet dat je key werkt!)
# Port 2222                           # Custom port i.p.v. 22
```

**Test en herstart SSH:**
```bash
# Test config (moet "OK" geven)
sshd -t

# Herstart SSH
systemctl restart sshd

# BELANGRIJK: Test login in NIEUWE terminal (niet deze sluiten!)
# ssh root@46.224.31.30
```

⚠️ **WAARSCHUWING**: Test eerst of SSH key login werkt voordat je PasswordAuthentication disabled!

**Wat dit doet:**
- ✅ Alleen SSH key login mogelijk (geen password guessing)
- ✅ Root kan niet meer met password inloggen
- ✅ Max 3 login pogingen (voorkomt brute force)
- ✅ Auto-disconnect inactieve sessies

---

### 4. MySQL Dedicated User

**WAT**: App mag NIET met root draaien, dedicated user met minimale rechten.

```bash
# Login op MySQL
mysql -u root -p'7Ut0xaLzh7s^T2!DmQKR'
```

**SQL commands:**
```sql
-- Maak dedicated user voor HavunAdmin
CREATE USER 'havunadmin_app'@'localhost' IDENTIFIED BY 'STRONG_RANDOM_PASSWORD_HERE';

-- Geef ALLEEN rechten op havunadmin_staging database
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, ALTER
ON havunadmin_staging.*
TO 'havunadmin_app'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Test
SHOW GRANTS FOR 'havunadmin_app'@'localhost';

EXIT;
```

**Update .env file:**
```bash
cd /var/www/staging
nano .env

# Wijzig:
DB_USERNAME=havunadmin_app
DB_PASSWORD=STRONG_RANDOM_PASSWORD_HERE
```

**Test database connectie:**
```bash
php artisan migrate:status
```

**Wat dit doet:**
- ✅ App draait niet meer met root (beperkte schade bij inbraak)
- ✅ User heeft alleen rechten op eigen database
- ✅ Kan geen andere databases zien/wijzigen
- ✅ Kan geen MySQL users aanmaken/verwijderen

---

### 5. Laravel Security (.env)

**WAT**: Debug mode uit, secure permissions.

```bash
cd /var/www/staging

# Edit .env
nano .env
```

**Wijzigingen:**
```env
# BELANGRIJK: Debug UIT in staging/production!
APP_DEBUG=false
APP_ENV=staging

# Zorg dat APP_KEY ingevuld is
APP_KEY=base64:...

# Database (nieuwe user)
DB_USERNAME=havunadmin_app
DB_PASSWORD=STRONG_PASSWORD_HERE

# Trusted proxies (voor SSL/HTTPS)
TRUSTED_PROXIES=*
```

**Permissions:**
```bash
# .env MOET 600 zijn (alleen owner kan lezen)
chmod 600 .env

# Storage en cache directories
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Public directory
chmod -R 755 public

# Zorg dat .env NOOIT web accessible is
cat public/.htaccess
```

**Verify .htaccess bevat:**
```apache
<FilesMatch "^\.">
    Require all denied
</FilesMatch>
```

**Wat dit doet:**
- ✅ APP_DEBUG=false: Geen stack traces naar bezoekers
- ✅ .env file 600: Alleen root kan lezen (credentials beschermd)
- ✅ Storage niet web accessible
- ✅ .env niet downloadbaar via browser

---

### 6. Apache Security

**WAT**: Hide server info, disable directory listing, security headers.

```bash
# Edit Apache security config
nano /etc/apache2/conf-available/security.conf
```

**Wijzigingen:**
```apache
# Hide Apache version
ServerTokens Prod
ServerSignature Off

# Disable directory listing
<Directory />
    Options -Indexes
    AllowOverride None
    Require all denied
</Directory>

# Security headers
Header always set X-Content-Type-Options "nosniff"
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "no-referrer-when-downgrade"
```

**Edit VirtualHost config:**
```bash
nano /etc/apache2/sites-available/staging.admin.havun.nl.conf
```

**Toevoegen binnen <VirtualHost> block:**
```apache
<VirtualHost *:443>
    # ... existing config ...

    # Force HTTPS
    <IfModule mod_headers.c>
        Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    </IfModule>

    # Disable .git access
    <DirectoryMatch "^/.*/\.git/">
        Require all denied
    </DirectoryMatch>

    # Disable .env access
    <FilesMatch "^\.env">
        Require all denied
    </FilesMatch>

    # Rate limiting (optioneel, vereist mod_evasive)
    # DOSHashTableSize 3097
    # DOSPageCount 5
    # DOSSiteCount 100
    # DOSPageInterval 2
    # DOSSiteInterval 2
</VirtualHost>
```

**Enable headers module en reload:**
```bash
a2enmod headers
systemctl reload apache2
```

**Test:**
```bash
curl -I https://staging.admin.havun.nl
# Moet security headers tonen (X-Content-Type-Options, etc.)
```

**Wat dit doet:**
- ✅ Verbergt Apache versie (hackers zien niet welke versie je draait)
- ✅ Geen directory listings (kan niet door folders bladeren)
- ✅ HSTS header (browser forceert HTTPS)
- ✅ XSS protection headers
- ✅ .git en .env niet toegankelijk via web

---

## 🔐 LEVEL 2: BELANGRIJKE SECURITY UPDATES

### 7. SSH Port Wijzigen (OPTIONEEL maar STERK AANBEVOLEN)

**WAAROM**: Default port 22 wordt constant gescand door bots. Custom port = veel minder aanvallen.

```bash
# Edit SSH config
nano /etc/ssh/sshd_config

# Wijzig:
Port 2222    # Of andere random port tussen 1024-65535

# Update firewall
ufw allow 2222/tcp
ufw delete allow 22/tcp

# Herstart SSH
systemctl restart sshd
```

**Update je SSH command:**
```bash
# Nieuwe manier van inloggen:
ssh -p 2222 root@46.224.31.30
```

⚠️ **LET OP**: Test EERST met nieuwe port voordat je 22 sluit! Houd oude terminal open totdat nieuwe werkt.

---

### 8. Automated Security Updates

**WAT**: Automatisch security patches installeren.

```bash
# Installeer unattended-upgrades
apt install unattended-upgrades -y

# Configureer
nano /etc/apt/apt.conf.d/50unattended-upgrades
```

**Enable security updates:**
```
Unattended-Upgrade::Allowed-Origins {
    "${distro_id}:${distro_codename}-security";
};

Unattended-Upgrade::Automatic-Reboot "false";
Unattended-Upgrade::Mail "havun22@gmail.com";
```

**Enable:**
```bash
dpkg-reconfigure -plow unattended-upgrades
systemctl enable unattended-upgrades
systemctl start unattended-upgrades
```

---

### 9. Database Backups

**WAT**: Automatische dagelijkse backups.

```bash
# Maak backup script
nano /usr/local/bin/havunadmin-backup.sh
```

**Script inhoud:**
```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/root/backups/havunadmin"
DB_NAME="havunadmin_staging"
DB_USER="root"
DB_PASS="7Ut0xaLzh7s^T2!DmQKR"

# Maak backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Application backup (zonder vendor, node_modules)
tar -czf $BACKUP_DIR/app_$DATE.tar.gz \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='storage/logs' \
    /var/www/staging

# Verwijder backups ouder dan 30 dagen
find $BACKUP_DIR -name "*.gz" -mtime +30 -delete

echo "Backup completed: $DATE"
```

**Maak executable:**
```bash
chmod +x /usr/local/bin/havunadmin-backup.sh
```

**Test backup:**
```bash
/usr/local/bin/havunadmin-backup.sh
ls -lh /root/backups/havunadmin/
```

**Cron job (dagelijks om 3:00 AM):**
```bash
crontab -e

# Voeg toe:
0 3 * * * /usr/local/bin/havunadmin-backup.sh >> /var/log/havunadmin-backup.log 2>&1
```

---

## 📊 Security Monitoring

### Check Firewall Status
```bash
ufw status verbose
```

### Check Fail2ban Status
```bash
fail2ban-client status
fail2ban-client status sshd
```

### Check Banned IPs
```bash
fail2ban-client get sshd banip
```

### Check Open Ports
```bash
netstat -tulpn | grep LISTEN
```

### Check Failed Login Attempts
```bash
grep "Failed password" /var/log/auth.log | tail -20
```

### Check Apache Errors
```bash
tail -f /var/log/apache2/staging-error.log
```

---

## 🚨 Security Incident Response

### Als je verdachte activiteit ziet:

1. **Check logs:**
```bash
tail -100 /var/log/auth.log
tail -100 /var/log/apache2/staging-access.log
```

2. **Check actieve connecties:**
```bash
netstat -antp | grep ESTABLISHED
```

3. **Check running processes:**
```bash
ps aux | grep -v "\[" | sort -k3 -r | head -10
```

4. **Bij inbraak:**
```bash
# Disable site immediately
php artisan down

# Ban IP
fail2ban-client set sshd banip <IP_ADDRESS>

# Check damage
grep <IP_ADDRESS> /var/log/apache2/*.log
```

---

## ✅ Security Checklist Verificatie

Run deze commands om te verifiëren dat alles secure is:

```bash
# 1. Firewall check
ufw status | grep "Status: active"

# 2. Fail2ban check
systemctl is-active fail2ban

# 3. SSH config check
grep "PermitRootLogin" /etc/ssh/sshd_config
grep "PasswordAuthentication" /etc/ssh/sshd_config

# 4. .env permissions check
ls -la /var/www/staging/.env | grep "rw-------"

# 5. APP_DEBUG check
grep "APP_DEBUG=false" /var/www/staging/.env

# 6. Apache version hidden check
curl -I https://staging.admin.havun.nl | grep -i "server:"

# 7. Database user check
mysql -u havunadmin_app -p -e "SELECT current_user();"

# 8. Open ports check
ss -tulpn | grep LISTEN
```

**Verwachte output:**
- ✅ UFW active
- ✅ Fail2ban running
- ✅ PermitRootLogin prohibit-password
- ✅ PasswordAuthentication no
- ✅ .env heeft 600 permissions
- ✅ APP_DEBUG=false
- ✅ Server header toont alleen "Apache" (geen versie)
- ✅ Alleen poorten 22, 80, 443 open

---

## 📚 Bronnen & Meer Info

- [Ubuntu Server Security Guide](https://ubuntu.com/security/certifications/docs/usg)
- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [Fail2ban Documentation](https://www.fail2ban.org/)

---

**Laatst bijgewerkt**: 28 Oktober 2025
**Status**: ACTIE VEREIST - Level 1 kritieke fixes moeten DIRECT uitgevoerd worden!
