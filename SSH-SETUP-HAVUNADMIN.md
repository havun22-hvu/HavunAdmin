# SSH Access Documentation - For Claude AI (havunadmin context)

## 🤖 AI Assistant Context

**IMPORTANT: This documentation is for Claude AI running in havunadmin environment**

This guide explains SSH access to the Hetzner VPS server for deployment automation and server management tasks.

---

## 📋 Critical Information for AI

**Server Details:**
- **IP:** 188.245.159.115
- **Hostname:** herdenkingsportaal-prod
- **SSH User:** root (ONLY root user works!)
- **Authentication:** SSH key-based ONLY (no password)
- **SSH Key Location:** `C:\Users\[username]\.ssh\id_ed25519`
- **Key Status:** ✅ Already configured and authorized on server

**Key Facts:**
- The same SSH key works from different Windows user accounts (henkv and havunadmin)
- No new key generation needed - existing key is already on server
- Key may need to be copied to havunadmin's .ssh folder if not present

---

## 🔧 SSH Commands for AI Assistant

### Basic SSH Connection

**Primary command (use this first):**
```bash
ssh root@188.245.159.115
```

**Alternative with explicit key path:**
```bash
ssh -i ~/.ssh/id_ed25519 root@188.245.159.115
```

**If SSH config alias exists:**
```bash
ssh hetzner
```

**Running single remote command:**
```bash
ssh root@188.245.159.115 -t "command here"
```

### 🚨 CRITICAL: SSH Usage Rules for AI

**ALWAYS:**
- ✅ Use `root@188.245.159.115` as connection target
- ✅ Use `-t` flag for commands that need a terminal
- ✅ Use full paths on server (e.g., `/var/www/staging` not relative paths)
- ✅ Include `Pseudo-terminal will not be allocated` in expected output

