# Project Brief

**Name:** Istanbul Airport Transfer WordPress Plugin

**Goal:** Build a comprehensive, secure, and maintainable WordPress plugin that enables users to book airport transfer services between Istanbul Airport (IST) and Sabiha Gökçen Airport (SAW) and various zones around Istanbul.

**Core Requirements:**

- Multi-step booking workflow with address autocomplete, zone detection and pricing.
- GeoJSON-based service zones (13 total including 2 airports) with point-in-polygon detection.
- Zone-to-zone pricing matrix, return trip support, optional extras (TV, child seat).
- Admin UI for managing zones, pricing, bookings, API keys and settings.
- Email notifications and booking state machine with auto-confirmation.
- API rotation for geocoding services (Yandex, Google, Nominatim) with caching and rate limiting.
- Security: reCAPTCHA v3, SQL injection protection, XSS/CSRF mitigations, rate limiting, API key secrecy.

**Constraints:**

- PHP 8.0+, WordPress 6.0+, MySQL 5.7+.
- No online payments (cash only, future schema ready for payments).
- Bilingual (Turkish/English) readiness.
- Maximum 5 passengers/luggage, 24h advance booking.

**Project Phase:** Planning & infrastructure; code implementation deferred until architecture and deployment processes are defined.

**Stakeholders:** Plugin author, WordPress site owners, admin users managing zones and bookings, end customers making transfer reservations.

**Success Metrics:** Reliable zone detection and pricing, secure administration, zero downtime during deployments, maintainable codebase, automated tests and CI pipeline.
