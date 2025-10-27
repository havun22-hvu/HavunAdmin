# 🚨 DEPLOYMENT PROTOCOL - KRITIEKE REGELS

> **VERPLICHT TE LEZEN VOOR ELKE DEPLOYMENT**

---

## ⛔ ABSOLUTE VERBODEN

### **NOOIT SCP/RSYNC GEBRUIKEN VOOR CODE DEPLOYMENT**

```bash
# ❌ FOUT - NOOIT DOEN!
scp -r app/Services root@server:/var/www/staging/app/
scp MemorialController.php root@server:/var/www/staging/app/Http/Controllers/
rsync -avz resources/ root@server:/var/www/staging/resources/

# ✅ CORRECT
ssh root@server
cd /var/www/staging
git pull origin develop
```

**Waarom SCP/RSYNC verboden is:**

1. ❌ **Incomplete State** - Server draait mix van oude/nieuwe code
2. ❌ **Broken Dependencies** - Method A roept method B aan die nog niet geüpload is → crash
3. ❌ **No Version Control** - Geen git history, geen atomic changes
4. ❌ **Impossible Rollback** - Bij problemen geen makkelijke terug
5. ❌ **Cache Mismatch** - Laravel cached routes/views matchen niet met nieuwe code
6. ❌ **Race Conditions** - Gebruikers laden pagina's tijdens incomplete upload
7. ❌ **Permission Issues** - SCP uploaded files hebben verkeerde ownership

---

## 🚀 AANBEVOLEN: GitHub Actions CI/CD (Automated)

**NIEUW sinds 13 Oktober 2025** - Professionele automated deployment pipeline!

### **Voordelen CI/CD:**
- ✅ **VOLLEDIG AUTOMATISCH** - Push naar GitHub → auto-deploy
- ✅ **TESTS EERST** - Deploy alleen als tests slagen
- ✅ **NO HUMAN ERROR** - Geen vergeten stappen
- ✅ **CONSISTENT** - Elke deployment exact hetzelfde
- ✅ **ROLLBACK** - Automatisch bij failures
- ✅ **2 MINUTEN** - Van push tot live

### **Hoe Het Werkt:**

```bash
# Lokaal werken
git add .
git commit -m "New feature"
git push origin develop

# → GitHub Actions DOET DE REST:
# 1. Run tests
# 2. SSH naar staging
# 3. git pull
# 4. composer install
# 5. php artisan migrate
# 6. Clear caches
# 7. Restart PHP-FPM
# ✅ KLAAR in 2-3 minuten!
```

### **Setup Guide:**

📚 **Volledige instructies:** [GITHUB-ACTIONS-SETUP.md](GITHUB-ACTIONS-SETUP.md)

**Quick Start:**
1. Configureer GitHub Secrets (eenmalig)
2. Push naar `develop` → auto-deploy naar staging
3. Push naar `fresh-start` → auto-deploy naar production

### **Monitoring:**

https://github.com/havun22-hvu/Herdenkingsportaal/actions

---

## ✅ ALTERNATIEF: Manual Git Pull Deployment

### **Stap 1: Lokaal - Commit & Push**

```bash
# 1. Check status
git status

# 2. Test lokaal eerst!
php artisan test  # Of manual testing
php artisan config:clear
php artisan view:clear

# 3. Commit ALLE changes in één atomic commit
git add .
git commit -m "Feature X + bugfix Y - complete atomic change"
git push origin develop

# NOOIT selective staging doen (git add file1.php file2.php)
# ALTIJD git add . voor atomic deployments
```

### **Stap 2: Staging - Pull & Deploy**

