## TASK: Build WordPress Admin Dashboard

PAGES:
1. Regions (CRUD for GeoJSON zones)
   - List view with map preview
   - Add/Edit: Title, Zone Code (dropdown), GeoJSON textarea
   - Visual map editor (Leaflet.js) OR geojson.io integration
   - Import/Export GeoJSON batch

2. Pricing Matrix
   - Grid view: From Zone × To Zone
   - Quick edit cells (inline)
   - CSV import/export
   - Bulk edit tools

3. API Management
   - Drag-drop sortable list (20 APIs max)
   - Per API: Provider, Key (masked), Daily Limit, Monthly Limit, Status
   - Usage stats: Today/This Month bars
   - Test connection button

4. Bookings List (WP_List_Table)
   - Columns: ID, Date/Time, Route, Price, Status, Actions
   - Filters: Status, Date Range, Zone
   - Bulk: Export CSV, Change Status
   - Quick view modal with details

5. Settings
   - Contact Email (undefined zone)
   - Contact WhatsApp (intl format)
   - reCAPTCHA keys
   - Email template editor (HTML with placeholders)
   - Cron settings

DASHBOARD WIDGET:
- Today's bookings count
- Pending confirmations
- This week's revenue (estimated)
- API usage status (green/yellow/red)

PERMISSIONS:
- iat_manager: Full access
- iat_operator: View bookings only
- administrator: Full access