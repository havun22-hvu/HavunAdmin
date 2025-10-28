# HavunAdmin - Stappenplan Morgen (29 Oktober 2025)

> **Doel**: Server beveiligen + Mollie sync werkend krijgen + Applicatie testen
> **Geschatte tijd**: 2-3 uur
> **Prioriteit**: SECURITY FIRST!

---

## 🔥 PRIORITEIT 1: SECURITY (60 minuten)

### ⚠️ KRITIEK - Doe dit EERST!

**Waarom**: Server is nu onbeveiligd, kan gehackt worden!

#### Stap 1: Firewall Setup (10 min)
```bash
ssh root@46.224.31.30

# UFW Firewall installeren en configureren
apt update
apt install ufw -y
ufw default deny incoming
ufw default allow outgoing
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw --force enable
ufw status verbose
```

**✅ Verwacht resultaat**: Alleen poorten 22, 80, 443 open

---

#### Stap 2: Fail2ban (10 min)
```bash
# Installeer fail2ban
apt install fail2ban -y

# Kopieer config
cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local

# Start fail2ban
systemctl enable fail2ban
systemctl start fail2ban
systemctl status fail2ban

# Verificatie
fail2ban-client status
```

**✅ Verwacht resultaat**: Fail2ban draait, SSH jail actief

---

#### Stap 3: .env Security (15 min)
```bash
cd /var/www/staging

# BACKUP MAKEN EERST!
cp .env .env.backup

# Edit .env
nano .env
```

**Wijzigingen in .env:**
```env
# 1. Debug mode UIT
APP_DEBUG=false
APP_ENV=staging

# 2. Mollie key toevoegen
MOLLIE_KEY=live_aKqTeJbFeuzARSeapNE3A2Tc8B2V3S

# 3. App key checken (moet ingevuld zijn)
APP_KEY=base64:...
```

**Permissions fixen:**
```bash
# .env MOET 600 zijn (alleen root kan lezen)
chmod 600 .env
ls -la .env

# Verify
cat .env | grep "APP_DEBUG"
cat .env | grep "MOLLIE_KEY"

# Clear caches
php artisan config:clear
php artisan cache:clear
```

**✅ Verwacht resultaat**:
- APP_DEBUG=false
- MOLLIE_KEY ingevuld
- .env heeft 600 permissions

---

#### Stap 4: Apache Security (10 min)
```bash
# Enable headers module
a2enmod headers

# Edit security config
nano /etc/apache2/conf-available/security.conf
```

**Wijzigingen:**
```apache
ServerTokens Prod
ServerSignature Off
```

**Reload Apache:**
```bash
systemctl reload apache2

# Test (moet geen versie tonen)
curl -I https://staging.admin.havun.nl | grep -i "server:"
```

**✅ Verwacht resultaat**: Server header toont alleen "Apache" (geen versie)

---

#### Stap 5: MySQL Dedicated User (15 min)
```bash
# Login MySQL
mysql -u root -p'7Ut0xaLzh7s^T2!DmQKR'
```

**SQL commands:**
```sql
-- Maak dedicated user
CREATE USER 'havunadmin_app'@'localhost' IDENTIFIED BY 'H4vun@dm1n#2025!Str0ng';

-- Geef alleen benodigde rechten
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, INDEX, ALTER, DROP
ON havunadmin_staging.*
TO 'havunadmin_app'@'localhost';

FLUSH PRIVILEGES;

-- Test
SHOW GRANTS FOR 'havunadmin_app'@'localhost';

EXIT;
```

**Update .env:**
```bash
nano /var/www/staging/.env

# Wijzig:
DB_USERNAME=havunadmin_app
DB_PASSWORD=H4vun@dm1n#2025!Str0ng

# Save en test
php artisan migrate:status
```

**✅ Verwacht resultaat**: App werkt nog, maar nu met dedicated MySQL user

---

