## TASK: Generate Test Cases

UNIT TESTS (PHPUnit):
1. Zone Detection
   - Point inside polygon
   - Point outside all polygons
   - Point on edge
   - Invalid GeoJSON

2. Pricing Engine
   - Valid zone pair
   - Reverse zone pair (should match)
   - Same zone (intrazonal)
   - Undefined zone pair

3. API Rotator
   - First API available
   - First API limit reached, second available
   - All APIs exhausted
   - Cache hit/miss

4. Booking State Machine
   - Create pending → confirm → cancel
   - Create pending → wait 24h → auto_confirm
   - Invalid token rejection

INTEGRATION TESTS:
1. Full booking flow
   - Enter addresses → Get quote → Submit booking → Receive email → Confirm
   - Verify database state at each step

2. Geocoding with rotation
   - Exhaust Yandex 1, verify Yandex 2 used
   - Verify usage logged correctly

3. Concurrent bookings
   - Race condition on API limits
   - Duplicate booking prevention

E2E TESTS (Playwright/Cypress):
1. Form validation
   - Past date rejection
   - Invalid phone format
   - Missing passenger names

2. Autocomplete
   - Type "Taksim" → Select suggestion → Verify zone detected

3. Price calculation
   - IST to Taksim → Verify 40€ displayed

EDGE CASES:
- Turkish characters in addresses (İ, ş, ç, ğ)
- International phone numbers (+1, +44, +91)
- Flight code format variations (TK 123, TK123, tk123)
- Same pickup/dropoff address
- Midnight crossing (23:59 booking)