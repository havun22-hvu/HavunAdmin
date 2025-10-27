# Havun - Bedrijfsgegevens

## Algemene Informatie

### Bedrijfsgegevens
- **Bedrijfsnaam**: Havun
- **KvK nummer**: 98516000
- **SBI code**: 63.100 (Gegevensverwerking, webhosting en aanverwante activiteiten)
- **Omzetbelastingnummer**: 195200305B01
- **BTW-identificatienummer**: NL002995910B70
- **Rechtsvorm**: Eenmanszaak
- **Vestigingsadres**: Jacques Bloemhof 57, 1628 VN Hoorn
- **Postadres**: Jacques Bloemhof 57, 1628 VN Hoorn

### Contact
- **Email zakelijk**: havun22@gmail.com
- **Telefoon**: 06-25058214
- **Website**: havun.nl
- **Admin portal**: admin.havun.nl

### Bankgegevens
- **Bank**: Bunq
- **Type rekening**: Zakelijke rekening
- **IBAN**: NL75BUNQ2167592531

## Activiteiten

### Hoofdactiviteit
Website en app ontwikkeling

### Projecten
1. **Herdenkingsportaal.nl**
   - Status: Bijna online
   - Type: Webapp voor online herdenkingen
   - Betalingen via: Mollie
   - Commercieel: JA

2. **IDSee**
   - Status: Nog te starten
   - Type: Hondenregistratie systeem
   - Functionaliteit: Registratie van honden en nestjes (moeder/pup) met chipnummers en eigenaar
   - Doel: Controle op buitenlandse hondenhandel
   - Technologie: Cardano blockchain registratie
   - Betalingen via: Nog te bepalen
   - Commercieel: Planning onbekend

3. **Judotoernooi**
   - Status: Bijna klaar
   - Type: Toernooi organisatie en afhandeling systeem
   - Functionaliteit: Toernooien organiseren en live afspelen op toernooidag
   - Betalingen via: Nog niet commercieel
   - Commercieel: NEE (nog niet)

## Belastingsituatie

### Huidige Status
- **BTW-plichtig**: NEE (KOR regeling)
- **Klein ondernemersregeling (KOR)**: JA - aangevraagd
- **Omzetbelasting aangifte**: JA - verplicht (geen BTW afdragen, wel omzet aangeven)
- **Voorbelasting**: Kan BTW op kosten terugvragen (zoals Claude AI)
- **BTW drempel**: €20.000 omzet per jaar (daarna verplicht BTW-plichtig)

### Boekhouding
- **Administratiekantoor**: Zelf (met HavunAdmin systeem)
- **Adviseur**: Kennis met verstand van zaken (voor vragen)
- **Boekjaar**: 1 januari - 31 december
- **BTW aangifte frequentie**: N.v.t. (KOR regeling)
- **Omzetbelasting aangifte**: Per jaar (via aangifte inkomstenbelasting)

## Leveranciers & Kosten

### Vaste Kosten (maandelijks)
- **Hosting (Hetzner)**: ~€5,00 per maand (wisselend)
- **Domain registraties**: €1,01 per maand (€12,08 per jaar voor 2 domeinen)
  - herdenkingsportaal.nl: €6,04/jaar (via mijn.host)
  - havun.nl: €6,04/jaar (via mijn.host)
- **Bunq abonnement**: €13,99 per maand (Pro Business)
- **Claude AI (Anthropic)**: €108,90 per maand (incl. €18,90 BTW)
  - BTW terugvraagbaar via voorbelasting later

### Software & Tools
- **Development**: Claude AI (Anthropic) - €90,00 excl. BTW
- **Design**: [IN TE VULLEN]
- **Project management**: [IN TE VULLEN]
- **Overige**: [IN TE VULLEN]

### Totale Vaste Kosten
- **Per maand**: ~€128,90
- **Per jaar**: ~€1.546,80

## API Credentials

### Gmail API
- **Google Cloud Project**: Havun Admin
- **Client ID**: `361603632927-rj4pkh18eq8un4vgq9l5ckk70murd1v6.apps.googleusercontent.com`
- **Client Secret**: `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`
- **Redirect URI (local)**: `http://localhost:8000/api/gmail/callback`
- **Redirect URI (production)**: `https://admin.havun.nl/api/gmail/callback`
- **⚠️ Let op**: Production redirect URI moet nog toegevoegd worden in Google Cloud Console voor live gebruik
- **Scopes**:
  - `https://www.googleapis.com/auth/gmail.readonly` (emails lezen)
  - `https://www.googleapis.com/auth/gmail.modify` (labels toevoegen)
- **Test user**: havun22@gmail.com
- **Status**: Geconfigureerd op 27 oktober 2025
- **Kosten**: GRATIS (1 miljard requests/dag quota)

### Mollie API
- **Account**: Herdenkingsportaal.nl
- **Live API key (production)**: `live_DmyesxzcAqkVp4RMx5DutBSd5KyhV7`
  - Gebruik voor: www.herdenkingsportaal.nl
- **Live API key (staging)**: `live_aKqTeJbFeuzARSeapNE3A2Tc8B2V3S`
  - Gebruik voor: www.staging.herdenkingsportaal.nl (testdoeleinden)
- **Kosten**: €0,29 per iDEAL transactie

### Bunq API
- **Account**: Zakelijke rekening (NL75BUNQ2167592531)
- **API Key**: [WORDT AANGEMAAKT ZODRA APPLICATIE WERKT]
- **Environment**: production
- **Account ID**: [NOG TE BEPALEN]
- **API toegang**: Vereist Business plan
- **Kosten**: Inbegrepen in Business plan
- **⚠️ Belangrijk**: API key moet binnen 3 uur na aanvraag gekoppeld worden aan applicatie
  - Daarom pas aanmaken zodra HavunAdmin live staat

## Notities
- Alle facturen komen binnen op havun22@gmail.com
- Gmail moet gescand worden op facturen van leveranciers
- Mollie transacties zijn klant betalingen (inkomsten)
- Bunq transacties bevatten zowel inkomsten als uitgaven

## HavunAdmin Functionaliteit
- **Facturatie**: GEEN factuurgeneratie in HavunAdmin
  - Facturen worden verstuurd vanuit Herdenkingsportaal zelf
  - HavunAdmin registreert en importeert alleen
  - Eventueel API koppeling tussen Herdenkingsportaal en HavunAdmin voor factuur kopieën
- **Import bronnen**:
  - Mollie API (betalingen van klanten)
  - Bunq API (alle banktransacties)
  - Gmail API (facturen van leveranciers als PDF)
  - Optioneel: Herdenkingsportaal API (factuur kopieën)
- **Export**: Kwartaal/jaaroverzichten voor belastingaangifte