```bash
# SSH naar staging server
ssh -i /c/Users/henkv/.ssh/id_ed25519 root@188.245.159.115

# Navigeer naar project
cd /var/www/staging

# Check current state
git status
git log -1  # Bekijk laatste commit op server

# Pull nieuwe changes (atomic!)
git pull origin develop

# Composer dependencies (indien composer.json gewijzigd)
composer install --no-dev --optimize-autoloader

# Database migrations (indien nieuwe migrations)
php artisan migrate --force

# Clear alle caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Fix permissions
chown -R www-data:www-data storage bootstrap/cache

# Restart PHP-FPM (belangrijk!)
systemctl restart php8.2-fpm

# Restart Queue workers (indien jobs draaien)
php artisan queue:restart
```

### **Stap 3: Verificatie**

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Test homepage
curl https://staging.herdenkingsportaal.nl

# Test memorial page
curl https://staging.herdenkingsportaal.nl/memorial/[uuid]

# Check PHP-FPM status
systemctl status php8.2-fpm
```

---

## 🚨 KRITIEKE REGEL: GEEN ACHTERSTALLIGE PULL REQUESTS

### **Probleem dat MOET worden voorkomen:**

**Dependabot/security PRs die blijven liggen = TECHNISCHE SCHULD**

**Wat er gebeurt:**
1. ❌ Dependabot maakt PR voor update
2. ❌ PR blijft weken/maanden open staan
3. ❌ Andere PRs worden gemerged → merge conflicts
4. ❌ Dependencies lopen MAANDEN achter
5. ❌ Security vulnerabilities blijven open
6. ❌ Op een dag: 7+ PRs, complexe merge conflicts, risico's

### **VERPLICHTE REGEL:**

**🔴 ELKE WEEK VRIJDAG: PR CLEANUP DAG**

```bash
# Wekelijkse PR review checklist:
1. Open GitHub: https://github.com/havun22-hvu/Herdenkingsportaal/pulls
2. Review ALLE open PRs (Dependabot, features, bugfixes)
3. Voor elke PR: BESLISSEN binnen 7 dagen
   - Merge ✅ (als veilig)
   - Close ❌ (als niet relevant)
   - Test 🧪 (als onzeker)
4. Geen PR mag langer dan 14 dagen open staan zonder beslissing
```

### **PR Triage Protocol:**

**Dependabot PRs (automated updates):**
```
MINOR/PATCH updates (1.2.3 → 1.2.4):
✅ Merge direct (binnen 24 uur)
→ Laag risico, security fixes, bug fixes

MAJOR updates (1.x.x → 2.0.0):
🔴 Test DAGELIJKS tot beslissing (max 3 dagen!)
→ Breaking changes, handmatig testen VERPLICHT
→ Dag 1: Checkout branch, run tests, check breaking changes
→ Dag 2: Test in lokale environment, dark mode check
→ Dag 3: BESLISSEN - Merge/Close/Schedule voor later
→ Als te complex: Close met reden + add to backlog

SECURITY updates:
🔴 PRIORITEIT - Merge binnen 24 uur
→ Geen excuses, security gaat voor
```

**Voorbeeld triage beslissingen:**
```
✅ laravel/framework 12.26 → 12.33 = MERGE (minor, bug fixes)
✅ vite 7.1.6 → 7.1.9 = MERGE (patch, veilig)
⚠️ tailwindcss 3.4 → 4.1 = TEST (major, breaking changes)
❌ package-we-dont-use → CLOSE (niet relevant)
```

### **Incident: 15 Oktober 2025 - 7 Achterstallige PRs**

**Wat er mis ging:**
- 7 Dependabot PRs bleven weken open staan
- Geen wekelijkse review gedaan
- Op één dag: chaos, merge conflicts, SSL errors, risico's
- 2+ uur werk om op te lossen

**Root Cause:**
1. Geen PR review discipline
2. Geen wekelijkse cleanup routine
3. "We doen het later" mentaliteit
4. Technische schuld stapelt op

**Gevolgen:**
- ❌ Dependencies maanden achter
- ❌ Security vulnerabilities open
- ❌ Merge conflicts tussen PRs
- ❌ Stressvolle cleanup sessie
- ❌ Risico's tijdens updates

### **OPLOSSING: Wekelijkse PR Hygiene**

**Elke vrijdag 16:00:**
```bash
# 1. Check open PRs
gh pr list --state open

