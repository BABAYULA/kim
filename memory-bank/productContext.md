# Product Context

This plugin exists to solve the problem of coordinating airport transfers across a sprawling city with multiple fare zones and varying prices. It provides a structured way for customers to request a ride, and for administrators to manage pricing, zones, and bookings without leaving the WordPress ecosystem.

**Problems Solved:**
- Manual quoting and human error when determining zones and prices.
- Difficulty rotating between geocoding API limits and tracking usage.
- Lack of centralized admin interface for managing service areas and settings.
- Unclear booking states and notification workflows leading to missed confirmations.

**User Experience Goals:**
- Simple, fast multi-step form with autocomplete and live price quotes.
- Clear feedback when a pickup or dropoff address falls outside service zones.
- Responsive admin pages with map previews for zones and pricing tables.
- Reliable email notifications for customers and admins.

**How It Works:**
- Frontend shortcode embeds booking form.
- Addresses are geocoded via rotating API provider and cached.
- Coordinates are tested against stored GeoJSON polygons to determine zones.
- Price is looked up from a matrix and options are added.
- Booking is saved in custom tables with state machine handling automatic transitions.
- Cron jobs process auto-confirmations and cleanup.

**Business Context:**
- Primary customers are airport taxi operators or WordPress site owners offering transfer services in Istanbul.
- Anticipated to be sold/downloaded via WordPress plugin directories or private distribution.
- Requires ongoing maintenance for API key updates and zone changes as city evolves.
