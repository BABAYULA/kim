## TASK: Build Responsive Booking Form (Vanilla JS ES6+)

TECH STACK:
- Vanilla JavaScript (ES6+) — NO React/Vue
- CSS Grid/Flexbox (no framework required)
- Accessible (ARIA labels, keyboard nav)
- Mobile-first responsive
- Shortcode integration: [iat_booking_form]

COMPONENTS:

1. AddressAutocomplete
   - Input with debounce (300ms)
   - Dropdown suggestions
   - API: Custom endpoint using Yandex/Google rotation
   - Props: onSelect(address, lat, lng), placeholder

2. FlightCodeInput (Conditional)
   - Show if selected address matches airport zone
   - Pattern validation: [A-Z]{2,3}\d{1,4}
   - Helper text: "We'll track for delays"

3. PassengerCounter
   - Min 1, Max 5
   - Affects Step 3 name fields

4. DynamicOptions
   - Loaded from wp_iat_options table (admin-managed)
   - Each option: checkbox + price display
   - Updates QuoteSummary dynamically

5. DateTimePicker
   - Native date input (min: +24h)
   - Custom time select (00:00-23:45, 15min steps)

6. QuoteSummary
   - Display calculated price (base + options breakdown)
   - Map View: Leaflet.js with OpenStreetMap (Show route preview)
   - Zone names
   - 🆕 Return Trip Toggle:
     - "Return trip for same price" checkbox
     - When ON: Return date + return time inputs appear
     - Price display: Outbound €X + Return €X = Total €Y
   - CTA buttons (Accept / Modify)

7. PassengerForm
   - Dynamic name inputs based on passenger count
   - Intl phone input with country flag
   - Email validation

AJAX ENDPOINTS (WordPress REST API):
POST /wp-json/iat/v1/geocode
  Body: {address: string}
  Response: {success: true, lat: float, lng: float, zone: object} | {success: false, error: string}

POST /wp-json/iat/v1/quote
  Body: {from: string, to: string, date: string, time: string, passengers: int, luggage: int, options: array}
  Response: {success: true, price: float, options_total: float, zones: object} | {success: false, error: string, contact: object}

POST /wp-json/iat/v1/book
  Body: {all form data, recaptcha_token: string, return_trip: bool, return_date: string, return_time: string}
  Response: {success: true, booking_id: string, return_booking_id: string|null, message: string} | {success: false, errors: array}

VALIDATION RULES:
- Date must be > now + 24 hours
- Phone must be valid E.164 (e.g., +905551234567)
- Email standard validation
- Passenger names: min 2 chars each
- reCAPTCHA v3 score > 0.5
- Return trip: return date must be >= outbound date

GEOCODING STRATEGY:
- Autocomplete: Yandex/Google (API rotation with usage tracking)
- Geocoding: Nominatim (free, primary) → Yandex/Google (fallback)
- All results cached in wp_iat_geocache