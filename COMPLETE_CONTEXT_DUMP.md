## FULL PROJECT CONTEXT

I'm building a WordPress plugin for Istanbul airport transfers with these specs:

CORE FUNCTION:
- Multi-step booking form (date/time, addresses, passengers, flight code)
- Address autocomplete using Yandex/Google Maps (20 API keys with rotation)
- GeoJSON zone detection (13 zones + 2 airports)
- Zone-to-zone pricing (50+ combinations, EUR)
- Booking state machine: pending → confirmed/auto_confirmed/cancelled
- Email confirmations with WP Mail SMTP
- Admin dashboard for zones, pricing, APIs, bookings

TECH STACK:
- PHP 8.0+, WordPress 6.0+
- Vanilla JS (ES6) or React
- MySQL with custom tables
- Composer for PHP deps
- Webpack for assets

CURRENT TASK: [describe what you need]

CONSTRAINTS:
- 24h advance booking only
- Max 5 passengers/luggage
- reCAPTCHA v3 required
- International phone format
- Turkish/English bilingual (WPML ready)
- Cash payment only (no online payment)

REFERENCE DATA:
[ paste relevant pricing matrix or GeoJSON sample ]