# Functionele Eisen - Havun Admin

## Kernfunctionaliteit

### 1. Factuur Beheer

#### Inkomsten Facturen
- Handmatig facturen invoeren
- Facturen importeren uit Mollie
- Facturen importeren uit Gmail
- Velden per factuur:
  - Factuurnummer (automatisch & handmatig)
  - Datum
  - Klant (naam, bedrijf, email)
  - Bedrag (excl BTW voor later)
  - BTW bedrag (optioneel, voor later)
  - Totaalbedrag
  - Project (herdenkingsportaal/IDSee/judotoernooi)
  - Status (concept/verstuurd/betaald)
  - Betaaldatum
  - Betalingsmethode (Mollie/Bunq overschrijving/Cash)
  - Notities

#### Uitgaven Facturen
- Handmatig uitgaven invoeren
- Facturen importeren uit Gmail
- Facturen importeren uit Bunq
- Velden per uitgave:
  - Leverancier
  - Factuurnummer leverancier
  - Datum
  - Bedrag (excl BTW)
  - BTW bedrag (indien van toepassing)
  - Totaalbedrag
  - Categorie (hosting/software/betaaldiensten/etc.)
  - Project (indien project-specifiek)
  - Status (ontvangen/betaald)
  - Betaaldatum
  - Factuur PDF/bestand upload
  - Notities

### 2. Project Beheer

#### Projecten
- **Herdenkingsportaal.nl**
  - Status: Actief/Bijna online
  - Beschrijving
  - Kleur code (voor dashboard)

- **IDSee**
  - Status: In ontwikkeling
  - Beschrijving
  - Kleur code

- **Judotoernooi**
  - Status: In ontwikkeling
  - Beschrijving
  - Kleur code

- **Algemeen/Overig**
  - Voor kosten die niet project-specifiek zijn

#### Functionaliteit
- Projecten toevoegen/bewerken
- Inkomsten per project zien
- Kosten toewijzen aan projecten
- Project archiveren

### 3. API Integraties

#### Mollie Integration
- **Wat importeren**:
  - Alle betalingen (payments)
  - Automatisch koppelen aan bestaande facturen (op basis van order ID of metadata)
  - Of nieuwe factuur aanmaken

- **Synchronisatie**:
  - Handmatig: "Sync Mollie" knop
  - Automatisch: dagelijks/per uur (cron job)
  - Alleen nieuwe transacties ophalen (sinds laatste sync)

- **Data mapping**:
  - Mollie payment ID → factuur referentie
  - Amount → factuur bedrag
  - Description → factuur omschrijving
  - Created date → factuur datum
  - Status → factuur status (paid/pending)

#### Bunq Integration
- **Wat importeren**:
  - Alle transacties van zakelijke rekening
  - Zowel inkomend (inkomsten) als uitgaand (uitgaven)

- **Synchronisatie**:
  - Handmatig: "Sync Bunq" knop
  - Automatisch: dagelijks (cron job)
  - Datum range selecteren voor import

- **Data mapping**:
  - Inkomende betaling → zoek matching factuur of maak nieuwe
  - Uitgaande betaling → maak uitgave met leverancier info
  - Description → omschrijving
  - Counterparty name → klant/leverancier naam
  - Amount → bedrag

- **Categorisering**:
  - Automatische categorisering op basis van herkenning
  - Handmatig aanpassen/toewijzen
  - Leren van eerdere toewijzingen (suggesties)

#### Gmail Integration
- **Wat importeren**:
  - Emails met bijlagen (PDF facturen)
  - Van havun22@gmail.com
  - Labels: "Facturen" of specifieke filters

- **Hoe herkennen**:
  - Email van bekende leveranciers
  - Bijlage is PDF
  - Keywords: "factuur", "invoice", "nota"
  - Van: [bekende leveranciers lijst]

- **Processing**:
  - PDF downloaden en opslaan
  - OCR/text extraction (indien mogelijk)
  - Velden proberen te extraheren:
    - Factuurnummer
    - Datum
    - Bedrag
    - Leverancier
  - Handmatige verificatie/correctie vereist

- **Synchronisatie**:
  - Handmatig: "Scan Gmail" knop
  - Automatisch: dagelijks (cron job)
  - Alleen ongelezen emails of sinds laatste sync

### 4. Dashboard

#### Hoofdscherm
- **Overzicht**:
  - Totale omzet dit jaar
  - Totale omzet dit kwartaal
  - Totale kosten dit jaar
  - Winst dit jaar (omzet - kosten)

- **Grafieken**:
  - Omzet per maand (bar chart)
  - Omzet per project (pie chart)
  - Kosten per categorie (pie chart)
  - Inkomsten vs Uitgaven (line chart)

- **Recent**:
  - Laatste 10 facturen (inkomsten)
  - Laatste 10 uitgaven
  - Nog te betalen facturen
  - Nog te ontvangen betalingen

