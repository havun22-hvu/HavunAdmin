# HavunAdmin Deployment Guide

## Status: Ready for Production Deployment

### Wat is er gebouwd?

**Duplicate Matching Systeem** - Automatische detectie en koppeling van duplicaten van Herdenkingsportaal → Mollie → Bunq → Gmail transacties op basis van Memorial Reference (eerste 12 chars van monument UUID).

### Features:
1. **Gmail API Integratie** - OAuth2 authenticatie, email scanning, PDF downloads
2. **Transaction Matching Service** - Intelligente duplicate detectie
3. **Reconciliation Dashboard** - UI om gekoppelde duplicaten te bekijken/beheren
4. **Database Structuur** - Memorial reference tracking en parent-child linking

---

## Deployment Stappen

### 1. Code naar Productie Pushen

```bash
# Commit alle wijzigingen
cd d:/GitHub/HavunAdmin
git add .
git commit -m "Add duplicate matching system with memorial reference tracking

Features:
- Gmail API integration with OAuth2
- Transaction matching service for duplicate detection
- Memorial reference extraction (12-char UUID)
- Reconciliation dashboard
- Master-duplicate linking system
- Migrations for new fields: memorial_reference, parent_invoice_id, is_duplicate

Ready for production deployment."

git push origin main
```

### 2. SSH naar Hetzner Server

```bash
ssh jouw-gebruiker@jouw-server-ip
cd /path/to/havunadmin
```

### 3. Pull Nieuwe Code

```bash
git pull origin main
```

### 4. Installeer Dependencies

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 5. Update Environment Variables

```bash
nano .env
```

Voeg toe:
```env
# Gmail API (zelfde als local voor nu)
GMAIL_CLIENT_ID=361603632927-rj4pkh18eq8un4vgq9l5ckk70murd1v6.apps.googleusercontent.com
GMAIL_CLIENT_SECRET=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
GMAIL_REDIRECT_URI=https://jouw-productie-domain.nl/gmail/callback

# Zorg dat deze staan
APP_ENV=production
APP_DEBUG=false
APP_URL=https://jouw-productie-domain.nl
```

### 6. Run Migrations

```bash
php artisan migrate
```

Dit voegt toe aan `invoices` tabel:
- `memorial_reference` (VARCHAR 12, indexed)
- `parent_invoice_id` (foreign key)
- `is_duplicate` (boolean)
- `match_confidence` (integer)
- `match_notes` (text)

### 7. Cache Optimalisatie

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 8. Permissions

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 9. Restart Services

```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

---

## Google Cloud Console Update

**BELANGRIJK**: Update de redirect URI in Google Cloud Console:

1. Ga naar https://console.cloud.google.com/
2. Selecteer project "Havun Admin"
3. APIs & Services → Credentials
4. OAuth 2.0 Client IDs → "Havun Admin Gmail Integration"
5. **Authorized redirect URIs** → Voeg toe:
   ```
   https://jouw-productie-domain.nl/gmail/callback
   ```

---

## Eerste Gebruik na Deployment

### 1. Gmail Koppelen
1. Log in op productie: `https://jouw-productie-domain.nl`
2. Ga naar **Sync** pagina
3. Klik "Gmail Koppelen"
4. Autoriseer de applicatie (gebruik havun22@gmail.com account)

### 2. Test Gmail Scan
1. Klik "Scan Inkomsten"
2. Controleer of emails worden geïmporteerd
3. Check **Reconciliatie** pagina voor duplicate groups

### 3. Herdenkingsportaal Koppeling (Toekomstig)
Zodra Herdenkingsportaal facturen maakt met memorial_reference in Mollie metadata:
- Mollie emails komen binnen met die reference
- Gmail import extraheert de reference automatisch
- Duplicates worden automatisch gelinkt

---

## Hoe het Duplicate Matching Werkt

### Scenario: Monument Verkoop

1. **Herdenkingsportaal** maakt factuur voor monument
   - Memorial UUID: `1a2b3c4d-5e6f-...`
   - Memorial Reference (eerste 12 chars): `1a2b3c4d5e6f`
   - Stuurt naar Mollie met deze reference in metadata

2. **Mollie Payment** wordt aangemaakt
   - Email bevestiging wordt verstuurd met `1a2b3c4d5e6f` in tekst

3. **Gmail Sync** importeert email
   - Extraheert `1a2b3c4d5e6f` uit email body
   - Slaat op als `memorial_reference`
   - Checkt of er al invoices zijn met deze reference
   - **Geen match** → Dit wordt de master invoice

