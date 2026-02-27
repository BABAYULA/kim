# Istanbul Airport Transfer Plugin — Coding Rules

> **This file is the single source of truth for ALL AI coding assistants working on this project.**
> Every AI tool (Gemini, Cursor, Cline, Windsurf, Copilot, etc.) MUST read and follow these rules before generating any code.

---

## 1. Multi-AI Collaboration Standards

### Communication Protocols
- **File Locking**: When multiple AIs work on the same file, use `.locks/` directory to prevent conflicts
- **Change Tracking**: Document all changes in `memory-bank/activeContext.md` under "Recent Changes" section
- **Handoff Protocol**: When switching between AIs, add a comment in the file indicating the handoff point
- **Conflict Resolution**: Use git-style conflict markers when multiple AIs modify the same section

### Consistency Rules
- **Naming Consistency**: All AIs must use identical class names, method names, and variable names
- **Style Consistency**: All AIs must follow the same coding style and formatting
- **Documentation Consistency**: All AIs must update documentation in the same way
- **Testing Consistency**: All AIs must write tests following the same patterns

### Handoff Markers
When an AI completes work on a file, add this marker at the end of the file:
```php
// AI Handoff: [AI Name] completed [feature/module] at [timestamp]
// Next AI should continue with [next task]
```

### Collaboration Workflows
- **File Locking**: Create `.locks/[filename].lock` before modifying any file
- **Timestamp Markers**: Include timestamp and AI identifier in all changes
- **Atomic Commits**: Keep changes atomic and focused on one task
- **Progress Updates**: Update `memory-bank/progress.md` after each major change
- **Issue Reporting**: Use `memory-bank/issues.md` for problems encountered
- **Decision Logging**: Document architectural decisions in `memory-bank/systemPatterns.md`

---

## 2. Project Identity

| Key | Value |
|-----|-------|
| **Name** | Istanbul Airport Transfer |
| **Type** | WordPress Plugin |
| **Package** | `istanbul-airport-transfer` |
| **Text Domain** | `istanbul-airport-transfer` |
| **Version** | 1.0.0 |
| **License** | GPL v2 or later |
| **Language** | English only (v1) |
| **Payment** | Cash only (v1), DB schema ready for online payment (v2) |

**What it does:** Multi-step booking form for airport transfers between Istanbul Airport (IST) / Sabiha Gökçen Airport (SAW) and 11 service zones across Istanbul (13 zones total including airports). Features zone-to-zone pricing, geocoding API rotation, GeoJSON point-in-polygon zone detection, booking state machine, and admin management panel.

---

## 3. Technology Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend | PHP | 8.0+ |
| CMS | WordPress | 6.0+ |
| Database | MySQL | 5.7+ |
| Frontend JS | Vanilla JavaScript | ES6+ |
| Frontend CSS | Vanilla CSS | CSS3 (Grid/Flexbox) |
| Maps | Leaflet.js | 1.9+ |
| Build | Webpack | 5.x |
| PHP Deps | Composer | 2.x |
| JS Deps | NPM | - |
| Testing | PHPUnit | 9.5+ |

**DO NOT USE:** React, Vue, Angular, jQuery, Tailwind, Bootstrap, Laravel, Symfony, or any other framework not listed above.

---

## 4. Coding Standards