### ✅ Security Verificatie (5 min)
```bash
# Run alle checks
ufw status | grep "Status: active"
systemctl is-active fail2ban
grep "APP_DEBUG=false" /var/www/staging/.env
ls -la /var/www/staging/.env | grep "rw-------"
curl -I https://staging.admin.havun.nl

# Check open ports
ss -tulpn | grep LISTEN
```

**✅ Alle checks moeten GROEN zijn!**

---

## 🔧 PRIORITEIT 2: MOLLIE SYNC TESTEN (20 min)

### Stap 6: Mollie Sync Test
```bash
cd /var/www/staging

# Test Mollie connectie
php artisan tinker
>>> $mollie = new \Mollie\Api\MollieApiClient();
>>> $mollie->setApiKey(config('services.mollie.key'));
>>> $mollie->payments->page();
>>> exit

# Run sync
php artisan sync:mollie

# Check logs
tail -f storage/logs/laravel.log
```

**✅ Verwacht resultaat**:
- Geen API key errors meer
- Payments worden opgehaald
- Facturen worden aangemaakt in database

**Check in browser:**
- Ga naar: https://staging.admin.havun.nl/sync
- Klik "Sync Mollie"
- Moet succesvol zijn

---

## 🧪 PRIORITEIT 3: APPLICATIE TESTEN (40 min)

### Stap 7: Dashboard Test (10 min)
```bash
# Open in browser
https://staging.admin.havun.nl
```

**Test items:**
- [ ] Login werkt (havun22@gmail.com / 9TD@GYB6!J@rvMkC*tmZ)
- [ ] Dashboard laadt zonder errors
- [ ] Alle 6 charts renderen:
  - Monthly Revenue (bar chart)
  - Revenue by Project (pie chart)
  - Income vs Expenses (line chart)
  - Expenses by Category (doughnut chart)
  - Monthly Profit (area chart)
  - Year-over-Year (bar chart)
- [ ] Statistieken tonen (YTD, Quarter, Month revenue/expenses)

**✅ Alles moet werken zonder SQL errors**

---

### Stap 8: CRUD Testen (15 min)

**Invoices (Inkomsten):**
- [ ] Ga naar "Facturen" → "Inkomsten"
- [ ] Klik "Nieuwe Factuur"
- [ ] Vul in:
  - Klant: "Test Klant" (nieuwe klant aanmaken)
  - Project: "Herdenkingsportaal"
  - Factuurnummer: "2025-001"
  - Datum: Vandaag
  - Bedrag: €100,00
  - BTW: €21,00 (21%)
- [ ] Opslaan
- [ ] Verifieer in lijst

**Expenses (Uitgaven):**
- [ ] Ga naar "Facturen" → "Uitgaven"
- [ ] Klik "Nieuwe Uitgave"
- [ ] Vul in:
  - Leverancier: "Hetzner" (nieuwe leverancier aanmaken)
  - Categorie: "Hosting"
  - Factuurnummer: "INV-2025-001"
  - Datum: Vandaag
  - Bedrag: €8,70
  - BTW: €1,83 (21%)
- [ ] Opslaan
- [ ] Verifieer in lijst

**✅ CRUD moet volledig werken**

---

### Stap 9: Reports Testen (10 min)

**Kwartaaloverzicht:**
- [ ] Ga naar "Rapportages"
- [ ] Selecteer "2025" en "Q4"
- [ ] Klik "Genereer Kwartaaloverzicht"
- [ ] Download CSV
- [ ] Open in Excel/LibreOffice
- [ ] Verifieer data (inkomsten, uitgaven, winst)

**Jaaroverzicht:**
- [ ] Selecteer "2025"
- [ ] Klik "Genereer Jaaroverzicht"
- [ ] Download CSV
- [ ] Verifieer data per maand

**✅ Exports moeten correcte CSV files zijn**

---

### Stap 10: Sync Dashboard Test (5 min)