- **Acties**:
  - Nieuwe factuur maken
  - Nieuwe uitgave registreren
  - Sync Mollie
  - Sync Bunq
  - Scan Gmail

#### Filters
- Periode selecteren (datum range)
- Project selecteren
- Status filter (betaald/onbetaald)
- Zoeken op klant/leverancier/omschrijving

### 5. Export & Rapportage

#### "1-Klik" Export voor Belastingaangifte ✅ GEÏMPLEMENTEERD (27 oktober 2025)

##### Kwartaaloverzicht ✅
**Status**: Volledig geïmplementeerd
- **Locatie**: /reports (Rapportages menu)
- **Knop**: "Exporteer Kwartaal" met jaar/kwartaal dropdown
- **Output**: CSV (Excel-compatible met UTF-8 BOM + semicolon separator)
- **Bestand**: `belastingdienst_Q[1-4]_[jaar].csv`
- **Inhoud**:
  - Bedrijfsgegevens header (KvK, BTW-id, adres)
  - Samenvatting sectie met:
    - Totale omzet dit kwartaal
    - Totale uitgaven dit kwartaal
    - Winst (omzet - kosten)
  - Omzet per project breakdown
  - Uitgaven per categorie breakdown
  - Volledige inkomsten lijst (factuurnummer, datum, klant, project, bedragen)
  - Volledige uitgaven lijst (factuurnummer, datum, leverancier, categorie, bedragen)
- **Technisch**: `TaxExportService::exportQuarterlyReport()`

##### Jaaroverzicht ✅
**Status**: Volledig geïmplementeerd
- **Locatie**: /reports (Rapportages menu)
- **Knop**: "Exporteer Jaaroverzicht" met jaar dropdown
- **Output**: CSV (Excel-compatible)
- **Bestand**: `belastingdienst_jaaroverzicht_[jaar].csv`
- **Inhoud**:
  - Bedrijfsgegevens header
  - Jaarlijkse samenvatting met:
    - Totale omzet hele jaar
    - Totale uitgaven hele jaar
    - Netto winst (omzet - kosten)
  - Per kwartaal breakdown (Q1, Q2, Q3, Q4)
  - Omzet per project (heel jaar)
  - Uitgaven per categorie (heel jaar)
  - Alle inkomsten gedetailleerd (project, klant, bedragen, betaaldatum)
  - Alle uitgaven gedetailleerd (categorie, leverancier, bedragen, betaaldatum)
- **Technisch**: `TaxExportService::exportYearlyReport()`

##### Transactie Export ⏳
**Status**: Nog te implementeren
- **Knop**: "Export Alle Transacties"
- **Output**: Excel/CSV
- **Inhoud**:
  - Alle Mollie betalingen
  - Alle Bunq transacties
  - Alle handmatige facturen
  - Met: datum, type, bedrag, project, categorie, status

##### BTW Aangifte ✅
**Status**: Volledig geïmplementeerd (voor toekomstig gebruik)
- **Locatie**: /reports (Rapportages menu)
- **Knop**: "Exporteer BTW Aangifte" met jaar/kwartaal dropdown
- **Output**: CSV (Excel-compatible)
- **Bestand**: `btw_aangifte_Q[1-4]_[jaar].csv`
- **Inhoud**:
  - Bedrijfsgegevens header
  - BTW Berekening sectie:
    - Verschuldigde BTW op omzet (21%)
    - Voorbelasting op kosten (terugvorderbaar)
    - **Netto te betalen BTW** aan Belastingdienst
  - Gedetailleerde inkomsten met BTW breakdown
  - Gedetailleerde uitgaven met BTW breakdown
- **Technisch**: `TaxExportService::exportBTWReport()`
- **Note**: Klaar voor wanneer BTW-plichtig wordt (omzet > €20.000/jaar)

##### Export Management ✅
**Status**: Volledig geïmplementeerd
- **Functionaliteit**:
  - Overzicht van eerder gegenereerde exports
  - Download functionaliteit voor oude exports
  - Delete functionaliteit voor oude exports
  - Bestandsgrootte en datum weergave
  - Exports opgeslagen in `storage/app/exports/`
- **Bewaarplicht**: Exports bewaren minimaal 7 jaar voor Belastingdienst

#### Rapportages
- **Omzet rapport**: per project, per periode
- **Kosten rapport**: per categorie, per periode
- **Winst rapport**: per project, per periode
- **Klant rapport**: top klanten, omzet per klant
- **Leverancier rapport**: uitgaven per leverancier

### 6. Archief & Backup

#### Bewaartermijn (7 jaar verplicht)
- Alle facturen
- Alle uitgaven
- Alle PDF bestanden
- Alle banktransacties

#### Backup Functionaliteit
- **Knop**: "Maak Jaarlijkse Backup"
- **Output**: ZIP bestand met:
  - Alle facturen (Excel)
  - Alle uitgaven (Excel)
  - Alle PDF's
  - Database dump
  - Rapportages (PDF)