### PHP
- **PSR-12** coding style strictly enforced
- **WordPress Coding Standards** for WP-specific code (hooks, filters, DB)
- **PSR-4** autoloading with `IAT\` namespace prefix
- All classes use **Singleton pattern** for main service classes
- **PHPDoc blocks** required on every class, method, and property
- Use `declare(strict_types=1)` where possible
- Type hints on all method parameters and return types

### JavaScript
- ES6+ syntax (const/let, arrow functions, template literals, modules)
- No global variables — use `iatSettings` object for WP-localized data
- JSDoc comments on all functions
- Debounce user inputs (300ms for autocomplete)

### CSS
- BEM naming convention (`.iat-block__element--modifier`)
- Mobile-first responsive design
- CSS custom properties for theming
- No `!important` unless overriding WP admin styles

### General
- No hardcoded strings — use constants or settings
- All user-facing strings in English
- Error messages should be descriptive and actionable
- Log errors via `error_log()` with `[IAT]` prefix

---

## 5. File Structure & Naming

```
istanbul-airport-transfer/
├── istanbul-airport-transfer.php    # Main plugin file
├── uninstall.php                    # Cleanup on uninstall
├── composer.json
├── package.json
├── webpack.config.js
├── phpunit.xml
├── CODING_RULES.md                  # THIS FILE
├── .locks/                          # AI collaboration locks
├── includes/                        # PHP classes
│   ├── class-iat-main.php          # Main plugin class
│   ├── class-iat-activator.php     # Activation handler
│   ├── class-iat-deactivator.php   # Deactivation handler
│   ├── class-iat-autoloader.php    # PSR-4 autoloader
│   ├── class-iat-shortcodes.php    # Shortcode registration
│   ├── class-iat-email-manager.php # Email notifications
│   ├── class-iat-cron-manager.php  # WP-Cron scheduled tasks
│   ├── database/
│   │   └── class-iat-db-manager.php
│   ├── admin/
│   │   ├── class-iat-admin-regions.php
│   │   ├── class-iat-admin-pricings.php
│   │   ├── class-iat-admin-bookings.php
│   │   ├── class-iat-admin-options.php
│   │   └── class-iat-settings.php
│   ├── frontend/
│   │   ├── class-iat-booking-form.php
│   │   └── class-iat-pricing-engine.php
│   ├── geocoding/
│   │   ├── class-iat-api-rotator.php
│   │   ├── class-iat-nominatim.php
│   │   ├── class-iat-yandex-geocoder.php
│   │   └── class-iat-google-geocoder.php
│   ├── zones/
│   │   └── class-iat-zone-detector.php
│   ├── security/
│   │   └── class-iat-security.php
│   ├── utils/
│   │   └── class-iat-helper.php
│   └── import/
│       └── class-iat-price-importer.php
├── admin/
│   └── views/                       # Admin PHP templates
├── assets/
│   ├── css/
│   │   ├── admin-styles.css
│   │   └── public-booking.css
│   ├── js/
│   │   ├── admin-regions.js
│   │   ├── admin-pricings.js
│   │   └── public-booking-form.js
│   └── images/
├── data/                            # Seed & reference data
│   ├── pricing-matrix.json
│   └── zones-metadata.json
├── languages/                       # i18n files
├── tests/
│   ├── unit/
│   ├── integration/
│   └── e2e/
└── memory-bank/                     # AI context documents
```

### Naming Conventions
- PHP files: `class-iat-{name}.php` (lowercase, hyphenated)
- CSS files: `{scope}-{name}.css` (admin- or public-)
- JS files: `{scope}-{name}.js`
- Classes: `IAT_{PascalCase}` (e.g., `IAT_Booking_Form`)
- Functions: `iat_{snake_case}` (e.g., `iat_generate_booking_id`)
- Constants: `IAT_{UPPER_CASE}` (e.g., `IAT_VERSION`)
- DB tables: `wp_iat_{name}` (e.g., `wp_iat_bookings`)
- Hooks: `iat/{action_name}` or `iat/{filter_name}`

---

## 6. Database Rules

### 7 Custom Tables
| Table | Purpose |
|-------|---------|
| `wp_iat_regions` | GeoJSON polygon zones (13 total) |
| `wp_iat_pricings` | Zone-to-zone pricing matrix |
| `wp_iat_bookings` | Reservation records |
| `wp_iat_options` | Admin-managed service options (TV Vehicle, Child Seat, etc.) |
| `wp_iat_booking_options` | Selected options per booking |
| `wp_iat_api_usage` | Geocoding API call tracking |
| `wp_iat_geocache` | Cached geocoding results |

### Database Conventions
- **ALWAYS** use `$wpdb->prepare()` for all queries with user input
- Use `dbDelta()` for table creation in activator
- Prefix all tables with `$wpdb->prefix . 'iat_'`
- Store dates as `datetime` in UTC, convert to `Europe/Istanbul` for display
- Use `decimal(10,2)` for prices, `decimal(10,7)` for coordinates
- Index foreign keys and frequently queried columns

---

## 7. Security Rules (MANDATORY)

Every piece of generated code MUST include:

1. **Nonce verification** for all form submissions and AJAX requests
   ```php
   check_ajax_referer('iat_nonce', 'nonce');
   wp_verify_nonce($_POST['_wpnonce'], 'iat_action');
   ```

2. **Input sanitization** on all user inputs
   ```php
   sanitize_text_field(), sanitize_email(), absint(), floatval()
   ```

3. **Output escaping** on all displayed data
   ```php
   esc_html(), esc_attr(), esc_url(), wp_kses_post()
   ```

4. **Capability checks** for admin operations
   ```php
   if (!current_user_can('manage_options')) { wp_die(); }
   ```

5. **Rate limiting** — 5 requests per IP per minute on public endpoints
6. **reCAPTCHA v3** validation on booking form submission (score > 0.5)
7. **Direct access prevention** at the top of every PHP file:
   ```php
   if (!defined('ABSPATH')) { exit; }
   ```

---

## 8. Zone System

### 13 Zones
| Code | Name | Type |
|------|------|------|
| `IST` | Istanbul Airport | airport |
| `SAW` | Sabiha Gokcen Airport | airport |
| `Bolge-Yarim` | Zone 0.5 | european |
| `Bolge-Bir` | Zone 1 | european |
| `Bolge-BirBucuk` | Zone 1.5 | european |
| `Bolge-Iki` | Zone 2 | european |
| `Bolge-IkiBucuk` | Zone 2.5 | european |
| `Bolge-BirBucuk-Sariyer` | Zone 1.5 Sariyer | european |
| `Bolge-Iki-Sariyer` | Zone 2 Sariyer | european |
| `Anadolu-Yarim` | Anatolian 0.5 | anatolian |
| `Anadolu-Bir` | Anatolian 1 | anatolian |
| `Anadolu-BirBucuk` | Anatolian 1.5 | anatolian |
| `Anadolu-Iki` | Anatolian 2 | anatolian |

Zone detection uses **Ray Casting** algorithm on GeoJSON polygons. Overlap resolution: smallest area wins. Airports are checked first.

---

## 9. Booking Flow

### Form Steps
1. **Trip Details** — Date, time, addresses (autocomplete), passengers (1–5), luggage (1–5), dynamic options from DB, flight code (conditional), reCAPTCHA
2. **Quote Display** — Route, price breakdown (base + options), return trip toggle, map preview (Leaflet), accept/modify buttons
3. **Passenger Details** — Email, phone (intl format), passenger names, notes

### State Machine
```
pending → confirmed       (admin clicks confirm link in email)
pending → auto_confirmed  (WP-Cron after 24 hours)
pending → cancelled       (cancel link, allowed until 1h before pickup)
confirmed → cancelled     (cancel link, allowed until 1h before pickup)
```

### Return Trip
- Toggle ON creates 2 separate bookings linked via `linked_booking_id`
- Each trip priced independently at the same unit price
- Cancellation is independent (cancelling one does NOT cancel the other)

---

## 10. API Rotation System

### Geocoding Providers
| Provider | Use | Limit |
|----------|-----|-------|
| Nominatim | Primary geocoder (free) | 1 req/sec |
| Yandex Maps | Autocomplete + fallback geocoder | 25,000/day per key (10 keys) |
| Google Maps | Autocomplete + fallback geocoder | 3,000/month per key (10 keys) |

### Flow
1. Check `wp_iat_geocache` first
2. Try Nominatim (free)
3. If fails → try next Yandex/Google key in rotation
4. Cache successful result
5. Log usage in `wp_iat_api_usage`

All providers implement `IAT_Geocoder_Interface` with `geocode()` and `check_limit()` methods.

---

## 11. Email Templates (5 total)

| Template ID | Trigger | Recipients |
|------------|---------|------------|
| `booking_new_admin` | New booking | Admin |
| `booking_pending_customer` | Booking created | Customer |
| `booking_confirmed` | Admin confirms | Customer |
| `booking_auto_confirmed` | 24h auto-confirm | Customer + Admin |
| `booking_cancelled` | Cancellation | Customer + Admin |

Use `wp_mail()` with HTML templates. SMTP via WP Mail SMTP plugin.

---

## 12. Cron Jobs

| Schedule | Task | Details |
|----------|------|---------|
| Hourly | Auto-confirm | `pending` bookings older than 24h → `auto_confirmed` |
| Daily | Booking cleanup | Delete bookings older than 6 months |
| Weekly | Geocache cleanup | Delete cache entries older than 30 days |

---

## 13. Module Development Order

Follow this order strictly — each module depends on the previous ones:

1. **Core Architecture** — `IAT_Main`, activator, deactivator, autoloader
2. **Database Manager** — `IAT_DB_Manager`, table creation, CRUD
3. **Security Layer** — `IAT_Security`, nonces, sanitization, rate limiting
4. **Geocoding Layer** — `IAT_API_Rotator`, provider classes, caching
5. **Zone Detection** — `IAT_Zone_Detector`, ray casting, GeoJSON
6. **Pricing Engine** — `IAT_Pricing_Engine`, zone-to-zone lookup, options
7. **Booking System** — `IAT_Booking_Form`, state machine, AJAX handlers
8. **Admin Interface** — `IAT_Admin_*` classes, WP_List_Table, settings
9. **Frontend** — CSS/JS, multi-step form, autocomplete, shortcode
10. **Email & Cron** — `IAT_Email_Manager`, `IAT_Cron_Manager`
11. **Testing** — PHPUnit unit + integration tests

---

## 14. Quality Gates

Before marking any module as complete, verify:

- [ ] PSR-12 compliance (`phpcs`)
- [ ] WordPress coding standards
- [ ] Security: nonces, sanitization, escaping on every input/output
- [ ] Error handling with try/catch and meaningful messages
- [ ] Logging with `[IAT]` prefix
- [ ] PHPDoc blocks on all classes and methods
- [ ] Unit tests with >80% coverage for the module
- [ ] `memory-bank/progress.md` updated
- [ ] Multi-AI handoff markers added where applicable

---

## 15. Reference Data

- **Pricing matrix:** See `data/pricing-matrix.json` and `fiyat_listesi.md`
- **Zone coordinates:** See `koordinatlar.md` and `data/zones-metadata.json`
- **Module specifications:** See `MODULE-01` through `MODULE-12` markdown files
- **Memory Bank:** See `memory-bank/` directory for project context
- **Implementation plan:** See `implementation_plan.md` for full architecture

---

## 16. WordPress Hooks Reference

### Actions Used
- `plugins_loaded` — Plugin initialization
- `admin_menu` — Admin menu registration
- `admin_enqueue_scripts` — Admin asset loading
- `wp_enqueue_scripts` — Frontend asset loading
- `wp_ajax_iat_*` — Authenticated AJAX handlers
- `wp_ajax_nopriv_iat_*` — Public AJAX handlers
- `iat/booking/created` — Custom: booking created event
- `iat/booking/status_changed` — Custom: status transition event

### Filters Used
- `wp_mail_from` — Email sender address
- `wp_mail_from_name` — Email sender name
- `iat/pricing/calculated` — Custom: modify price calculation

### Shortcodes
- `[iat_booking_form]` — Renders the booking form

---

## 17. Constants

```php
define('IAT_PLUGIN_FILE', __FILE__);
define('IAT_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('IAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IAT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('IAT_VERSION', '1.0.0');
define('IAT_DB_VERSION', '1.0.0');
define('IAT_MIN_PHP', '8.0');
define('IAT_MIN_WP', '6.0');
```
