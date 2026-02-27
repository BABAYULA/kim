## FULL PROJECT CONTEXT

**Project:** Istanbul Airport Transfer WordPress Plugin
**Type:** Custom WordPress Plugin (PHP 8.0+ / Vanilla JS ES6+)
**Status:** Planning complete, code implementation pending

### CORE FUNCTION
- Multi-step booking form (date/time, addresses, passengers, flight code)
- Address autocomplete using Yandex/Google Maps (20 API keys with rotation)
- GeoJSON zone detection (13 zones: 11 Istanbul zones + 2 airports)
- Zone-to-zone pricing (90+ route combinations, EUR)
- Booking state machine: pending → confirmed/auto_confirmed/cancelled
- Email confirmations (5 templates) via WP Mail SMTP
- Admin dashboard for zones, pricing, APIs, bookings, service options

### TECH STACK
- PHP 8.0+, WordPress 6.0+, MySQL 5.7+
- Vanilla JS (ES6+) — NO React/Vue/jQuery
- Leaflet.js for map display
- Composer (PHP deps), NPM + Webpack (asset bundling)
- PHPUnit for testing

### CONSTRAINTS
- 24h advance booking only
- Max 5 passengers/luggage
- reCAPTCHA v3 required (score > 0.5)
- International phone format (E.164)
- English only (v1)
- Cash payment only (no online payment in v1)
- All DB queries via $wpdb->prepare()

### DB TABLES (7 custom)
1. wp_iat_regions — GeoJSON polygons for 13 zones
2. wp_iat_pricings — Zone-to-zone pricing matrix
3. wp_iat_bookings — Reservation records (20+ fields)
4. wp_iat_options — Admin-managed service options (TV Vehicle, Child Seat)
5. wp_iat_booking_options — Selected options per booking
6. wp_iat_api_usage — API call tracking per provider/key/day
7. wp_iat_geocache — Cached geocoding results

### ZONES (13 total)
European: Bolge-Yarim, Bolge-Bir, Bolge-BirBucuk, Bolge-Iki, Bolge-IkiBucuk, Bolge-BirBucuk-Sariyer, Bolge-Iki-Sariyer
Anatolian: Anadolu-Yarim, Anadolu-Bir, Anadolu-BirBucuk, Anadolu-Iki
Airports: IST (Istanbul Airport), SAW (Sabiha Gokcen)

### KEY FILES
- `CODING_RULES.md` — Complete coding standards and project rules
- `MODULE-01` through `MODULE-12` — Module specifications
- `memory-bank/` — Project context & planning documents
- `implementation_plan.md` — Full architecture plan
- `fiyat_listesi.md` — Complete pricing matrix
- `koordinatlar.md` — GeoJSON zone coordinates
- `data/pricing-matrix.json` — Machine-readable pricing data
- `data/zones-metadata.json` — Zone codes and metadata

### CURRENT TASK
[Describe what you need the AI to do here]

### REFERENCE DATA
[Paste relevant pricing matrix, GeoJSON sample, or module doc section here]