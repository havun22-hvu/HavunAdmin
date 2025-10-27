# SSH Instructions for Claude AI (havunadmin)

## 🤖 Essential Context

You are working in the **havunadmin** environment and have SSH access to the Hetzner VPS server.

**Server:** 188.245.159.115
**User:** root (only user that works)
**Key:** Already configured in `~/.ssh/id_ed25519`
**Webserver:** Apache2 (not Nginx!)

---

## 🚀 Quick Commands

### Connect to server:
```bash
ssh root@188.245.159.115
```

### Run remote command:
```bash
ssh root@188.245.159.115 -t "command here"
```

---

## 📁 Server Paths

```
/var/www/staging/       → Staging environment (active)
/var/www/production/    → Production (future)
```

---

## 🎯 Deployment Workflow

**When deploying to staging:**

1. **Local:** Git commit + push
2. **Remote:** Pull + restart services

```bash
# Complete staging deployment (copy-paste ready):
ssh root@188.245.159.115 -t "cd /var/www/staging && git pull origin main && php artisan config:clear && php artisan cache:clear && php artisan view:clear && chown -R www-data:www-data storage bootstrap/cache && systemctl restart php8.2-fpm"
```

---

## ⚠️ Critical Rules

**ALWAYS:**
- Use `root@188.245.159.115` (only this user works)
- Restart `php8.2-fpm` after code deployment
- Use `git pull` for deployment (NEVER scp/rsync)
- Use full paths on server (`/var/www/staging`)

**NEVER:**
- Run `migrate:fresh` without user permission
- Use `sudo` (you're already root)
- Modify `/root/.ssh/authorized_keys`

---

## 🔧 Common Tasks

### Restart services:
```bash
ssh root@188.245.159.115 -t "systemctl restart php8.2-fpm"
ssh root@188.245.159.115 -t "systemctl restart apache2"
```

### Check logs:
```bash
ssh root@188.245.159.115 -t "tail -n 50 /var/www/staging/storage/logs/laravel.log"
```

### Run migrations:
```bash
ssh root@188.245.159.115 -t "cd /var/www/staging && php artisan migrate --force"
```

### Check git status:
```bash
ssh root@188.245.159.115 -t "cd /var/www/staging && git log -1 --oneline"
```

---

## 📋 Reference

Full documentation: `docs/4-DEPLOYMENT/`
Project context: `CLAUDE.md` (read this first!)

**Server OS:** Ubuntu
**PHP:** 8.2
**Database:** SQLite (staging)
**Webserver:** Apache 2.4.52

---

**Status:** ✅ SSH fully configured and working
**Last updated:** 27 Oktober 2025