4. **Mollie Sync** (nog te bouwen)
   - Haalt betaling op via API
   - Extraheert `1a2b3c4d5e6f` uit metadata
   - **Match gevonden** → Wordt gelinkt als duplicate aan Gmail invoice

5. **Bunq Sync** (nog te bouwen)
   - Bankrekening transactie komt binnen
   - Beschrijving bevat `1a2b3c4d5e6f`
   - **Match gevonden** → Wordt gelinkt als duplicate

### Resultaat in Reconciliatie Dashboard

```
Memorial Ref: 1a2b3c4d5e6f (3 transacties)

  [MASTER] BUNQ-TRX-54321
   └─ Bunq | €35,00 | Echte bankrekening ontvangst

  [DUPLICATE] HERD-2025-0042
   └─ Herdenkingsportaal | €35,00 | Bewijsstuk/factuur | 100% match

  [DUPLICATE] GMAIL-19a1c2a0ad
   └─ Gmail | €35,00 | Email backup | 100% match
```

**Simpel**: Bunq is de master (het geld dat echt binnenkomt), Herdenkingsportaal is het bewijsstuk, Gmail is backup.

### Master Priority (Vereenvoudigd)

Als meerdere invoices worden geïmporteerd voordat matching plaatsvindt, wordt de master bepaald op basis van:

1. **Bunq** (hoogste priority - echte geldstroom, de waarheid)
2. **Herdenkingsportaal** (officiële factuur, bewijsstuk voor Bunq ontvangst)
3. **Gmail** (email backup, alleen als Bunq/Herdenkingsportaal ontbreekt)
4. **Mollie** (tussenstap, alleen voor debugging)
5. **Manual** (handmatig ingevoerd)

**Logica**: Bunq is leidend want dat is het geld dat echt binnenkomt. De Herdenkingsportaal factuur is het bewijsstuk voor die Bunq ontvangst. Mollie is alleen de payment processor (tussenstap) - klant betaalt via Mollie, geld komt binnen op Bunq.

---

## Testing Checklist

### Local Testing (VOOR deployment)
- [x] Gmail OAuth werkt
- [x] Gmail email scanning werkt
- [x] PDF downloads werken
- [x] Memorial reference extractie werkt
- [x] Duplicate matching werkt
- [x] Reconciliation dashboard toont correct
- [ ] Test met echte Mollie email (test memorial reference extractie)

### Production Testing (NA deployment)
- [ ] Gmail OAuth werkt op productie
- [ ] Gmail scan vindt emails
- [ ] PDFs worden gedownload naar storage
- [ ] Reconciliation dashboard is bereikbaar
- [ ] Database migrations succesvol
- [ ] Geen errors in logs

---

## Volgende Stappen (Post-Deployment)

### 1. Mollie Sync Implementeren
- Haal payments op via Mollie API
- Extraheer memorial_reference uit metadata
- Auto-match met bestaande invoices

### 2. Bunq Sync Implementeren
- Haal transacties op via Bunq API
- Extraheer memorial_reference uit beschrijving
- Auto-match met bestaande invoices

### 3. Herdenkingsportaal Aanpassen
- Bij factuur aanmaken: neem `memorial_reference` (eerste 12 chars UUID) mee
- Stuur naar Mollie in metadata: `metadata: { memorial_reference: '1a2b3c4d5e6f' }`
- Zo kan HavunAdmin alles automatisch koppelen

### 4. Webhooks (Optioneel)
- Mollie webhook voor real-time payment updates
- Bunq webhook voor real-time transaction updates

---

## Database Backup VOOR Deployment

```bash
# Op productie server
php artisan backup:run
# Of handmatig
mysqldump -u user -p database_name > backup_$(date +%Y%m%d_%H%M%S).sql
```

---

## Rollback Plan (als er problemen zijn)

```bash
# Rollback migrations
php artisan migrate:rollback

# Rollback code
git reset --hard HEAD~1

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Support & Logs

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

### Common Issues

1. **Gmail OAuth fails**
   - Check redirect URI in Google Console
   - Check .env GMAIL_REDIRECT_URI matches
   - Check HTTPS is working

2. **Migrations fail**
   - Check database connection
   - Check if columns already exist: `php artisan migrate:status`
   - Manual rollback if needed

3. **Memorial reference not extracted**
   - Check logs for extraction attempts
   - Verify email body contains 12-char alphanumeric string
   - Test pattern matching in tinker

---

## Contact

Voor vragen over deployment:
- Henk van Unen - henk@havun.nl
- Check logs eerst: `storage/logs/laravel.log`
- Check sync history in UI: `/sync`
