## CONTEXT: WordPress Plugin Architecture

Create a WordPress plugin with the following structure:

CUSTOM DATABASE TABLES:
1. wp_iat_regions - Stores GeoJSON polygon zones (13 total: 11 zones + 2 airports)
   - Fields: zone_code (string), zone_type (european|anatolian|airport), geojson (text), base_price_intra (decimal)

2. wp_iat_pricings - Stores zone-to-zone pricing
   - Fields: from_zone_code, to_zone_code, price_eur (decimal), is_bidirectional

3. wp_iat_bookings - Stores reservations
   - Fields: 20+ fields including pickup/dropoff addresses, zones, datetime, passenger info, cancellation_token, linked_booking_id, is_return_trip

4. wp_iat_options - Admin-managed service options (TV Vehicle, Child Seat etc.)
   - Fields: option_name, option_slug, price_eur, description, is_active, sort_order

5. wp_iat_booking_options - Selected options per booking
   - Fields: booking_id, option_id, option_price_eur

6. wp_iat_api_usage - Tracks daily/monthly API call counts per provider

7. wp_iat_geocache - Caches geocoding results (address_hash -> lat/lng/zone)

SECURITY:
- Use nonces for all AJAX
- Prepared statements for DB queries
- reCAPTCHA v3 integration
- Rate limiting by IP

CODING STANDARDS:
- PSR-12 PHP style
- WordPress coding standards
- OOP with namespaces (IAT\Regions, IAT\Booking, etc.)
- Singleton pattern for main classes

LANGUAGE: English only (v1)
PAYMENT: Cash only (v1), schema ready for online payment (v2)
FRONTEND: Vanilla JS (ES6+), Shortcode integration