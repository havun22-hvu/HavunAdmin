# Havun Admin - Project Status

**Laatst bijgewerkt**: 27 oktober 2025

## Huidige Fase
🟡 **Planning & Documentatie**

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

### 🟡 In Progress

#### Documentatie (nog te maken)
- [ ] TECHNICAL-ARCHITECTURE.md - Technische architectuur
- [ ] API-INTEGRATIONS.md - Gedetailleerde API integratie specs
- [ ] DATABASE-DESIGN.md - Database schema en relaties
- [ ] SECURITY.md - Security en privacy maatregelen
- [ ] IMPLEMENTATION-PLAN.md - Stapsgewijs implementatieplan
- [ ] DEPLOYMENT.md - Deployment strategie

### ⏳ Gepland

#### API Configuratie (nog te doen)
- [ ] Bunq API - ⚠️ Wachten tot app live is (3-uurs regel)
  - Sandbox key voor development
  - Production key zodra app deployed is
  - Account ID ophalen via API

#### Development
- [ ] Laravel project initialiseren
- [ ] Database migrations maken
- [ ] Models en relaties opzetten
- [ ] API integraties implementeren
- [ ] Dashboard bouwen
- [ ] Export functionaliteit

#### Deployment
- [ ] Hetzner server configureren
- [ ] Domain: admin.havun.nl
- [ ] SSL certificaat
- [ ] Database setup (MySQL)
- [ ] Cron jobs voor automatische sync

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
- [ ] Rechtsvorm (Eenmanszaak/BV/VOF?)
- [ ] Vestigingsadres
- [ ] Postadres (als anders)
- [ ] Telefoonnummer
- [ ] Website URL (als die er is)

### Belastingen
- [ ] Doe je zelf administratie of via accountant?
- [ ] Wanneer loopt boekjaar? (meestal 1 jan - 31 dec)
- [ ] Omzetbelasting aangifte per kwartaal of jaar?
- [ ] Heb je al voorlopige aanslag?

### Kosten
- [ ] Hetzner hosting kosten per maand?
- [ ] Bunq abonnement kosten?
- [ ] Domain registratie kosten?
- [ ] Welke software/tools met licenties?
- [ ] Andere vaste maandelijkse kosten?

### Projecten
- [ ] Korte beschrijving IDSee?
- [ ] Korte beschrijving Judotoernooi?
- [ ] Wanneer gaan ze ongeveer live?
- [ ] Via welke payment provider?

### Technical
- [ ] Welk domein voor HavunAdmin? (admin.havun.nl?)
- [ ] Zelfde Hetzner server als Herdenkingsportaal?
- [ ] Aparte database of gedeelde MySQL instance?
- [ ] SSL certificaat via Let's Encrypt?

## Volgende Stappen

### 1. Documentatie Afronden (1-2 dagen)
- [ ] TECHNICAL-ARCHITECTURE.md schrijven
- [ ] DATABASE-DESIGN.md maken
- [ ] API-INTEGRATIONS.md uitwerken
- [ ] SECURITY.md opstellen
- [ ] IMPLEMENTATION-PLAN.md maken

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

### 5. Advanced Features (1-2 weken)
- [ ] Kwartaal/jaar rapportages
- [ ] PDF exports
- [ ] Grafieken
- [ ] Automatische categorisering

### 6. Deployment (1-2 dagen)
- [ ] Hetzner configuratie
- [ ] Domain setup
- [ ] SSL certificaat
- [ ] Database migratie
- [ ] Cron jobs
- [ ] Bunq API key configureren (binnen 3 uur!)

## Tijdsschatting

**Totaal**: 4-6 weken

- Planning & Documentatie: ✅ 90% compleet
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

## Contact & Support

- **Email**: havun22@gmail.com
- **Documentatie**: Alle MD bestanden in root
- **API Credentials**: Zie BUSINESS-INFO.md (veilig bewaren!)
