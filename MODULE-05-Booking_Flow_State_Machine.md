## TASK: Implement Multi-Step Booking with State Management

FORM STEPS:
Step 1: Trip Details
  - Date (min: today + 24 hours)
  - Time (24h format, 15-min intervals)
  - From address (autocomplete via Yandex/Google rotation)
  - To address (autocomplete)
  - Passengers (1-5)
  - Luggage (1-5)
  - Dynamic options from wp_iat_options (e.g. TV Vehicle +10€, managed via admin)
  - Flight code (conditional: if from/to is airport)
  - reCAPTCHA v3

Step 2: Quote Display
  - Show route, datetime, price breakdown (base + options)
  - 🆕 "Return trip for same price" toggle
    - When enabled: Return date + Return time inputs appear
    - Pricing: Each trip = same unit price (e.g. 40€ + 40€ = 80€ total)
    - Display: Outbound €X + Return €X = Total €Y
  - Accept/Modify buttons

Step 3: Passenger Details
  - Email (validation)
  - Phone (intl format, lib: intl-tel-input)
  - Passenger names array (dynamic based on count)
  - Notes (optional)

STATE MACHINE:
pending → confirmed (via admin email link click)
pending → auto_confirmed (cron after 24h if no action)
pending → cancelled (via cancellation link, allowed until 1h before pickup)
confirmed → cancelled (via cancellation link, allowed until 1h before pickup)

DATABASE FIELDS:
status: enum('pending', 'confirmed', 'auto_confirmed', 'cancelled')
cancellation_token: varchar(64) — single token for both customer and admin
linked_booking_id: bigint NULL — reference to return trip booking
is_return_trip: tinyint(1) DEFAULT 0

RETURN TRIP LOGIC:
- Toggle ON → 2 separate bookings created on form submit
- Outbound booking: linked_booking_id → return booking ID
- Return booking: is_return_trip = 1, linked_booking_id → outbound booking ID
- Return direction: pickup ↔ dropoff addresses swap
- Cancellation: Independent (cancelling one does NOT cancel the other)

CRON JOBS:
1. Hourly: Auto-confirm check
   SELECT * FROM wp_iat_bookings 
   WHERE status = 'pending' 
   AND created_at < NOW() - INTERVAL 24 HOUR
   → Update to 'auto_confirmed', send email to customer + admin

2. Daily: Cleanup
   DELETE FROM wp_iat_bookings WHERE created_at < NOW() - INTERVAL 6 MONTH

3. Weekly: Geocache cleanup
   DELETE FROM wp_iat_geocache WHERE created_at < NOW() - INTERVAL 30 DAY

EMAIL TEMPLATES (English, WP Mail SMTP):
1. booking_new_admin: Admin notification + Confirm/Cancel links
2. booking_pending_customer: Booking received + Cancel link
3. booking_confirmed: Confirmed details
4. booking_auto_confirmed: 24h auto-confirm notice (customer + admin)
5. booking_cancelled: Cancellation confirmation (customer + admin)

SECURITY:
- Token: Single cancellation_token (bin2hex(random_bytes(32)))
- Links: site.com/iat-action/?token=xxx&booking=123&action=confirm|cancel
- Rate limit: 5 attempts per IP per hour on action endpoints