# 2. Voor elke PR:
#    - Lees change log
#    - Check breaking changes
#    - Beslissen: merge/close/test

# 3. Merge veilige updates
#    - Minor/patch updates → direct merge
#    - Security updates → prioriteit

# 4. Close irrelevante PRs
#    - Add comment met reden
#    - "Closing: not using this package"

# 5. Schedule major updates
#    - Tag als "needs-testing"
#    - Plan test sessie binnen 7 dagen
```

**Automated reminder:**
```bash
# Optioneel: Voeg toe aan calendar/tasks
- Vrijdag 16:00: "GitHub PR Cleanup"
- Notification als >5 open PRs
- Notification als PR >14 dagen oud
```

### **Lessons Learned:**

1. ✅ **WEKELIJKSE PR REVIEW** - Geen uitzonderingen
2. ✅ **BESLISSEN BINNEN 3 DAGEN** - Merge, close, of test daily
3. ✅ **MINOR UPDATES DIRECT MERGEN** - Laag risico, hoge waarde (24 uur)
4. ✅ **MAJOR UPDATES DAGELIJKS TESTEN** - Test elke dag tot beslissing (max 3 dagen)
5. ✅ **SECURITY UPDATES PRIORITEIT** - Binnen 24 uur mergen
6. ✅ **GEEN TECHNISCHE SCHULD** - Opruimen voorkomt chaos

---

## 🔥 INCIDENT: 13 Oktober 2025 - Partial SCP Deployment

### **Wat er mis ging:**

**Fout Deployment Method:**
```bash
# Gebruikt op 13 oktober (FOUT!)
scp -r app/Services root@server:/var/www/staging/app/
scp MemorialController.php root@server:/var/www/staging/app/Http/Controllers/
scp -r resources/views/memorials root@server:/var/www/staging/resources/views/
```

**Gevolgen:**
- ✅ Lokaal: Alles werkt perfect
- ❌ Staging: Crashes, missing methods, broken dependencies
- ❌ Server draaide met **incomplete codebase**
- ❌ Sommige bestanden nieuw, andere oud → incompatible state

### **Root Cause:**

Git pull gaf eerder key verification errors, dus:
1. Claude dacht "SCP is sneller"
2. Selectief files uploaden leek efficiënter
3. **MAAR**: Dit creëerde partial deployment → production crash

### **Correcte Fix:**

```bash
# 1. Controleer git status op staging
ssh root@server
cd /var/www/staging
git status  # Was: "No commits yet" → geen git repo!

# 2. Initialize git repo op staging
git init
git remote add origin https://github.com/havun22-hvu/Herdenkingsportaal.git
git fetch origin develop
git checkout develop

# 3. Nu kan atomic deployment
git pull origin develop  # Hele codebase syncen!
```

### **Lessons Learned:**

1. ✅ **ALTIJD git pull gebruiken** - Atomic deployments only
2. ✅ **NOOIT selective file uploads** - Complete codebase sync
3. ✅ **Test git status eerst** - Zorg dat staging een git repo is
4. ✅ **Cache clear NA git pull** - Nooit ervoor
5. ✅ **Restart PHP-FPM** - Code changes vereisen process restart

---

## 🔧 Git Setup op Staging Server

### **Eenmalige Setup (indien nog niet gedaan):**

```bash
# SSH naar staging
ssh root@188.245.159.115
cd /var/www/staging

# Controleer of git repo bestaat
git status

# Indien "not a git repository":
git init
git remote add origin https://github.com/havun22-hvu/Herdenkingsportaal.git
git fetch origin develop
git checkout develop