- [ ] Ga naar "Sync"
- [ ] Bekijk laatste sync status
- [ ] Test "Sync Mollie" button
- [ ] Moet succesvol zijn (groene melding)
- [ ] Check of facturen toegevoegd zijn aan "Facturen" lijst

**✅ Sync moet werken zonder errors**

---

## 📝 OPTIONEEL: Als tijd over (30 min)

### Backup Script Setup
```bash
# Maak backup script
nano /usr/local/bin/havunadmin-backup.sh
```

**Script inhoud** (zie SECURITY-HARDENING.md sectie 9)

```bash
chmod +x /usr/local/bin/havunadmin-backup.sh

# Test backup
/usr/local/bin/havunadmin-backup.sh
ls -lh /root/backups/havunadmin/

# Cron job (dagelijks 3 AM)
crontab -e
# Voeg toe: 0 3 * * * /usr/local/bin/havunadmin-backup.sh >> /var/log/havunadmin-backup.log 2>&1
```

---

## 📊 Einde Dag Checklist

### Security ✅
- [ ] Firewall actief (UFW)
- [ ] Fail2ban draait
- [ ] APP_DEBUG=false
- [ ] .env permissions 600
- [ ] MySQL dedicated user
- [ ] Apache security headers
- [ ] Geen open poorten (behalve 22, 80, 443)

### Functionaliteit ✅
- [ ] Dashboard werkt (alle 6 charts)
- [ ] Login werkt
- [ ] CRUD werkt (invoices, expenses)
- [ ] Reports werken (CSV exports)
- [ ] Mollie sync werkt
- [ ] Geen errors in logs

### Documentatie ✅
- [ ] SECURITY-HARDENING.md gelezen
- [ ] Wachtwoorden veilig opgeslagen
- [ ] Backup gemaakt van .env
- [ ] Server IP genoteerd (46.224.31.30)

---

## 🚨 Als iets niet werkt

### Laravel Errors
```bash
# Check logs
tail -50 /var/www/staging/storage/logs/laravel.log

# Clear caches
cd /var/www/staging
php artisan config:clear
php artisan cache:clear
php artisan view:clear
systemctl restart php8.2-fpm
```

### Mollie Errors
```bash
# Check .env
cat /var/www/staging/.env | grep MOLLIE_KEY

# Test in tinker
php artisan tinker
>>> config('services.mollie.key')
```

### Database Errors
```bash
# Test connection
php artisan migrate:status

# Check user permissions
mysql -u havunadmin_app -p'H4vun@dm1n#2025!Str0ng' -e "SELECT current_user();"
```

---

## 📞 Hulp Nodig?

**Documentatie:**
- SECURITY-HARDENING.md (complete security guide)
- STAGING-INFO.md (server info + credentials)
- PROJECT-STATUS.md (deployment history)

**Logs:**
- Laravel: `/var/www/staging/storage/logs/laravel.log`
- Apache: `/var/log/apache2/staging-error.log`
- Auth: `/var/log/auth.log`

---

## ⏱️ Tijdsinschatting

| Taak | Tijd | Prioriteit |
|------|------|-----------|
| Security Setup | 60 min | 🔥 KRITIEK |
| Mollie Sync | 20 min | ⚡ Hoog |
| Applicatie Testen | 40 min | ⚡ Hoog |
| Backup Setup | 30 min | 💡 Optioneel |
| **TOTAAL** | **2-3 uur** | |

---

## ✨ Einde Dag Status

**Na deze stappen:**
- ✅ Server is beveiligd tegen inbrekers
- ✅ Mollie sync werkt
- ✅ Applicatie volledig getest
- ✅ Klaar voor productie deployment (volgende week)

**Volgende stap (later deze week):**
- [ ] Gmail OAuth testing
- [ ] Herdenkingsportaal remote database access
- [ ] Bunq API configuratie (3-uurs regel!)
- [ ] Production deployment plannen

---

**Succes morgen! 🚀**

**Gemaakt**: 28 Oktober 2025, 23:55
**Voor**: 29 Oktober 2025
