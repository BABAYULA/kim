# System Patterns

## Architectural Overview

The plugin follows a modular, object-oriented architecture built around a global `IAT_Main` singleton that bootstraps and registers all sub-modules with WordPress hooks. Each functional area is encapsulated in a class under `includes/` (or appropriate subdirectory) with clearly defined responsibilities.

### Key Components

- **Main Class (`class-iat-main.php`)**
  - Initializes plugin, loads dependencies, registers assets, sets up hooks.
  - **Conditional Loading**: Frontend assets only load on pages containing booking shortcodes via `should_load_frontend_assets()` method.

- **Autoloader (`class-iat-autoloader.php`)**
  - PSR-4 compatible autoloader with subdirectory mapping.
  - **Directory Map**: Handles nested classes (`DB_Manager`, `Security`, `Zone_Detector`, `API_Rotator`, `Pricing_Engine`) in their respective subdirectories.

- **Database Manager (`class-iat-db-manager.php`)**
  - Creates custom tables, handles migrations, provides generic CRUD wrappers.
  - Uses `$wpdb` with prepared statements.
  - **Geocache with TTL**: 30-day expiry on geocoding results to ensure fresh data.
  - **Cryptographic Security**: Booking IDs generated using `random_bytes()` instead of `str_shuffle()`.
  - **JSON Validation**: Proper GeoJSON validation via `sanitize_geojson()` method.

- **Geocoding Layer**
  - `API_Rotator` selects between providers (Yandex, Google, Nominatim) based on usage.
  - Each provider has its own class implementing a common interface.
  - Results stored in `wp_iat_geocache` to reduce external calls.

- **Zone Detection**
  - `Zone_Detector` loads GeoJSON for regions and tests points using ray-casting.
  - Handles overlapping polygons by preference rules (airports first).

- **Pricing Engine**
  - Central service calculates price based on zones and extras.
  - Return trips share prices or double depending on business logic.

- **Booking Workflow**
  - Frontend AJAX handlers create, update, and fetch quotes.
  - `Booking_State_Machine` manages status transitions (pending → confirmed/auto-confirmed/cancelled).

- **Admin Controllers**
  - Separate classes for regions, pricings, bookings, options, settings.
  - Each provides CRUD via standard WP list tables and custom forms.

- **Security Utilities**
  - Nonce helpers, input sanitizers, rate limiter.

- **Notifications/Email Manager**
  - Renders templates, queues emails via WP Mail.

### Design Patterns in Use

- **Singletons** for global access (main plugin class, DB manager).
- **Factory/Strategy** for API provider selection.
- **Repository** style for each entity (RegionRepository, BookingRepository).
- **Observer/Event** pattern via WP hooks to decouple actions (e.g. trigger email on status change).

### Deployment/Infrastructure

- Asset pipeline: Webpack builds JS/CSS into `assets/` directory; versioned for cache busting.
- Composer handles PHP dependencies, autoloading via PSR-4.
- `package.json` and NPM scripts orchestrate build tasks.
- Tests: PHPUnit for PHP units, possible Cypress or Jest for JS (future).
- CI/CD pipeline should run lint, build, tests, and package plugin.

### Migration Strategy

- Activation hook runs table creation (activator class) and seeds initial data.
- Database versioning stored in option; migration scripts executed on update.

### Failover & Recovery

- External API errors logged and retried with next provider.
- Geocoding cache fallback if all providers fail.
- Booking creation validated locally before DB insert.

## Component Relationships

```
IAT_Main
  ├─> DB_Manager
  ├─> API_Rotator -> [Yandex, Google, Nominatim]
  ├─> Zone_Detector
  ├─> Pricing_Engine
  ├─> Booking_Manager
  ├─> Admin_* Controllers
  ├─> Security (RateLimiter, Recaptcha)
  └─> Email_Manager
```
