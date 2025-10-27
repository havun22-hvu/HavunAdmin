# Herdenkingsportaal Integratie - Vereisten voor Duplicate Matching

## Doel
Automatische koppeling van transacties tussen Herdenkingsportaal, Mollie, Bunq en Gmail op basis van een unieke Memorial Reference.

## Memorial Reference Format

**Format**: Eerste 12 characters van het monument UUID (zonder hyphens)

**Voorbeeld**:
- Monument UUID: `1a2b3c4d-5e6f-7890-abcd-ef1234567890`
- Memorial Reference: `1a2b3c4d5e6f` (eerste 12 chars zonder hyphens)

**Waarom 12 characters?**
- Uniek genoeg (16^12 = 281 biljoen mogelijkheden)
- Kort genoeg voor betalingskenmerk velden
- Makkelijk te herkennen en valideren

---

## Wat moet Herdenkingsportaal meesturen?

### 1. Bij Mollie Payment Creatie

Wanneer Herdenkingsportaal een betaling naar Mollie stuurt:

```php
$payment = $mollie->payments->create([
    'amount' => [
        'currency' => 'EUR',
        'value' => '35.00'
    ],
    'description' => 'Premium Monument - Gerrit Willem van Unen',
    'redirectUrl' => '...',
    'webhookUrl' => '...',
    'metadata' => [
        'memorial_reference' => '1a2b3c4d5e6f',  // 👈 DIT IS ESSENTIEEL!
        'order_id' => 'HERD-2025-0042',           // Optioneel maar handig
        'customer_name' => 'Hr H van Unen',
    ]
]);
```

**Belangrijk**:
- De `memorial_reference` moet in de `metadata` van de Mollie payment
- Dit zorgt ervoor dat HavunAdmin de Mollie transactie kan matchen met Bunq en Gmail
- De reference komt automatisch in Mollie emails terecht

### 2. Bij Factuur Synchronisatie naar HavunAdmin (Toekomstig)

Wanneer Herdenkingsportaal via API facturen naar HavunAdmin stuurt:

```json
{
  "invoice_number": "HERD-2025-0042",
  "memorial_reference": "1a2b3c4d5e6f",
  "customer_name": "Hr H van Unen",
  "monument_name": "Premium Monument - Gerrit Willem van Unen",
  "amount": 35.00,
  "vat_amount": 0,
  "total": 35.00,
  "invoice_date": "2025-10-25",
  "status": "paid",
  "payment_method": "mollie",
  "mollie_payment_id": "tr_FMRKqQERmAyhqzehYF5GJ"
}
```

**Belangrijk**:
- `memorial_reference` is het belangrijkste veld voor duplicate matching
- `mollie_payment_id` helpt bij extra verificatie

---

## Hoe werkt de Matching?

### Flow: Klant koopt monument

1. **Herdenkingsportaal**: Klant koopt monument, monument UUID: `1a2b3c4d-5e6f-...`
   - Maakt factuur: `HERD-2025-0042`
   - Memorial Reference: `1a2b3c4d5e6f` (eerste 12 chars)

2. **Mollie Payment**: Herdenkingsportaal stuurt naar Mollie
   ```php
   metadata: {
     'memorial_reference' => '1a2b3c4d5e6f'
   }
   ```

3. **Mollie Email**: Mollie stuurt betaalbevestiging
   - Email bevat: "Memorial ref: 1a2b3c4d5e6f"
   - HavunAdmin Gmail sync importeert email
   - Extraheert `1a2b3c4d5e6f` automatisch

4. **Bunq Transactie**: Geld komt binnen op Bunq rekening
   - Beschrijving: "Mollie - 1a2b3c4d5e6f - Premium Monument"
   - HavunAdmin Bunq sync importeert transactie
   - Extraheert `1a2b3c4d5e6f` automatisch

5. **Herdenkingsportaal Sync**: (Optioneel - als we API bouwen)
   - HavunAdmin haalt factuur op via API
   - Heeft `memorial_reference: 1a2b3c4d5e6f`

