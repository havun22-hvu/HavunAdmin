# Havun Admin - Bedrijfsadministratie Systeem

## Overzicht
Administratie en registratie systeem voor Havun, gericht op belastingdoeleinden en financieel beheer.

## Doel
- Bijhouden van alle facturen (inkomsten en uitgaven)
- Automatische import van Mollie betalingen, Bunq transacties en Gmail facturen
- Overzicht per project (Herdenkingsportaal.nl, IDSee, Judotoernooi)
- 1-klik export voor omzetbelasting aangifte
- Online webapp - toegankelijk vanuit binnen- en buitenland

## Documentatie Structuur

### Planning & Business
- [BUSINESS-INFO.md](BUSINESS-INFO.md) - Bedrijfsgegevens Havun
- [TAX-REQUIREMENTS.md](TAX-REQUIREMENTS.md) - Nederlandse belasting eisen en administratie verplichtingen
- [FUNCTIONAL-REQUIREMENTS.md](FUNCTIONAL-REQUIREMENTS.md) - Functionele eisen en features

### Technische Documentatie
- [TECHNICAL-ARCHITECTURE.md](TECHNICAL-ARCHITECTURE.md) - Technische architectuur en tech stack
- [API-INTEGRATIONS.md](API-INTEGRATIONS.md) - Mollie, Bunq en Gmail API integraties
- [DATABASE-DESIGN.md](DATABASE-DESIGN.md) - Database structuur en relaties
- [SECURITY.md](SECURITY.md) - Beveiliging en privacy

### Implementatie
- [IMPLEMENTATION-PLAN.md](IMPLEMENTATION-PLAN.md) - Stapsgewijs implementatieplan
- [DEPLOYMENT.md](DEPLOYMENT.md) - Deployment op Hetzner server

## Status
🟡 Planning fase - documentatie wordt opgesteld

## Tech Stack
- **Backend**: Laravel (PHP 8.x)
- **Database**: MySQL ✅ (zelfde MySQL instance als Herdenkingsportaal)
- **APIs**: Mollie, Bunq, Gmail
- **Hosting**: Hetzner (zelfde server als Herdenkingsportaal)
- **Omgevingen**: Local, Staging, Production

## Bedrijfsgegevens (kort)
- **Bedrijf**: Havun
- **KvK**: 98516000
- **BTW-id**: NL002995910B70
- **Omzetbelasting nr**: 195200305B01
- **Bank**: Bunq (NL75BUNQ2167592531)
- **Email**: havun22@gmail.com

Zie [BUSINESS-INFO.md](BUSINESS-INFO.md) voor volledige details.
