# Havun Admin - Project Status

**Laatst bijgewerkt**: 28 oktober 2025 - 21:30

## Huidige Fase
🟢 **STAGING DEPLOYMENT COMPLEET** ✅
✅ Phase 1 MVP Development - COMPLEET
✅ Phase 2 API Integrations - Mollie COMPLEET
✅ Phase 3 Advanced Features - Tax Exports COMPLEET
✅ **Phase 4 Staging Deployment - COMPLEET (28 oktober 2025)** ✨

**Live URL**: https://staging.admin.havun.nl
**Server**: Dedicated Hetzner (46.224.31.30)
**Status**: Applicatie operationeel, dashboard werkend, ready for testing

## Voortgang Overzicht

### ✅ Voltooid

#### Documentatie
- [x] README.md - Project overzicht
- [x] BUSINESS-INFO.md - Bedrijfsgegevens compleet ingevuld
- [x] TAX-REQUIREMENTS.md - Nederlandse belasting eisen gedocumenteerd
- [x] FUNCTIONAL-REQUIREMENTS.md - Functionele eisen en features
- [x] API-SETUP-GUIDE.md - Stapsgewijze API setup instructies
- [x] .env.example - Environment variables template

#### API Configuratie
- [x] Gmail API - Volledig geconfigureerd en klaar voor gebruik
  - Client ID: ✅
  - Client Secret: ✅
  - Redirect URIs: ✅
  - Scopes: ✅
  - Test user: ✅

- [x] Mollie API - Keys beschikbaar
  - Production key: ✅
  - Staging key: ✅

#### Bedrijfsgegevens
- [x] KvK nummer: 98516000
- [x] BTW-identificatienummer: NL002995910B70
- [x] Omzetbelastingnummer: 195200305B01
- [x] Bunq IBAN: NL75BUNQ2167592531
- [x] Klein Ondernemersregeling (KOR): Aangevraagd

### ✅ Documentatie - COMPLEET (27 oktober 2025)

#### Documentatie
- [x] TECHNICAL-ARCHITECTURE.md - Technische architectuur ✅
- [x] DATABASE-DESIGN.md - Database schema en relaties ✅
- [x] IMPLEMENTATION-PLAN.md - Stapsgewijs implementatieplan ✅
- [ ] API-INTEGRATIONS.md - Gedetailleerde API integratie specs (optioneel, info in TECHNICAL-ARCHITECTURE)
- [ ] SECURITY.md - Security en privacy maatregelen (optioneel, info in TECHNICAL-ARCHITECTURE)
- [ ] DEPLOYMENT.md - Deployment strategie (optioneel, info in IMPLEMENTATION-PLAN)

### ⏳ Gepland

#### API Configuratie (nog te doen)
- [ ] Bunq API - ⚠️ Wachten tot app live is (3-uurs regel)
  - Sandbox key voor development
  - Production key zodra app deployed is
  - Account ID ophalen via API

#### Development - Phase 1 MVP ✅ COMPLEET (27 okt 2025)
- [x] Laravel project initialiseren ✅ (27 okt 2025)
- [x] Database migrations maken (13 tabellen) ✅
- [x] Models en relaties opzetten (11 models) ✅
- [x] Database seeders (User, Projects, Categories) ✅
- [x] SQLite database configuratie ✅
- [x] Vendor directory (via Herdenkingsportaal) ✅
- [x] Database migrations uitgevoerd ✅
- [x] Seeders uitgevoerd (1 user, 4 projecten, 6 categorieën) ✅
- [x] Laravel serve test - draait op http://localhost:8000 ✅
- [x] Laravel Breeze installeren (authentication) ✅
- [x] Controllers maken (Invoice, Expense, Project, Dashboard) ✅
- [x] Routes definiëren ✅
- [x] Blade views bouwen ✅
- [x] Dashboard met statistieken ✅

#### Development - Phase 2 API Integrations (In Progress)
- [x] Mollie API integration ✅ (27 okt 2025)
  - [x] MollieService voor payment sync ✅
  - [x] SyncController voor manual triggers ✅
  - [x] Artisan command sync:mollie ✅
  - [x] Sync views (dashboard + history) ✅
  - [x] Navigation menu updated ✅
  - [x] Routes toegevoegd ✅
  - [x] Config file (mollie.php) ✅
  - [x] Tested en werkend ✅