**NEVER:**
- ❌ Try to SSH as different user (only root works!)
- ❌ Attempt password authentication (will fail)
- ❌ Run destructive commands without user confirmation
- ❌ Use `sudo` (you're already root!)

---

## 🗂️ Server Directory Structure

**Project Locations:**
```bash
/var/www/staging/              # Staging environment
/var/www/production/           # Production environment (future)
/var/www/intro/                # Landing page
```

**Important Files:**
```bash
/var/www/staging/.env          # Environment config
/var/www/staging/storage/      # Storage (must be www-data:www-data)
/var/www/staging/database/     # SQLite database
/root/.ssh/authorized_keys     # SSH keys (DO NOT MODIFY!)
```

**Web Server Config:**
```bash
/etc/apache2/sites-enabled/    # Apache vhosts
/etc/php/8.2/fpm/              # PHP-FPM config
```

---

## 🚀 Common Deployment Commands for AI

### Deploy Code (ATOMIC git pull method)

**Staging deployment:**
```bash
ssh root@188.245.159.115 -t "cd /var/www/staging && git pull origin main && php artisan config:clear && php artisan cache:clear && php artisan view:clear && chown -R www-data:www-data storage bootstrap/cache && systemctl restart php8.2-fpm"
```

**Check Laravel version:**
```bash
ssh root@188.245.159.115 -t "cd /var/www/staging && php artisan --version"
```

### Service Management

**Restart PHP-FPM (CRITICAL after code deployment):**
```bash
ssh root@188.245.159.115 -t "systemctl restart php8.2-fpm"
```

**Restart Apache:**
```bash
ssh root@188.245.159.115 -t "systemctl restart apache2"
```

**Check service status:**
```bash
ssh root@188.245.159.115 -t "systemctl status apache2 && systemctl status php8.2-fpm"
```

### Database Operations

**Run migrations:**
```bash
ssh root@188.245.159.115 -t "cd /var/www/staging && php artisan migrate --force"
```

**⚠️ NEVER run without user permission:**
```bash
# ❌ FORBIDDEN without explicit user consent:
php artisan migrate:fresh
php artisan migrate:reset
php artisan db:wipe
```

### Log Viewing

**View Laravel logs:**
```bash
ssh root@188.245.159.115 -t "tail -n 50 /var/www/staging/storage/logs/laravel.log"
```

**Follow logs in real-time:**
```bash
ssh root@188.245.159.115 -t "tail -f /var/www/staging/storage/logs/laravel.log"
```

---

## 🎯 AI Assistant Workflow: Code Deployment

**When user requests staging deployment, follow these steps EXACTLY:**

1. **Local git commit (if changes exist):**
   ```bash
   git add .
   git commit -m "Feature description"
   git push origin main
   ```

2. **Wait for git push to complete** (critical!)

3. **Deploy to staging (ATOMIC):**
   ```bash
   ssh root@188.245.159.115 -t "cd /var/www/staging && git pull origin main && composer install --no-dev --optimize-autoloader && php artisan migrate --force && php artisan config:clear && php artisan cache:clear && php artisan view:clear && php artisan route:clear && chown -R www-data:www-data storage bootstrap/cache && systemctl restart php8.2-fpm"
   ```

4. **Verify deployment:**
   ```bash
   ssh root@188.245.159.115 -t "cd /var/www/staging && git log -1 --oneline"
   ```

5. **Check for errors:**
   ```bash
   ssh root@188.245.159.115 -t "tail -n 20 /var/www/staging/storage/logs/laravel.log"
   ```

**⛔ NEVER use SCP/RSYNC for code deployment!** (See CLAUDE.md for incident details)

---

## 🚨 SSH Config (Optional Shortcut)

Maak een shortcut zodat je alleen `ssh hetzner` hoeft te typen.

### Windows:

1. **Maak/edit SSH config:**
   ```bash
   notepad ~/.ssh/config
   ```

   Of in Git Bash:
   ```bash
   nano ~/.ssh/config
   ```

2. **Voeg toe:**
   ```
   Host hetzner
     HostName 188.245.159.115
     User root
     IdentityFile ~/.ssh/id_ed25519
     PubkeyAuthentication yes
     PasswordAuthentication no
   ```

3. **Sla op en sluit**

4. **Test:**
   ```bash
   ssh hetzner
   ```

   Nu werkt de shortcut! 🎉

---

## 📁 Belangrijke Server Locaties

Na inloggen op de server:

```bash
# Staging environment
cd /var/www/staging

# Production environment
cd /var/www/production

# Check Laravel version
cd /var/www/staging && php artisan --version

# Check logs
tail -f /var/www/staging/storage/logs/laravel.log

# Restart PHP-FPM (na code updates)
systemctl restart php8.2-fpm

# Restart Apache
systemctl restart apache2

# Check server status
systemctl status apache2
systemctl status php8.2-fpm
```

---

## 🔧 Troubleshooting

### "Permission denied (publickey)"

**Oplossing 1: Check of je de juiste key gebruikt**
```bash
ssh -v root@188.245.159.115
```
Dit toont debug info. Let op regel:
```
debug1: Offering public key: /Users/jouwname/.ssh/id_ed25519
```

**Oplossing 2: Check key permissions op jouw computer**
```bash
chmod 600 ~/.ssh/id_ed25519
chmod 644 ~/.ssh/id_ed25519.pub
```

**Oplossing 3: Voeg key toe aan SSH agent**
```bash
# Start SSH agent
eval "$(ssh-agent -s)"

# Voeg key toe
ssh-add ~/.ssh/id_ed25519

# Check of key is toegevoegd
ssh-add -l
```

---

### "Host key verification failed"

Dit gebeurt als de server key is veranderd.

**Oplossing:**
```bash
ssh-keygen -R 188.245.159.115
```

Probeer opnieuw te verbinden.

---

### Windows: "ssh command not found"

**Oplossing:** Gebruik Git Bash (komt met Git for Windows) of PowerShell 7+

**Download Git Bash:**
https://git-scm.com/download/win

---

## 🛡️ Security Best Practices

### ✅ DO:
- Gebruik SSH keys (niet wachtwoorden)
- Gebruik ED25519 keys (veiligste algoritme)
- Deel NOOIT je private key (`id_ed25519`)
- Gebruik SSH config voor gemak
- Log altijd uit met `exit` na werk

### ❌ DON'T:
- Gebruik NOOIT wachtwoord authenticatie op production
- Deel NOOIT `id_ed25519` (private key)
- Deel ALLEEN `id_ed25519.pub` (public key)
- Run NOOIT destructive commands zonder backup (`rm -rf`, `migrate:fresh`, etc.)

---

## 📞 Hulp Nodig?

### Common Commands:

```bash
# Verbinden met server
ssh root@188.245.159.115

# Of met shortcut (als je SSH config hebt)
ssh hetzner

# Check welke user je bent
whoami

# Check hostname
hostname

# Check server uptime
uptime

# Exit server
exit
```

### Check Current SSH Keys on Server:

```bash
ssh hetzner -t "cat /root/.ssh/authorized_keys"
```

Dit toont alle users die toegang hebben.

---

## ✅ Checklist Setup (voor havunadmin)

### Als je op dezelfde PC werkt als henkv:
- [ ] SSH key kopiëren van henkv naar havunadmin (optioneel)
- [ ] Test connectie: `ssh root@188.245.159.115` ✅
- [ ] SSH config aanmaken (optioneel): `~/.ssh/config`
- [ ] Test shortcut: `ssh hetzner`

### Als je op andere PC werkt:
- [ ] SSH key kopiëren van henkv's PC
- [ ] Plaats in `C:\Users\[username]\.ssh\` folder
- [ ] Test connectie: `ssh root@188.245.159.115`
- [ ] SSH config aanmaken (optioneel)

---

**Laatste Update:** 27 Oktober 2025
**Status:** ✅ SSH key al geconfigureerd - alleen key kopiëren indien nodig

**Belangrijkste info:**
- Je bestaande SSH key (`id_ed25519`) is al toegevoegd aan de server
- Je hoeft GEEN nieuwe key aan te maken
- Kopieer gewoon de key naar je nieuwe locatie als je die ergens anders nodig hebt
- SSH werkt meteen zodra de key op de juiste plek staat

Voor meer info: check `docs/4-DEPLOYMENT/SERVER-ACCESS.md`
