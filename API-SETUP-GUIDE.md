# API Setup Guide - Havun Admin

Dit document beschrijft hoe je toegang krijgt tot alle benodigde API's voor het Havun Admin systeem.

## Gmail API Setup

### Stap 1: Google Cloud Project aanmaken

1. Ga naar [Google Cloud Console](https://console.cloud.google.com/)
2. Log in met je havun22@gmail.com account
3. Klik rechtsboven op "Select a project" → "New Project"
4. Geef het project een naam: "Havun Admin"
5. Klik "Create"

### Stap 2: Gmail API activeren

1. In het menu (☰) ga naar: **APIs & Services** → **Library**
2. Zoek naar "Gmail API"
3. Klik op "Gmail API"
4. Klik op "Enable"

### Stap 3: OAuth Credentials aanmaken

1. Ga naar: **APIs & Services** → **Credentials**
2. Klik bovenaan op "+ CREATE CREDENTIALS"
3. Kies "OAuth client ID"
4. Als je nog geen OAuth consent screen hebt:
   - Klik "CONFIGURE CONSENT SCREEN"
   - Kies "External" (tenzij je Google Workspace hebt)
   - Vul in:
     - App name: "Havun Admin"
     - User support email: havun22@gmail.com
     - Developer contact: havun22@gmail.com
   - Klik "Save and Continue"
   - Bij "Scopes" → "Add or Remove Scopes":
     - Zoek en selecteer: `https://www.googleapis.com/auth/gmail.readonly`
     - Zoek en selecteer: `https://www.googleapis.com/auth/gmail.modify` (voor labels)
   - Klik "Update" en "Save and Continue"
   - Bij "Test users" → "Add Users":
     - Voeg havun22@gmail.com toe
   - Klik "Save and Continue"

5. Terug bij "Create OAuth client ID":
   - Application type: **Web application**
   - Name: "Havun Admin Web"
   - Authorized redirect URIs:
     - Voor local: `http://localhost:8000/api/gmail/callback`
     - Voor staging: `https://staging.havunadmin.nl/api/gmail/callback` (of jouw domein)
     - Voor production: `https://havunadmin.nl/api/gmail/callback` (of jouw domein)
   - Klik "Create"

6. Download de JSON file (Client ID en Client Secret)
   - Bewaar deze veilig!
   - Deze komt in je `.env` file

### Stap 4: Gmail API in Laravel

✅ **VOLTOOID** - Credentials zijn al aangemaakt!

In je `.env` bestand:

**.env.local** (development)
```env
GMAIL_CLIENT_ID=361603632927-rj4pkh18eq8un4vgq9l5ckk70murd1v6.apps.googleusercontent.com
GMAIL_CLIENT_SECRET=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
GMAIL_REDIRECT_URI=http://localhost:8000/api/gmail/callback
```

**.env.production**
```env
GMAIL_CLIENT_ID=361603632927-rj4pkh18eq8un4vgq9l5ckk70murd1v6.apps.googleusercontent.com
GMAIL_CLIENT_SECRET=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
GMAIL_REDIRECT_URI=https://admin.havun.nl/api/gmail/callback
```

### Stap 5: Eerste keer autoriseren

1. In de app klik je op "Connect Gmail"
2. Je wordt doorgestuurd naar Google
3. Log in met havun22@gmail.com
4. Geef toestemming (accept)
5. Je wordt teruggestuurd naar de app
6. Refresh token wordt opgeslagen in database

### Wat kan de Gmail API?

- ✅ Emails lezen (read-only)
- ✅ Bijlagen downloaden (PDF facturen)
- ✅ Zoeken op labels, afzender, datum
- ✅ Labels toevoegen (bijv. "Verwerkt door Havun Admin")
- ❌ Emails versturen (hebben we niet nodig, maar kan wel als je wilt)

### Kosten
- **GRATIS** voor normale gebruik
- Quota: 1 miljard requests per dag (meer dan genoeg!)

---

## Mollie API Setup

### Stap 1: Mollie Account

1. Log in op [Mollie Dashboard](https://www.mollie.com/dashboard)
2. Ga naar **Developers** → **API keys**

### Stap 2: API Keys ophalen

Je ziet twee sets van keys:
- **Test mode** (voor development)
- **Live mode** (voor production)

#### Test API Key
- Gebruik dit voor local en staging
- Begint met `test_...`
- Kan je vrij gebruiken zonder echte betalingen

#### Live API Key
- Gebruik dit ALLEEN voor production
- Begint met `live_...`
- Echte betalingen!

### Stap 3: Mollie in Laravel

✅ **VOLTOOID** - API keys zijn beschikbaar!

In je `.env` bestand:

**.env.local** (development - gebruik staging key voor testen)
```env
MOLLIE_KEY=live_aKqTeJbFeuzARSeapNE3A2Tc8B2V3S
# Let op: dit is de staging key van Herdenkingsportaal, voor testdoeleinden
```

**.env.staging**
```env
MOLLIE_KEY=live_aKqTeJbFeuzARSeapNE3A2Tc8B2V3S
# Staging key van www.staging.herdenkingsportaal.nl
```

**.env.production**
```env
MOLLIE_KEY=live_DmyesxzcAqkVp4RMx5DutBSd5KyhV7
# Production key van www.herdenkingsportaal.nl
```

**⚠️ Belangrijk:**
- Beide keys zijn "live" keys maar worden voor verschillende omgevingen gebruikt
- De staging key is voor testdoeleinden maar doet nog steeds echte betalingen
- Wees voorzichtig bij testen!

### Wat kan de Mollie API?

Voor Havun Admin hebben we nodig:
- ✅ Lijst van alle betalingen ophalen
- ✅ Details van een betaling
- ✅ Status van betaling (paid/pending/failed)
- ✅ Bedrag, datum, description
- ✅ Metadata (om facturen te koppelen)

### Laravel Package

We gebruiken: `mollie/laravel-mollie`
```bash
composer require mollie/larlie-mollie
```

### Kosten
- **GRATIS** om API te gebruiken
- Je betaalt alleen transactiekosten per betaling:
  - iDEAL: €0,29 per transactie
  - Credit card: 1,8% + €0,25 per transactie
  - Etc.

---

## Bunq API Setup

### Stap 1: Bunq Account

1. Log in op [Bunq Web](https://www.bunq.com/)
2. Ga naar **Profile** → **Security & Settings** → **Developers**

### Stap 2: API Key aanmaken

⚠️ **NOG NIET UITGEVOERD** - Wacht tot de applicatie live staat!

**Waarom?** Bunq API keys moeten binnen 3 uur na aanmaken gekoppeld worden aan de applicatie, anders verlopen ze.

**Plan:**
1. Bouw eerst de HavunAdmin applicatie
2. Deploy naar production (admin.havun.nl)
3. DAN pas Bunq API key aanmaken
4. Binnen 3 uur configureren in de app

**Wanneer je het doet:**
1. Klik op "Show API Keys"
2. Klik op "Add API Key"
3. Geef een naam: "Havun Admin"
4. Kopieer de API key (begint met een lange random string)
5. **LET OP**: Je ziet deze maar 1 keer! Bewaar hem goed.
6. Configureer DIRECT in de app (binnen 3 uur!)

### Twee modes bij Bunq:

#### Sandbox (Test)
- Voor development
- Nep geld, nep transacties
- Gratis te gebruiken
- API key: via [Bunq Developer Portal](https://developer.bunq.com/)

#### Production
- Echte bankrekening
- Echte transacties
- API key: via Bunq app of web

### Stap 3: Bunq in Laravel

⏳ **WORDT LATER INGEVULD** - Zodra API key is aangemaakt

In je `.env` bestand:

**Local/Staging (Sandbox):**
```env
BUNQ_API_KEY=sandbox_...
BUNQ_ENVIRONMENT=sandbox
# Voor development met nep data
```

**Production:**
```env
BUNQ_API_KEY=[WORDT NOG AANGEMAAKT]
BUNQ_ENVIRONMENT=production
BUNQ_ACCOUNT_ID=[NOG TE BEPALEN]
BUNQ_IBAN=NL75BUNQ2167592531
# Echte zakelijke rekening van Havun
```

### Account ID vinden

Je hebt je account ID nodig:
1. Via API call: `GET /user/{userid}/monetary-account`
2. Of via Bunq dashboard: staat in account details

### Wat kan de Bunq API?

Voor Havun Admin hebben we nodig:
- ✅ Alle transacties ophalen (payments)
- ✅ Filtering op datum
- ✅ Inkomende vs uitgaande betalingen
- ✅ Counterparty info (naam, IBAN van betaler/ontvanger)
- ✅ Description
- ✅ Amount

### Laravel Package

We gebruiken: `bunq/sdk_php`
```bash
composer require bunq/sdk_php
```

### Kosten

**API gebruik zelf:**
- ❌ **NIET gratis** voor zakelijke accounts
- Bunq Business plan vereist (€9,99/maand of hoger)
- API toegang zit inbegrepen in Business plan

**Let op:**
- Easy Money plan (gratis): GEEN API toegang
- Business plan: WEL API toegang

Controleer of je zakelijke account Business plan heeft!

### Security

Bunq API is heel veilig maar ook streng:
- Elke device moet geregistreerd worden
- IP whitelisting mogelijk
- 2FA bij eerste setup
- Alle calls zijn SSL encrypted

---

## API Security Best Practices

### Environment Variables

**NOOIT** API keys in code zetten!
Altijd in `.env` file:

**.env.local** (development)
```env
MOLLIE_KEY=test_...
BUNQ_API_KEY=sandbox_...
GMAIL_CLIENT_ID=...
```

**.env.staging** (staging server)
```env
MOLLIE_KEY=test_...
BUNQ_API_KEY=sandbox_...
GMAIL_CLIENT_ID=...
```

**.env.production** (production server)
```env
MOLLIE_KEY=live_...
BUNQ_API_KEY=production_...
GMAIL_CLIENT_ID=...
```

### .gitignore

Zorg dat `.env` files NOOIT in Git komen:
```
.env
.env.*
!.env.example
```

### API Rate Limits

Let op rate limits:
- **Gmail API**: 250 requests per user per second (ruim voldoende)
- **Mollie API**: Geen harde limit, maar gebruik common sense
- **Bunq API**: ~5000 requests per 3 uur (voor gratis tier, meer met Business)

---

## Testing API Connections

Zodra je API keys hebt, maak ik test scripts om te controleren of alles werkt:

### Gmail Test
```bash
php artisan gmail:test
```
Output: Laatste 10 emails met "factuur" in subject

### Mollie Test
```bash
php artisan mollie:test
```
Output: Laatste 10 betalingen

### Bunq Test
```bash
php artisan bunq:test
```
Output: Laatste 10 transacties van zakelijke rekening

---

## Troubleshooting

### Gmail API niet werkend?
- Check of Gmail API enabled is in Google Cloud Console
- Check of OAuth consent screen geconfigureerd is
- Check of havun22@gmail.com in test users staat
- Check redirect URI exact hetzelfde is

### Mollie API error?
- Check of je juiste key gebruikt (test vs live)
- Check of key niet expired is
- Check internet connectie

### Bunq API error?
- Check of je Business plan hebt (API toegang)
- Check of device geregistreerd is
- Check of API key niet expired is
- Check environment (sandbox vs production)

---

## Volgende Stappen

1. ☐ Gmail API setup doorlopen
2. ☐ Mollie API key ophalen
3. ☐ Bunq API key ophalen (check eerst of je Business plan hebt!)
4. ☐ Alle keys in BUSINESS-INFO.md noteren (versleuteld/veilig!)
5. ☐ Test connecties zodra Laravel app draait

---

## Handige Links

- [Gmail API Docs](https://developers.google.com/gmail/api)
- [Mollie API Docs](https://docs.mollie.com/)
- [Bunq API Docs](https://doc.bunq.com/)
- [Laravel Socialite](https://laravel.com/docs/socialite) (voor OAuth)