# Configureer git identity (optioneel)
git config user.name "Staging Deployment"
git config user.email "deploy@herdenkingsportaal.nl"
```

### **Controleer GitHub SSH Key (indien git pull over SSH):**

```bash
# Check of SSH key werkt
ssh -T git@github.com

# Indien niet:
# 1. Genereer SSH key op server
ssh-keygen -t ed25519 -C "staging-deploy@herdenkingsportaal.nl"

# 2. Voeg public key toe aan GitHub
cat ~/.ssh/id_ed25519.pub
# Copy output → GitHub → Settings → SSH Keys → Add SSH key

# 3. Test opnieuw
ssh -T git@github.com
```

### **Alternatief: HTTPS met Personal Access Token**

```bash
# Indien SSH niet werkt, gebruik HTTPS
git remote set-url origin https://[TOKEN]@github.com/havun22-hvu/Herdenkingsportaal.git

# Of gebruik credential helper
git config credential.helper store
git pull  # Vraagt eenmalig om token
```

---

## 📋 Pre-Deployment Checklist

### **Lokaal - Voor git push:**

- [ ] Alle wijzigingen getest lokaal
- [ ] `php artisan test` succesvol (of manual testing)
- [ ] Dark mode getest (indien UI changes)
- [ ] Mobile responsive getest (indien layout changes)
- [ ] Database migrations getest (indien schema changes)
- [ ] `.env.example` geüpdatet (indien nieuwe env vars)
- [ ] CLAUDE.md geüpdatet (indien nieuwe features)
- [ ] Git commit message duidelijk en beschrijvend
- [ ] `git add .` gebruikt voor atomic commit
- [ ] `git push origin develop` succesvol

### **Staging - Voor deployment:**

- [ ] SSH toegang getest: `ssh root@188.245.159.115`
- [ ] Git status gecheckt: `cd /var/www/staging && git status`
- [ ] Backup database (indien migrations): `mysqldump -u root -p staging_db > backup.sql`
- [ ] Maintenance mode (indien grote changes): `php artisan down`

### **Staging - Na deployment:**

- [ ] `git pull origin develop` succesvol
- [ ] Composer install gedaan (indien composer.json changed)
- [ ] Migrations gerund (indien nieuwe migrations)
- [ ] Caches gecleared (config, view, route, cache)
- [ ] Permissions gefixed (storage, bootstrap/cache)
- [ ] PHP-FPM restarted: `systemctl restart php8.2-fpm`
- [ ] Queue workers restarted (indien jobs): `php artisan queue:restart`
- [ ] Maintenance mode uit: `php artisan up`
- [ ] Homepage test: `curl https://staging.herdenkingsportaal.nl`
- [ ] Laravel logs checked: `tail -f storage/logs/laravel.log`

---

## 🚨 Emergency Rollback Procedure

### **Indien deployment faalt:**

```bash
# SSH naar staging
ssh root@188.245.159.115
cd /var/www/staging

# Bekijk laatste commits
git log -5

# Rollback naar vorige commit
git reset --hard HEAD~1

# Of rollback naar specifieke commit
git reset --hard [commit-hash]

# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Restart PHP-FPM
systemctl restart php8.2-fpm

# Check status
curl https://staging.herdenkingsportaal.nl
tail -f storage/logs/laravel.log
```

### **Indien database migrations probleem:**

```bash
# Rollback laatste migration batch
php artisan migrate:rollback

# Of rollback specifieke migration
php artisan migrate:rollback --step=1

# Restore database backup
mysql -u root -p staging_db < backup.sql
```

---

## 🎯 Best Practices

### **1. Atomic Deployments**
- ✅ Één git commit = één complete feature/fix
- ✅ ALLE gerelateerde files in één commit
- ✅ Nooit partial uploads of selective staging

### **2. Testing Pipeline**
```
Lokaal test ✅ → Git commit ✅ → Git push ✅ → Staging pull ✅ → Staging test ✅
```