- **Per jaar archiveren**
- Download lokaal
- Optioneel: automatische backup naar externe opslag

### 7. Instellingen & Configuratie

#### Bedrijfsgegevens
- KvK nummer
- BTW nummer (indien van toepassing)
- Adres gegevens
- Bank gegevens
- Facturatievoorkeuren

#### API Configuratie
- Mollie API key (live/test)
- Bunq API key
- Gmail OAuth credentials
- Synchronisatie instellingen

#### Facturatie Instellingen
- Automatische factuurnummering
- Prefix voor factuurnummers
- Standaard betalingstermijn
- BTW percentages (voor later)

#### Notificaties
- Email notificaties bij nieuwe betalingen
- Herinneringen voor aangifte deadlines
- Alerts voor onbetaalde facturen

### 8. Gebruikersbeheer

#### Authenticatie
- Inloggen met email/wachtwoord
- 2FA (optioneel maar aanbevolen)
- Wachtwoord reset

#### Rollen (voor later)
- Admin: volledige toegang
- Accountant: alleen lezen en rapporten
- Beperkt: alleen invoeren

#### Security
- Sessions
- CSRF bescherming
- Rate limiting
- Audit log (wie heeft wat gedaan)

## User Interface

### Design Principes
- **Eenvoudig**: Niet overloaded, focus op functionaliteit
- **Snel**: Dashboard moet snel laden
- **Overzichtelijk**: Goede structuur, makkelijk navigeren
- **Mobile responsive**: Moet ook op tablet/telefoon werken

### Hoofdnavigatie
```
[Logo] Havun Admin

[Dashboard] [Facturen] [Uitgaven] [Projecten] [Rapportages] [Sync] [Instellingen] [User]
```

### Kleurenschema
- Primary: [IN TE VULLEN - wat is Havun brand kleur?]
- Success: Groen (voor betaald)
- Warning: Oranje (voor pending)
- Danger: Rood (voor onbetaald/verlopen)
- Info: Blauw

## Workflows

### Workflow 1: Factuur verzenden en betaling ontvangen
1. Gebruiker maakt nieuwe factuur in systeem
2. Status: "Concept"
3. Gebruiker verzendt factuur naar klant (buiten systeem of via email)
4. Status: "Verzonden"
5. Klant betaalt via Mollie
6. Mollie sync draait en matcht betaling
7. Status: "Betaald"
8. Verschijnt in dashboard en rapportages

### Workflow 2: Uitgave registreren
1. Factuur komt binnen via email (havun22@gmail.com)
2. Gmail sync detecteert factuur PDF
3. Systeem maakt concept uitgave met PDF
4. Gebruiker verifieert en vult aan (categorie, project)
5. Gebruiker betaalt via Bunq
6. Bunq sync matcht betaling
7. Status: "Betaald"
8. Verschijnt in kosten rapportages

### Workflow 3: Kwartaal aangifte voorbereiden
1. Kwartaal is voorbij (bijv. Q1 eindigt 31 maart)
2. Gebruiker klikt "Export Kwartaal Q1 2024"
3. Systeem genereert Excel en PDF met:
   - Alle inkomsten Q1
   - Totaal per project
   - Detail lijst
4. Gebruiker downloadt bestanden
5. Gebruiker gebruikt dit voor aangifte bij Belastingdienst

## Technical Requirements

### Performance
- Dashboard laden < 2 seconden
- Factuur lijst (100 items) laden < 1 seconde
- Export genereren < 5 seconden
- API sync (Mollie) < 10 seconden

### Browser Support
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

### Responsive Breakpoints
- Desktop: > 1024px
- Tablet: 768px - 1024px
- Mobile: < 768px

## Fases van Implementatie

### Fase 1: MVP (Minimum Viable Product)
- [ ] Basis Laravel setup
- [ ] Database structuur
- [ ] Handmatig facturen invoeren (in/uit)
- [ ] Basis dashboard met totalen
- [ ] Project management
- [ ] Eenvoudige export (Excel)

### Fase 2: API Integraties
- [ ] Mollie integration
- [ ] Bunq integration
- [ ] Gmail integration
- [ ] Automatische synchronisatie

### Fase 3: Rapportages & Export
- [ ] Kwartaaloverzicht
- [ ] Jaaroverzicht
- [ ] PDF generatie
- [ ] Backup functionaliteit

### Fase 4: Advanced Features
- [ ] Grafieken en visualisaties
- [ ] Automatische categorisering
- [ ] BTW voorbereiding
- [ ] Notificaties
- [ ] Multi-user support

## Vragen nog te beantwoorden
1. Wat is de Havun brand kleur voor UI?
2. Moet het systeem ook facturen kunnen genereren en versturen (PDF)?
3. Wil je automatische herinneringen voor onbetaalde facturen?
4. Moet er een klanten/contacten database zijn?
5. Wil je een mobile app of is responsive web genoeg?