6. **Automatische Matching**: HavunAdmin ziet 3-4 records met zelfde reference
   ```
   [MASTER] BUNQ-54321 (Bunq) - €35,00
     ├─ [DUPLICATE] HERD-2025-0042 (Herdenkingsportaal) - Bewijsstuk
     └─ [DUPLICATE] GMAIL-19a1c2a0ad (Gmail) - Email backup
   ```

---

## Bunq Beschrijving Format (Aanbeveling)

Als Mollie geld doorstort naar Bunq, zou de beschrijving idealiter zijn:

```
Mollie - 1a2b3c4d5e6f - Premium Monument
```

Of:
```
1a2b3c4d5e6f - Premium Monument - Gerrit Willem van Unen
```

**Belangrijk**: De 12-character reference moet in de Bunq beschrijving staan voor automatische matching.

Dit wordt meestal automatisch door Mollie ingevuld, maar controleer of de memorial_reference daadwerkelijk doorkomt in Bunq.

---

## Testing Checklist

### Herdenkingsportaal Team moet testen:

- [ ] Memorial reference (12 chars) wordt gegenereerd van monument UUID
- [ ] Memorial reference wordt meegegeven in Mollie metadata
- [ ] Mollie betaalbevestiging email bevat memorial reference
- [ ] Bunq transactie beschrijving bevat memorial reference
- [ ] Test met meerdere monumenten op zelfde dag (unieke references)

### HavunAdmin Team moet testen:

- [ ] Gmail sync extraheert memorial reference uit Mollie emails
- [ ] Bunq sync extraheert memorial reference uit transactie beschrijving
- [ ] Automatische matching werkt (3+ transacties gekoppeld)
- [ ] Reconciliatie dashboard toont correcte duplicate groups
- [ ] Master priority: Bunq is leidend, Herdenkingsportaal is duplicate

---

##Voorbeeld Test Scenario

**Monument**: Premium Monument voor "Jan Jansen"
- UUID: `a1b2c3d4-e5f6-7890-1234-567890abcdef`
- Memorial Reference: `a1b2c3d4e5f6`

**Stap 1**: Herdenkingsportaal maakt factuur
```
HERD-2025-0100
Memorial Ref: a1b2c3d4e5f6
Bedrag: €35,00
```

**Stap 2**: Mollie payment
```json
{
  "id": "tr_TestPayment123",
  "description": "Premium Monument - Jan Jansen",
  "metadata": {
    "memorial_reference": "a1b2c3d4e5f6"
  }
}
```

**Stap 3**: Mollie email naar havun22@gmail.com
```
Onderwerp: Betaling ontvangen - Premium Monument
Body: ...memorial ref: a1b2c3d4e5f6...
```

**Stap 4**: Bunq transactie
```
Bedrag: +€35,00
Beschrijving: Mollie - a1b2c3d4e5f6 - Premium Monument - Jan Jansen
```

**Verwacht Resultaat in HavunAdmin**:
```
Reconciliatie Dashboard:

Memorial Ref: a1b2c3d4e5f6 (3 transacties)

  [MASTER] BUNQ-TRX-98765
   └─ Bunq | €35,00 | 25-10-2025

  [DUPLICATE] HERD-2025-0100
   └─ Herdenkingsportaal | €35,00 | Bewijsstuk | 100% match

  [DUPLICATE] GMAIL-19abc123
   └─ Gmail | €35,00 | Email backup | 100% match
```

---

## Vragen?

Contact: Henk van Unen - henk@havun.nl

---

## Toekomstige Uitbreidingen

### 1. Herdenkingsportaal → HavunAdmin API
Direct synchronisatie van facturen via REST API endpoint:

```
POST /api/invoices/sync
Authorization: Bearer {api_token}

Body:
{
  "invoice_number": "HERD-2025-0042",
  "memorial_reference": "1a2b3c4d5e6f",
  ...
}
```

### 2. Webhooks
Real-time notificaties:
- Mollie webhook: Payment status updates
- Bunq webhook: Transaction notifications
- Herdenkingsportaal webhook: Factuur created/updated

### 3. Reconciliation Rapport
Automatisch dagelijks/wekelijks rapport:
- Aantal matched transactions
- Unmatched transactions (need review)
- Missing invoices (Bunq heeft geld maar geen factuur)
- Missing payments (Factuur bestaat maar geen Bunq ontvangst)