### **3. Cache Management**
- ✅ Clear caches NA deployment, NOOIT ervoor
- ✅ Config cache altijd eerst clearen
- ✅ View cache belangrijk na Blade changes

### **4. PHP-FPM Restart**
- ✅ ALTIJD restart na code changes
- ✅ PHP-FPM cached opcode, moet refreshen
- ✅ Check status na restart

### **5. Database Migrations**
- ✅ Test migrations lokaal eerst
- ✅ Backup database voor migrations op staging
- ✅ Check migration status: `php artisan migrate:status`

### **6. Environment Files**
- ✅ `.env` NOOIT committen
- ✅ `.env.example` WEL updaten
- ✅ Nieuwe env vars handmatig toevoegen op server

---

## 📞 Support & Escalatie

### **Indien deployment problemen:**

1. **Check Laravel Logs:**
   ```bash
   tail -f /var/www/staging/storage/logs/laravel.log
   ```

2. **Check PHP-FPM Logs:**
   ```bash
   tail -f /var/log/php8.2-fpm.log
   ```

3. **Check Nginx Logs:**
   ```bash
   tail -f /var/log/nginx/error.log
   ```

4. **Emergency Rollback:**
   - Gebruik procedure hierboven
   - Notify team immediately

---

## 🔒 Security Notes

- ✅ SSH key authentication only (no passwords)
- ✅ Git over HTTPS met Personal Access Token
- ✅ `.env` files NEVER in git
- ✅ Composer `--no-dev` op staging/production
- ✅ PHP-FPM runs as `www-data` (not root)

---

---

## 📅 WEKELIJKSE MAINTENANCE CHECKLIST

### **Elke vrijdag voor 17:00 (VERPLICHT):**

```bash
# 1️⃣ PR CLEANUP (5-10 minuten)
□ Open: https://github.com/havun22-hvu/Herdenkingsportaal/pulls
□ Review ALLE open PRs
□ Merge veilige updates (minor/patch)
□ Close irrelevante PRs met comment
□ Tag major updates voor testen (binnen 7 dagen)
□ Target: 0 open Dependabot PRs

# 2️⃣ DEPENDENCY CHECK (2 minuten)
□ Run: composer outdated
□ Run: npm outdated
□ Noteer major updates voor planning

# 3️⃣ SECURITY CHECK (2 minuten)
□ Check: https://github.com/havun22-hvu/Herdenkingsportaal/security
□ Review security advisories
□ Merge security updates DIRECT (priority!)

# 4️⃣ GITHUB ACTIONS CHECK (1 minuut)
□ Check: https://github.com/havun22-hvu/Herdenkingsportaal/actions
□ Verify laatste deployment geslaagd
□ Fix failures binnen 24 uur

# 5️⃣ BACKUP VERIFICATIE (1 minuut)
□ Check laatste git tags: git tag -l "backup-*"
□ Verify database backups bestaan
□ Test rollback procedure (1x per maand)
```

### **Consequenties van NIET doen:**
- ❌ Technische schuld stapelt op
- ❌ Security vulnerabilities blijven open
- ❌ Chaos bij volgende update sessie
- ❌ Merge conflicts en risico's
- ❌ Stress en extra werk

### **Voordelen van WEL doen:**
- ✅ Dependencies altijd up-to-date
- ✅ Security risks geminimaliseerd
- ✅ Geen verrassingen bij deployments
- ✅ Professionele codebase hygiene
- ✅ 10 minuten per week = uren bespaard later

---

**📅 Laatste Update:** 15 Oktober 2025 (na PR cleanup incident)
**✍️ Auteur:** Post-incident analysis
**🎯 Status:** KRITIEK - VERPLICHT LEZEN

**🚨 ONTHOUD:
1. NOOIT SCP/RSYNC VOOR CODE DEPLOYMENT - ALTIJD GIT PULL!
2. ELKE VRIJDAG: PR CLEANUP - GEEN ACHTERSTALLIGE UPDATES!**