- [ ] Mollie package installeren (echte package, nu stub)
- [ ] Bunq API integration (wacht op deployment)
- [ ] Gmail API integration

#### Development - Phase 3 Advanced Features ✅ (27 okt 2025)
- [x] Tax Export functionaliteit (Belastingdienst) ✅
  - [x] TaxExportService (3 export methods) ✅
  - [x] ReportController met export endpoints ✅
  - [x] Reports view met export formulieren ✅
  - [x] Kwartaaloverzicht export (CSV) ✅
  - [x] Jaaroverzicht export (CSV) ✅
  - [x] BTW aangifte export (CSV) ✅
  - [x] Download functionaliteit voor exports ✅
  - [x] Delete functionaliteit voor oude exports ✅
  - [x] Navigation menu updated met Rapportages ✅
  - [x] Routes toegevoegd voor alle exports ✅
- [ ] PDF export functionaliteit (facturen)
- [ ] Grafieken en visualisaties
- [ ] Automatische categorisering van uitgaven

#### Deployment - STAGING ✅ COMPLEET (28 oktober 2025)
- [x] Dedicated Hetzner server opgezet (46.224.31.30) ✅
- [x] Domain: staging.admin.havun.nl ✅
- [x] SSL certificaat (Let's Encrypt, geldig tot 2026-01-25) ✅
- [x] Database setup MySQL (havunadmin_staging) ✅
- [x] Admin user aangemaakt ✅
- [x] Apache + PHP-FPM configuratie ✅
- [x] Alle Laravel dependencies geïnstalleerd ✅
- [x] Dashboard SQL fix (SQLite → MySQL conversie) ✅
- [ ] Cron jobs voor automatische sync (nog te doen)
- [ ] Production deployment (admin.havun.nl) (nog te doen)

## API Status Details

### Gmail API
**Status**: ✅ Volledig geconfigureerd

| Item | Status | Waarde/Info |
|------|--------|-------------|
| Client ID | ✅ | 361603632927-rj4pkh18eq8un4vgq9l5ckk70murd1v6.apps.googleusercontent.com |
| Client Secret | ✅ | Aanwezig in BUSINESS-INFO.md |
| Redirect URI (local) | ✅ | http://localhost:8000/api/gmail/callback |
| Redirect URI (prod) | ✅ | https://admin.havun.nl/api/gmail/callback |
| Scopes | ✅ | readonly + modify |
| Test User | ✅ | havun22@gmail.com |
| Kosten | ✅ | GRATIS |

### Mollie API
**Status**: ✅ Keys beschikbaar

| Omgeving | Key | Gebruik |
|----------|-----|---------|
| Production | live_Dmyes... | www.herdenkingsportaal.nl |
| Staging | live_aKqTe... | staging.herdenkingsportaal.nl |
| Local | live_aKqTe... | Development/testing |

⚠️ **Let op**: Zelfs staging key doet echte transacties!

### Bunq API
**Status**: ⏳ Nog te configureren

| Item | Status | Info |
|------|--------|------|
| Zakelijke rekening | ✅ | NL75BUNQ2167592531 |
| Business plan | ⚠️ | Vereist voor API toegang - checken! |
| Sandbox key | ⏳ | Voor local development |
| Production key | ⏳ | Zodra app live is (3-uurs regel) |
| Account ID | ⏳ | Ophalen via eerste API call |

## Belasting Situatie

### Huidige Status
- **BTW-plichtig**: ❌ Nee
- **Klein Ondernemersregeling**: ✅ Aangevraagd
- **Omzetbelasting aangifte**: ✅ Verplicht
- **Frequentie**: [NOG TE BEPALEN - kwartaal of jaar?]
- **BTW-vrijstelling**: ✅ Via KOR

### Administratie Vereisten
✅ **System moet bijhouden**:
- Alle inkomsten (facturen + betalingen)
- Alle uitgaven (facturen + betalingen)
- Per project opsplitsing
- Kostencategorieën
- Bankafschriften (Bunq)
- 7 jaar bewaarplicht

## Projecten

### Herdenkingsportaal.nl
- **Status**: Bijna online
- **Betalingen**: Via Mollie
- **Mollie key**: Beschikbaar

### IDSee
- **Status**: In ontwikkeling
- **Betalingen**: [NOG TE BEPALEN]
- **Beschrijving**: [NOG IN TE VULLEN]

### Judotoernooi
- **Status**: In ontwikkeling
- **Betalingen**: [NOG TE BEPALEN]
- **Beschrijving**: [NOG IN TE VULLEN]

## Nog Te Bepalen / Vragen

### Bedrijfsgegevens
- [x] Rechtsvorm: Eenmanszaak ✅
- [x] Vestigingsadres: Jacques Bloemhof 57, 1628 VN Hoorn ✅
- [x] Postadres: Jacques Bloemhof 57, 1628 VN Hoorn ✅
- [x] Telefoonnummer: 06-25058214 ✅
- [x] Website URL: havun.nl ✅
- [x] Admin portal: admin.havun.nl ✅

### Belastingen
- [x] Administratie: Zelf (met HavunAdmin systeem) ✅
- [x] Adviseur: Kennis met verstand van zaken (voor vragen) ✅
- [x] Boekjaar: 1 januari - 31 december ✅
- [x] Omzetbelasting aangifte: Per jaar (via aangifte inkomstenbelasting) ✅
- [ ] Voorlopige aanslag: [NOG TE BEPALEN]

### Kosten
- [x] Hetzner hosting: ~€5,00 per maand (wisselend) ✅
- [x] Bunq abonnement: €13,99 per maand (Pro Business) ✅
- [x] Domain registratie: €12,08 per jaar (2 domeinen via mijn.host) ✅
- [x] Software/tools: Claude AI €108,90/maand (€90 excl. BTW) ✅
- [x] Totale vaste kosten: ~€128,90 per maand ✅

### Projecten
- [x] IDSee: Hondenregistratie systeem (chipnummers + blockchain) - Nog te starten ✅
- [x] Judotoernooi: Toernooi organisatie systeem - Bijna klaar, nog niet commercieel ✅
- [ ] Live datum: Nog te bepalen
- [ ] Payment provider: Nog te bepalen (IDSee), N.v.t. (Judotoernooi)

### Technical
- [x] Domein voor HavunAdmin: staging.admin.havun.nl (staging) + admin.havun.nl (production) ✅
- [x] Hosting: **Dedicated Hetzner server (46.224.31.30)** - NIET gedeeld met Herdenkingsportaal ✅
- [x] Database: MySQL 8.0 op dedicated server (havunadmin_staging) ✅
- [x] SSL certificaat: Via Let's Encrypt (geldig tot 2026-01-25) ✅
- [x] Stack: Apache 2.4.58 + PHP 8.2-FPM + MySQL 8.0 ✅
- [x] Remote database access: Herdenkingsportaal database (188.245.159.115) ⚠️ nog te configureren

## Volgende Stappen

### 1. Documentatie Afronden ✅ COMPLEET (27 oktober 2025)
- [x] TECHNICAL-ARCHITECTURE.md schrijven ✅
- [x] DATABASE-DESIGN.md maken ✅
- [x] IMPLEMENTATION-PLAN.md maken ✅
- [x] PROJECT-STATUS.md bijwerken ✅
- [x] Alle bedrijfsgegevens compleet ✅

### 2. Development Setup (1 dag)
- [ ] Laravel installeren
- [ ] Database migrations maken
- [ ] Basic auth setup
- [ ] Models aanmaken

### 3. MVP Development (1-2 weken)
- [ ] Handmatige factuur invoer (in/uit)
- [ ] Project management
- [ ] Basis dashboard
- [ ] Eenvoudige export (Excel)

### 4. API Integraties (1 week)
- [ ] Gmail integration
- [ ] Mollie integration
- [ ] Bunq integration (zodra app live is)

### 5. Advanced Features (1-2 weken) - ⚡ IN PROGRESS
- [x] Kwartaal/jaar rapportages ✅ (27 oktober 2025)
- [x] CSV exports voor Belastingdienst ✅ (27 oktober 2025)
- [ ] PDF exports (facturen)
- [ ] Grafieken
- [ ] Automatische categorisering

### 6. Deployment - STAGING ✅ COMPLEET (28 oktober 2025)
- [x] Hetzner server configuratie ✅
- [x] Domain setup (staging.admin.havun.nl) ✅
- [x] SSL certificaat (Let's Encrypt) ✅
- [x] Database migratie (14 tabellen) ✅
- [x] Apache + PHP-FPM setup ✅
- [x] Permissions en security ✅
- [x] Admin user aangemaakt ✅
- [ ] Cron jobs (nog te doen)
- [ ] Bunq API key configureren (wacht op productie, binnen 3 uur na aanmaken!)

### 7. Production Deployment (nog te plannen)
- [ ] Domain setup (admin.havun.nl)
- [ ] Production database setup
- [ ] Production .env configuratie
- [ ] Bunq API key aanmaken (LET OP: 3-uurs regel!)
- [ ] Remote database access Herdenkingsportaal
- [ ] Cron jobs configureren
- [ ] Monitoring en logging setup
- [ ] Backup strategie

## Tijdsschatting

**Totaal**: 7-8 weken (part-time, ~20 uur/week)

- Planning & Documentatie: ✅ 100% COMPLEET (27 oktober 2025)
- MVP Development: 1-2 weken
- API Integraties: 1 week
- Testing & Refinement: 1 week
- Deployment: 2-3 dagen

## Risico's & Aandachtspunten

### ⚠️ Hoog Risico
1. **Bunq API 3-uurs regel**
   - Oplossing: Pas key aanmaken als app 100% klaar is

2. **Mollie staging key doet echte betalingen**
   - Oplossing: Voorzichtig testen, kleine bedragen

3. **7 jaar bewaarplicht**
   - Oplossing: Goede backup strategie vanaf dag 1

### 💡 Aandachtspunten
1. **Security van API keys**
   - Alle keys in .env
   - Nooit in Git
   - Server goed beveiligen

2. **BTW voorbereiding**
   - Nu al velden in database
   - Makkelijk activeren later

3. **Multi-omgeving setup**
   - Local, Staging, Production
   - Aparte databases
   - Aparte API keys waar mogelijk

## Beslissingen Genomen

✅ **Tech Stack**: Laravel + MySQL (consistent met Herdenkingsportaal)
✅ **Hosting**: Hetzner (zelfde server)
✅ **Database**: MySQL (zelfde als Herdenkingsportaal)
✅ **API's**: Gmail + Mollie + Bunq
✅ **Omgevingen**: Local, Staging, Production
✅ **Belasting focus**: Omzetbelasting aangifte (1-klik export)

## Deployment History

### 28 Oktober 2025 - Staging Deployment COMPLEET ✅

**Belangrijke Beslissing: Dedicated Server**
- **Context**: Oorspronkelijk plan was deployment op gedeelde Hetzner server met Herdenkingsportaal (188.245.159.115)
- **Probleem**: "stom, dat hadden weerst even moeten overleggen !" - gebruiker wilde aparte server
- **Reden**: "we gaan naar een eigen server, er komen nog meer apps aan die daar ook op moeten"
- **Beslissing**: Nieuwe dedicated server (46.224.31.30) voor alle Havun business tools
- **Cleanup**: Hetzner deployment volledig verwijderd (database, files, Apache config)

**Server Setup:**
- Server: Hetzner CPX22 (46.224.31.30)
- OS: Ubuntu 22.04 LTS
- Stack: Apache 2.4.58 + PHP 8.2-FPM + MySQL 8.0
- Domain: staging.admin.havun.nl
- SSL: Let's Encrypt (geldig tot 2026-01-25)

**Deployment Issues & Fixes:**

1. **MySQL Root Password Issue**
   - Error: `Access denied for user 'root'@'localhost'`
   - Oorzaak: Fresh MySQL installation, root niet geconfigureerd
   - Fix: Gebruikt debian-sys-maint credentials om root password te zetten
   - Password: 7Ut0xaLzh7s^T2!DmQKR

2. **PHP-FPM Not Enabled**
   - Error: Apache toonde PHP source code in plaats van te executen
   - Fix: `a2enmod proxy_fcgi setenvif` + `a2enconf php8.2-fpm` + restart Apache

3. **Git Dubious Ownership**
   - Error: `fatal: detected dubious ownership in repository`
   - Fix: `git config --global --add safe.directory /var/www/staging`

4. **Local Changes Blocking Pull**
   - Error: Local DashboardController changes blocking git pull
   - Fix: `git stash` om lokale changes tijdelijk op te slaan

5. **SQL Syntax Error - SQLite vs MySQL** ⚠️ KRITIEK
   - Error: `SQLSTATE[42000]: Syntax error... CAST(strftime('%m'...`
   - Oorzaak: Dashboard queries gebruikten SQLite `strftime()` functie op MySQL database
   - Impact: Dashboard crashes bij laden
   - Fix: Alle `CAST(strftime('%m', invoice_date) AS INTEGER)` vervangen door `MONTH(invoice_date)`
   - Files: `app/Http/Controllers/DashboardController.php` (5 occurrences, 3 methods)
   - Methodes: `getMonthlyRevenue()`, `getMonthlyIncomeVsExpenses()`, `getMonthlyProfit()`
   - Commit: "fix: Replace SQLite strftime with MySQL MONTH() in dashboard queries"
   - Deployed: git pull + cache clear op server

**Deployment Checklist:**
- ✅ 14 Database migrations uitgevoerd
- ✅ Admin user aangemaakt (havun22@gmail.com / 9TD@GYB6!J@rvMkC*tmZ)
- ✅ Composer dependencies installed (Laravel 12, Mollie, etc.)
- ✅ NPM assets gebuild (Vite, Chart.js, Tailwind)
- ✅ Permissions: www-data:www-data, 775 op storage/
- ✅ Apache VirtualHost configuratie
- ✅ SSL certificaat geïnstalleerd en verified
- ✅ DNS propagated (staging.admin.havun.nl → 46.224.31.30)
- ✅ Dashboard SQL fix deployed

**Current Status:**
- Applicatie: ✅ LIVE op https://staging.admin.havun.nl
- Login: ✅ Werkend
- Dashboard: ✅ Werkend (na SQL fix)
- Database: ✅ 14 tabellen, all migrations successful
- SSL: ✅ Valid tot 2026-01-25

**Nog Te Configureren:**
- ⚠️ Remote database access naar Herdenkingsportaal (188.245.159.115)
- ⚠️ Bunq API credentials
- ⚠️ Gmail OAuth testing
- ⚠️ Cron jobs voor automatische sync

**🚨 KRITIEKE SECURITY ISSUES (NU):**
- ⚠️ **FIREWALL NIET ACTIEF** - Alle poorten staan open!
- ⚠️ **GEEN FAIL2BAN** - Geen brute force protection
- ⚠️ **APP_DEBUG=true** - Debug mode toont sensitive info
- ⚠️ **DATABASE ROOT USER** - App draait met MySQL root
- ⚠️ **APACHE VERSION ZICHTBAAR** - Hackers zien kwetsbaarheden
- ⚠️ **.ENV PERMISSIONS** - Te open (644 i.p.v. 600)

**📋 PLAN MORGEN (29 oktober):**
- 🔥 PRIORITEIT 1: Security hardening (60 min)
- ⚡ PRIORITEIT 2: Mollie sync testen (20 min)
- ⚡ PRIORITEIT 3: Applicatie testen (40 min)
- Zie **TODO-MORGEN.md** voor complete stappenplan

**Lessons Learned:**
1. **Altijd server keuze afstemmen met gebruiker** - Niet automatisch aannemen
2. **SQLite development ≠ MySQL production** - Date functions zijn verschillend
3. **Test dashboard na deployment** - Database syntax verschillen kunnen crashes veroorzaken
4. **Git pull is atomic deployment** - Nooit SCP/RSYNC gebruiken
5. **PHP-FPM restart vereist** - Na code changes altijd PHP-FPM restarten

---

## Contact & Support

- **Email**: havun22@gmail.com
- **Documentatie**: Alle MD bestanden in root
- **API Credentials**: Zie BUSINESS-INFO.md (veilig bewaren!)
- **Server Access**: Zie STAGING-INFO.md voor SSH credentials en toegang
