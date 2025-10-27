# Database Design - Havun Admin

**Database**: MySQL
**Character Set**: utf8mb4
**Collation**: utf8mb4_unicode_ci

## Overzicht

Het database ontwerp ondersteunt:
- Facturenbeheer (inkomsten en uitgaven)
- Project tracking
- Klanten en leveranciers
- API integraties (Mollie, Bunq, Gmail)
- Categorieën en BTW (voor later)
- Rapportages en exports

## Entity Relationship Diagram (beschrijving)

```
Users (1) -----> (*) Invoices
Projects (1) -----> (*) Invoices
Customers (1) -----> (*) Invoices (inkomsten)
Suppliers (1) -----> (*) Invoices (uitgaven)
Categories (1) -----> (*) Invoices (uitgaven)
Invoices (1) -----> (*) Invoice_Items
Invoices (1) -----> (*) Transactions
API_Syncs - logging table
Settings - key/value configuratie
```

---

## Tabellen

### 1. users
Gebruikers die toegang hebben tot het systeem

| Kolom | Type | Constraints | Beschrijving |
|-------|------|-------------|--------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | Naam gebruiker |
| email | VARCHAR(255) | NOT NULL, UNIQUE | Email adres |
| email_verified_at | TIMESTAMP | NULL | Email verificatie |
| password | VARCHAR(255) | NOT NULL | Gehashed wachtwoord |
| role | ENUM | 'admin', 'user', 'accountant' | Gebruikersrol |
| is_active | BOOLEAN | DEFAULT true | Account actief? |
| remember_token | VARCHAR(100) | NULL | Remember me token |
| created_at | TIMESTAMP | NULL | Aanmaakdatum |
| updated_at | TIMESTAMP | NULL | Laatste wijziging |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (email)
- INDEX (role)

---

### 2. projects
Projecten waarvoor facturen worden gemaakt

| Kolom | Type | Constraints | Beschrijving |
|-------|------|-------------|--------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | Project naam |
| slug | VARCHAR(255) | NOT NULL, UNIQUE | URL-vriendelijke naam |
| description | TEXT | NULL | Beschrijving |
| color | VARCHAR(7) | NULL | Hex kleurcode voor UI (#FF5733) |
| status | ENUM | 'active', 'development', 'archived' | Project status |
| start_date | DATE | NULL | Start datum |
| is_active | BOOLEAN | DEFAULT true | Actief? |
| created_at | TIMESTAMP | NULL | Aanmaakdatum |
| updated_at | TIMESTAMP | NULL | Laatste wijziging |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (slug)
- INDEX (status)

**Standaard projecten:**
- Herdenkingsportaal.nl
- IDSee
- Judotoernooi
- Algemeen/Overig

---

### 3. customers
Klanten (voor inkomsten facturen)

| Kolom | Type | Constraints | Beschrijving |
|-------|------|-------------|--------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | Klant naam |
| company_name | VARCHAR(255) | NULL | Bedrijfsnaam |
| email | VARCHAR(255) | NULL | Email adres |
| phone | VARCHAR(50) | NULL | Telefoonnummer |
| address | VARCHAR(255) | NULL | Adres |
| postal_code | VARCHAR(20) | NULL | Postcode |
| city | VARCHAR(100) | NULL | Stad |
| country | VARCHAR(100) | DEFAULT 'Nederland' | Land |
| kvk_number | VARCHAR(50) | NULL | KvK nummer |
| btw_number | VARCHAR(50) | NULL | BTW nummer |
| notes | TEXT | NULL | Notities |
| is_active | BOOLEAN | DEFAULT true | Actief? |
| created_at | TIMESTAMP | NULL | Aanmaakdatum |
| updated_at | TIMESTAMP | NULL | Laatste wijziging |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (email)
- INDEX (kvk_number)

---

### 4. suppliers
Leveranciers (voor uitgaven facturen)

| Kolom | Type | Constraints | Beschrijving |
|-------|------|-------------|--------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | Leverancier naam |
| email | VARCHAR(255) | NULL | Email adres |
| phone | VARCHAR(50) | NULL | Telefoonnummer |
| website | VARCHAR(255) | NULL | Website |
| iban | VARCHAR(34) | NULL | IBAN voor betalingen |
| kvk_number | VARCHAR(50) | NULL | KvK nummer |
| btw_number | VARCHAR(50) | NULL | BTW nummer |
| notes | TEXT | NULL | Notities |
| is_active | BOOLEAN | DEFAULT true | Actief? |
| created_at | TIMESTAMP | NULL | Aanmaakdatum |
| updated_at | TIMESTAMP | NULL | Laatste wijziging |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (email)
- INDEX (name)

**Voorbeelden:**
- Hetzner (hosting)
- Bunq (bank)
- Mollie (payment provider)

---

### 5. categories
Categorieën voor uitgaven (kostencategorieën)

| Kolom | Type | Constraints | Beschrijving |
|-------|------|-------------|--------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | NOT NULL | Categorie naam |
| slug | VARCHAR(255) | NOT NULL, UNIQUE | URL-vriendelijke naam |
| description | TEXT | NULL | Beschrijving |
| color | VARCHAR(7) | NULL | Hex kleurcode voor UI |
| parent_id | BIGINT UNSIGNED | NULL, FK | Parent categorie (subcategorieën) |
| is_active | BOOLEAN | DEFAULT true | Actief? |
| created_at | TIMESTAMP | NULL | Aanmaakdatum |
| updated_at | TIMESTAMP | NULL | Laatste wijziging |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (slug)
- INDEX (parent_id)
- FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL

**Standaard categorieën:**
- Hosting & Infrastructuur
- Software & Licenties
- Betaaldiensten
- Marketing
- Kantoorkosten
- Overige

---

### 6. invoices
Hoofdtabel voor alle facturen (inkomsten EN uitgaven)

| Kolom | Type | Constraints | Beschrijving |
|-------|------|-------------|--------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| invoice_number | VARCHAR(50) | NOT NULL, UNIQUE | Factuurnummer |
| type | ENUM | 'income', 'expense' | Inkomsten of uitgave |
| user_id | BIGINT UNSIGNED | NULL, FK | Aangemaakt door gebruiker |
| project_id | BIGINT UNSIGNED | NULL, FK | Gekoppeld aan project |
| customer_id | BIGINT UNSIGNED | NULL, FK | Klant (bij income) |
| supplier_id | BIGINT UNSIGNED | NULL, FK | Leverancier (bij expense) |
| category_id | BIGINT UNSIGNED | NULL, FK | Categorie (bij expense) |
| invoice_date | DATE | NOT NULL | Factuurdatum |
| due_date | DATE | NULL | Vervaldatum |
| payment_date | DATE | NULL | Betaaldatum |
| description | TEXT | NULL | Omschrijving |
| subtotal | DECIMAL(10,2) | NOT NULL, DEFAULT 0 | Subtotaal (excl BTW) |
| vat_amount | DECIMAL(10,2) | DEFAULT 0 | BTW bedrag |
| vat_percentage | DECIMAL(5,2) | DEFAULT 0 | BTW percentage (21.00) |
| total | DECIMAL(10,2) | NOT NULL | Totaalbedrag (incl BTW) |
| status | ENUM | 'draft', 'sent', 'paid', 'overdue', 'cancelled' | Status |
| payment_method | VARCHAR(50) | NULL | Betalingsmethode |
| reference | VARCHAR(255) | NULL | Referentie (bijv. Mollie payment ID) |
| file_path | VARCHAR(255) | NULL | Pad naar PDF bestand |
| source | ENUM | 'manual', 'mollie', 'bunq', 'gmail' | Bron van factuur |
| mollie_payment_id | VARCHAR(255) | NULL | Mollie payment ID |
| bunq_transaction_id | VARCHAR(255) | NULL | Bunq transaction ID |
| gmail_message_id | VARCHAR(255) | NULL | Gmail message ID |
| notes | TEXT | NULL | Interne notities |
| created_at | TIMESTAMP | NULL | Aanmaakdatum |
| updated_at | TIMESTAMP | NULL | Laatste wijziging |
| deleted_at | TIMESTAMP | NULL | Soft delete |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (invoice_number)
- INDEX (type)
- INDEX (status)
- INDEX (invoice_date)
- INDEX (user_id)
- INDEX (project_id)
- INDEX (customer_id)
- INDEX (supplier_id)
- INDEX (category_id)
- INDEX (mollie_payment_id)
- INDEX (bunq_transaction_id)
- INDEX (gmail_message_id)
- FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
- FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL
- FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
- FOREIGN KEY (supplier_id) REFERENCES suppliers(id) ON DELETE SET NULL
- FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL

**Factuurnummer format:**
- Inkomsten: `INV-2024-0001`
- Uitgaven: `EXP-2024-0001`

---

### 7. invoice_items
Individuele regels op facturen (optioneel, voor gedetailleerde facturen)

| Kolom | Type | Constraints | Beschrijving |
|-------|------|-------------|--------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| invoice_id | BIGINT UNSIGNED | NOT NULL, FK | Gekoppelde factuur |
| description | TEXT | NOT NULL | Omschrijving regel |
| quantity | DECIMAL(10,2) | DEFAULT 1 | Aantal |
| unit_price | DECIMAL(10,2) | NOT NULL | Prijs per stuk |
| vat_percentage | DECIMAL(5,2) | DEFAULT 0 | BTW percentage |
| subtotal | DECIMAL(10,2) | NOT NULL | Subtotaal regel |
| vat_amount | DECIMAL(10,2) | DEFAULT 0 | BTW bedrag |
| total | DECIMAL(10,2) | NOT NULL | Totaal regel |
| sort_order | INT | DEFAULT 0 | Volgorde |
| created_at | TIMESTAMP | NULL | Aanmaakdatum |
| updated_at | TIMESTAMP | NULL | Laatste wijziging |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (invoice_id)
- INDEX (sort_order)
- FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE

---

### 8. transactions
Bank/payment transacties (van Mollie, Bunq)

| Kolom | Type | Constraints | Beschrijving |
|-------|------|-------------|--------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| invoice_id | BIGINT UNSIGNED | NULL, FK | Gekoppelde factuur |
| type | ENUM | 'income', 'expense' | Inkomend of uitgaand |
| source | ENUM | 'mollie', 'bunq', 'manual' | Bron van transactie |
| external_id | VARCHAR(255) | NULL | External ID (Mollie/Bunq) |
| transaction_date | DATETIME | NOT NULL | Transactie datum |
| amount | DECIMAL(10,2) | NOT NULL | Bedrag |
| description | TEXT | NULL | Omschrijving |
| counterparty_name | VARCHAR(255) | NULL | Naam tegenpartij |
| counterparty_iban | VARCHAR(34) | NULL | IBAN tegenpartij |
| payment_method | VARCHAR(50) | NULL | Betaalmethode (iDEAL, etc.) |
| status | VARCHAR(50) | NULL | Status (paid, pending, failed) |
| raw_data | JSON | NULL | Ruwe API response |
| matched | BOOLEAN | DEFAULT false | Gematcht met factuur? |
| created_at | TIMESTAMP | NULL | Aanmaakdatum |
| updated_at | TIMESTAMP | NULL | Laatste wijziging |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (invoice_id)
- INDEX (type)
- INDEX (source)
- INDEX (external_id)
- INDEX (transaction_date)
- INDEX (matched)
- FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL

---

### 9. api_syncs
Logging van API synchronisaties

| Kolom | Type | Constraints | Beschrijving |
|-------|------|-------------|--------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| service | ENUM | 'mollie', 'bunq', 'gmail' | Service |
| type | VARCHAR(50) | NOT NULL | Type sync (payments, transactions, emails) |
| status | ENUM | 'success', 'failed', 'partial' | Status |
| started_at | DATETIME | NOT NULL | Start tijd |
| completed_at | DATETIME | NULL | Eind tijd |
| items_found | INT | DEFAULT 0 | Aantal items gevonden |
| items_processed | INT | DEFAULT 0 | Aantal verwerkt |
| items_created | INT | DEFAULT 0 | Aantal nieuw aangemaakt |
| items_updated | INT | DEFAULT 0 | Aantal geüpdatet |
| items_failed | INT | DEFAULT 0 | Aantal gefaald |
| error_message | TEXT | NULL | Error bericht |
| metadata | JSON | NULL | Extra metadata |
| created_at | TIMESTAMP | NULL | Aanmaakdatum |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (service)
- INDEX (status)
- INDEX (started_at)

---

### 10. settings
Key-value store voor systeem instellingen

| Kolom | Type | Constraints | Beschrijving |
|-------|------|-------------|--------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| key | VARCHAR(255) | NOT NULL, UNIQUE | Setting key |
| value | TEXT | NULL | Setting value (JSON mogelijk) |
| type | VARCHAR(50) | DEFAULT 'string' | Data type (string, json, boolean, etc.) |
| description | TEXT | NULL | Beschrijving |
| is_public | BOOLEAN | DEFAULT false | Publiek zichtbaar? |
| created_at | TIMESTAMP | NULL | Aanmaakdatum |
| updated_at | TIMESTAMP | NULL | Laatste wijziging |

**Indexes:**
- PRIMARY KEY (id)
- UNIQUE (key)

**Voorbeelden:**
```json
{
  "company_name": "Havun",
  "company_kvk": "98516000",
  "company_btw_id": "NL002995910B70",
  "invoice_prefix_income": "INV",
  "invoice_prefix_expense": "EXP",
  "btw_enabled": false,
  "last_mollie_sync": "2024-10-27 12:00:00",
  "last_bunq_sync": "2024-10-27 12:00:00",
  "last_gmail_sync": "2024-10-27 12:00:00",
  "auto_sync_enabled": true,
  "gmail_refresh_token": "encrypted_token_here"
}
```

---

### 11. oauth_tokens
OAuth refresh tokens voor API's (Gmail)

| Kolom | Type | Constraints | Beschrijving |
|-------|------|-------------|--------------|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Primary key |
| service | VARCHAR(50) | NOT NULL | Service naam (gmail, etc.) |
| access_token | TEXT | NULL | Access token (encrypted) |
| refresh_token | TEXT | NULL | Refresh token (encrypted) |
| expires_at | DATETIME | NULL | Verloop tijd access token |
| scope | TEXT | NULL | OAuth scopes |
| is_active | BOOLEAN | DEFAULT true | Actief? |
| created_at | TIMESTAMP | NULL | Aanmaakdatum |
| updated_at | TIMESTAMP | NULL | Laatste wijziging |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (service)
- INDEX (expires_at)

---

## Database Relaties

### One-to-Many
- `users` → `invoices` (1 user kan meerdere facturen aanmaken)
- `projects` → `invoices` (1 project heeft meerdere facturen)
- `customers` → `invoices` (1 klant heeft meerdere facturen)
- `suppliers` → `invoices` (1 leverancier heeft meerdere facturen)
- `categories` → `invoices` (1 categorie heeft meerdere facturen)
- `invoices` → `invoice_items` (1 factuur heeft meerdere regels)
- `invoices` → `transactions` (1 factuur kan meerdere transacties hebben)

### Self-referencing
- `categories` → `categories` (parent-child relatie voor subcategorieën)

---

## Indexes & Performance

### Meest gebruikte queries

1. **Dashboard totalen**:
   ```sql
   SELECT SUM(total) FROM invoices
   WHERE type='income' AND status='paid'
   AND YEAR(invoice_date) = 2024
   ```
   Index: (type, status, invoice_date)

2. **Per project**:
   ```sql
   SELECT * FROM invoices
   WHERE project_id = ? AND type='income'
   ```
   Index: (project_id, type)

3. **Onbetaalde facturen**:
   ```sql
   SELECT * FROM invoices
   WHERE status IN ('sent', 'overdue')
   ORDER BY due_date ASC
   ```
   Index: (status, due_date)

4. **API matching**:
   ```sql
   SELECT * FROM invoices
   WHERE mollie_payment_id = ?
   ```
   Index: (mollie_payment_id)

---

## Data Integriteit

### Constraints
- Alle foreign keys met `ON DELETE SET NULL` of `ON DELETE CASCADE`
- `deleted_at` voor soft deletes (facturen worden nooit echt verwijderd)
- UNIQUE constraints op externe IDs (mollie_payment_id, etc.)

### Validatie
- Totaalbedragen worden berekend: `total = subtotal + vat_amount`
- Datums: `invoice_date <= payment_date` (indien betaald)
- Type checks: income moet customer hebben, expense moet supplier hebben

---

## BTW Voorbereiding

Het systeem is voorbereid op BTW, maar nu nog uitgeschakeld:

**Velden aanwezig:**
- `vat_amount` (BTW bedrag)
- `vat_percentage` (BTW percentage)
- `btw_number` (op customers en suppliers)

**Feature flag:**
```php
// .env
ENABLE_BTW=false

// settings table
"btw_enabled": false
```

**Wanneer BTW actief wordt:**
1. `ENABLE_BTW=true` in .env
2. Velden worden zichtbaar in UI
3. Berekeningen includeren BTW
4. Extra rapportages voor BTW aangifte

---

## Backup Strategie

### Dagelijkse Backups
```bash
# Cron job op Hetzner
mysqldump -u user -p havun_admin > /backups/havun_admin_$(date +%Y%m%d).sql
```

### 7-jaar Bewaarplicht
- Jaarlijkse export naar externe opslag
- Inclusief alle PDF bestanden
- Encrypted storage

---

## Migraties Planning

### Fase 1: Basis Tabellen
1. users
2. projects
3. categories
4. settings

### Fase 2: Hoofdfunctionaliteit
5. customers
6. suppliers
7. invoices
8. invoice_items

### Fase 3: API Integratie
9. transactions
10. api_syncs
11. oauth_tokens

### Fase 4: Seed Data
- Standaard projecten
- Standaard categorieën
- Admin gebruiker
- Basis settings

---

## Voorbeeld Data

### Projects
```sql
INSERT INTO projects (name, slug, status, color) VALUES
('Herdenkingsportaal.nl', 'herdenkingsportaal', 'active', '#4A90E2'),
('IDSee', 'idsee', 'development', '#50E3C2'),
('Judotoernooi', 'judotoernooi', 'development', '#F5A623'),
('Algemeen', 'algemeen', 'active', '#7ED321');
```

### Categories
```sql
INSERT INTO categories (name, slug, color) VALUES
('Hosting & Infrastructuur', 'hosting', '#FF6B6B'),
('Software & Licenties', 'software', '#4ECDC4'),
('Betaaldiensten', 'payment', '#45B7D1'),
('Marketing', 'marketing', '#FFA07A'),
('Kantoorkosten', 'kantoor', '#98D8C8'),
('Overige', 'overige', '#95A5A6');
```

---

## Database Grootte Schatting

**Per jaar (schatting):**
- Invoices: ~500 records × 2KB = 1MB
- Invoice_items: ~1000 records × 1KB = 1MB
- Transactions: ~1000 records × 2KB = 2MB
- API_syncs: ~1000 records × 1KB = 1MB
- **Totaal**: ~5MB per jaar

**Na 7 jaar**: ~35MB (zeer beheersbaar)

PDF bestanden daarentegen kunnen meer ruimte innemen (geschat 500MB na 7 jaar).

---

## Security Overwegingen

1. **API Tokens**: Altijd encrypted in oauth_tokens
2. **Soft Deletes**: Facturen nooit echt verwijderen
3. **Audit Trail**: created_at, updated_at op alle tabellen
4. **User Roles**: Admin, User, Accountant
5. **2FA**: Mogelijk via Laravel Fortify (toekomstige feature)

---

## Volgende Stap

Na deze database design:
1. ✅ Laravel migrations maken
2. ✅ Models met relaties maken
3. ✅ Seeders voor standaard data
4. ✅ Factories voor testing
