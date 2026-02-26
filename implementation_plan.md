# Implementation Plan

## [Overview]
Create a comprehensive WordPress plugin for Istanbul Airport transfers with 13 service zones, zone-to-zone pricing, multi-step booking, and admin management. The plugin will handle bookings between Istanbul Airport (IST) and Sabiha Gökçen Airport (SAW) with 11 service zones across Istanbul, featuring geocoding API rotation, point-in-polygon zone detection, and a complete booking state machine.

## [Types]
### Database Schema Types
```php
// Core Plugin Tables
interface IAT_Database_Tables {
    // Regions table - stores GeoJSON polygons for 13 zones
    wp_iat_regions: {
        id: bigint AUTO_INCREMENT PRIMARY KEY,
        region_name: varchar(100) NOT NULL,
        zone_code: varchar(50) UNIQUE NOT NULL,
        zone_type: enum('european', 'anatolian', 'airport') NOT NULL,
        geojson: text NOT NULL,
        base_price_intra: decimal(10,2) DEFAULT 0.00,
        created_at: datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at: datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    }
    
    // Pricing table - zone-to-zone pricing matrix
    wp_iat_pricings: {
        id: bigint AUTO_INCREMENT PRIMARY KEY,
        from_zone_code: varchar(50) NOT NULL,
        to_zone_code: varchar(50) NOT NULL,
        price_eur: decimal(10,2) NOT NULL,
        is_bidirectional: tinyint(1) DEFAULT 1,
        created_at: datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at: datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY (from_zone_code, to_zone_code)
    }
    
    // Bookings table - reservation data
    wp_iat_bookings: {
        id: bigint AUTO_INCREMENT PRIMARY KEY,
        booking_id: varchar(32) UNIQUE NOT NULL,
        status: enum('pending', 'confirmed', 'auto_confirmed', 'cancelled') DEFAULT 'pending',
        linked_booking_id: bigint NULL,
        is_return_trip: tinyint(1) DEFAULT 0,
        pickup_address: text NOT NULL,
        pickup_lat: decimal(10,7),
        pickup_lng: decimal(10,7),
        pickup_zone_code: varchar(50),
        dropoff_address: text NOT NULL,
        dropoff_lat: decimal(10,7),
        dropoff_lng: decimal(10,7),
        dropoff_zone_code: varchar(50),
        pickup_datetime: datetime NOT NULL,
        flight_code: varchar(10),
        has_tv_option: tinyint(1) DEFAULT 0,
        passenger_count: tinyint DEFAULT 1,
        luggage_count: tinyint DEFAULT 1,
        passenger_names: json,
        contact_phone: varchar(20) NOT NULL,
        contact_email: varchar(100) NOT NULL,
        price_eur: decimal(10,2) NOT NULL,
        currency: varchar(3) DEFAULT 'EUR',
        cancellation_token: varchar(64),
        auto_confirm_deadline: datetime,
        recaptcha_score: decimal(3,2),
        created_at: datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at: datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    }
    
    // Options table - service options (TV Vehicle, Child Seat, etc.)
    wp_iat_options: {
        id: bigint AUTO_INCREMENT PRIMARY KEY,
        option_name: varchar(100) NOT NULL,
        option_slug: varchar(50) UNIQUE NOT NULL,
        price_eur: decimal(10,2) NOT NULL DEFAULT 0,
        description: text,
        is_active: tinyint(1) DEFAULT 1,
        sort_order: int DEFAULT 0,
        created_at: datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at: datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    }
    
    // Booking options table - selected options per booking
    wp_iat_booking_options: {
        id: bigint AUTO_INCREMENT PRIMARY KEY,
        booking_id: bigint NOT NULL,
        option_id: bigint NOT NULL,
        option_price_eur: decimal(10,2) NOT NULL
    }
    
    // API usage tracking table
    wp_iat_api_usage: {
        id: bigint AUTO_INCREMENT PRIMARY KEY,
        api_provider: varchar(20),
        api_key_index: tinyint,
        call_date: date,
        call_count: int DEFAULT 0,
        monthly_count: int DEFAULT 0,
        last_call_time: datetime,
        UNIQUE KEY (api_provider, api_key_index, call_date)
    }
    
    // Geocaching table - cached geocoding results
    wp_iat_geocache: {
        id: bigint AUTO_INCREMENT PRIMARY KEY,
        address_hash: varchar(64),
        lat: decimal(10,7),
        lng: decimal(10,7),
        formatted_address: text,
        zone_code: varchar(50),
        created_at: datetime,
        updated_at: datetime,
        UNIQUE KEY (address_hash)
    }
}

// Zone Types
enum Zone_Type {
    EUROPEAN = 'european',    // 7 zones on European side
    ANATOLIAN = 'anatolian',  // 4 zones on Asian side  
    AIRPORT = 'airport'       // 2 airports
}

// Booking Status Types
enum Booking_Status {
    PENDING = 'pending',
    CONFIRMED = 'confirmed', 
    AUTO_CONFIRMED = 'auto_confirmed',
    CANCELLED = 'cancelled'
}

// API Provider Types
enum API_Provider {
    YANDEX = 'yandex',
    GOOGLE = 'google',
    NOMINATIM = 'nominatim'
}

## [Files]
### Core Plugin Files
1. **istanbul-airport-transfer.php** - Main plugin file
   - Plugin header metadata
   - Activation/deactivation hooks
   - Main class initialization

2. **includes/class-iat-main.php** - Main plugin class
   - Singleton pattern implementation
   - Module loading and initialization
   - WordPress hook registration

3. **includes/class-iat-activator.php** - Plugin activation
   - Database table creation
   - Default data insertion
   - Initial configuration setup

4. **includes/class-iat-deactivator.php** - Plugin deactivation
   - Cleanup operations
   - Optional data preservation

### Database Management
5. **includes/database/class-iat-db-manager.php** - Database operations
   - Table creation and management
   - CRUD operations for all entities
   - Database migration support

### Admin Interface
6. **includes/admin/class-iat-admin-regions.php** - Regions management
   - Region CRUD operations
   - GeoJSON upload and validation
   - Map preview integration

7. **includes/admin/class-iat-admin-pricings.php** - Pricing management
   - Pricing matrix management
   - Bulk import/export functionality
   - Price validation

8. **includes/admin/class-iat-admin-bookings.php** - Bookings management
   - Booking list and filtering
   - Status management
   - Booking details and actions

9. **includes/admin/class-iat-admin-options.php** - Service options
   - Option CRUD operations
   - Sorting and ordering
   - Active/inactive management

10. **includes/admin/class-iat-settings.php** - Plugin settings
    - API key configuration
    - Contact information
    - System preferences

### Frontend Components
11. **includes/frontend/class-iat-booking-form.php** - Booking form
    - Multi-step form rendering
    - Form validation
    - AJAX handlers

12. **includes/frontend/class-iat-pricing-engine.php** - Pricing calculation
    - Zone-to-zone pricing lookup
    - Option pricing integration
    - Return trip pricing

13. **includes/class-iat-shortcodes.php** - Shortcode management
    - Booking form shortcode
    - Asset loading

### Geocoding and API
14. **includes/geocoding/class-iat-api-rotator.php** - API rotation
    - Provider failover logic
    - Usage tracking
    - Rate limiting

15. **includes/geocoding/class-iat-nominatim.php** - Nominatim geocoding
    - Free geocoding service
    - Rate limit handling

16. **includes/geocoding/class-iat-yandex-geocoder.php** - Yandex geocoding
    - Autocomplete functionality
    - Geocoding fallback

17. **includes/geocoding/class-iat-google-geocoder.php** - Google geocoding
    - Autocomplete functionality
    - Geocoding fallback

### Zone Detection
18. **includes/zones/class-iat-zone-detector.php** - Zone detection
    - Point-in-polygon algorithm
    - Overlap resolution
    - Caching integration

### Security and Utilities
19. **includes/security/class-iat-security.php** - Security features
    - Nonce management
    - Input validation
    - Rate limiting

20. **includes/utils/class-iat-helper.php** - Utility functions
    - Data formatting
    - Validation helpers
    - Common operations

### Email and Notifications
21. **includes/class-iat-email-manager.php** - Email system
    - Template management
    - SMTP integration
    - Notification triggers

22. **includes/class-iat-cron-manager.php** - Scheduled tasks
    - Auto-confirmation jobs
    - Cleanup operations
    - Cache management

### Import/Export
23. **includes/import/class-iat-price-importer.php** - Data import
    - Price list parsing
    - Bulk data import
    - Error handling

### Frontend Assets
24. **assets/css/admin-styles.css** - Admin styling
    - Admin interface styles
    - Responsive design
    - Map integration styles

25. **assets/css/public-booking.css** - Public form styling
    - Booking form styles
    - Mobile responsiveness
    - User experience enhancements

26. **assets/js/admin-regions.js** - Region management JS
    - Map interactions
    - Form validation
    - Dynamic content

27. **assets/js/admin-pricings.js** - Pricing management JS
    - Matrix interactions
    - Import functionality
    - Real-time validation

28. **assets/js/public-booking-form.js** - Booking form JS
    - Multi-step navigation
    - Address autocomplete
    - Form validation

### Configuration Files
29. **composer.json** - PHP dependencies
    - Package requirements
    - Autoloading configuration
    - Development tools

30. **package.json** - NPM dependencies
    - Build tools
    - Frontend dependencies
    - Development scripts

31. **readme.txt** - WordPress plugin readme
    - Plugin description
    - Installation instructions
    - Usage documentation

32. **uninstall.php** - Plugin uninstallation
    - Database cleanup
    - Option removal
    - File cleanup

## [Functions]
### Core Plugin Functions
1. **iat_main()** - Plugin initialization
   - File: istanbul-airport-transfer.php
   - Purpose: Initialize main plugin class
   - Usage: Called on plugin load

2. **iat_activate_plugin()** - Plugin activation
   - File: includes/class-iat-activator.php
   - Purpose: Set up database tables and default data
   - Usage: Called on plugin activation

3. **iat_deactivate_plugin()** - Plugin deactivation
   - File: includes/class-iat-deactivator.php
   - Purpose: Cleanup operations
   - Usage: Called on plugin deactivation

### Database Functions
4. **iat_create_tables()** - Create database tables
   - File: includes/database/class-iat-db-manager.php
   - Purpose: Create all 7 custom tables
   - Usage: Called during activation

5. **iat_get_region_by_code(string $zone_code)** - Get region by code
   - Purpose: Retrieve region data by zone code
   - Returns: Region array or false

6. **iat_create_region(array $data)** - Create new region
   - Purpose: Insert new region into database
   - Returns: New region ID or false

7. **iat_get_pricing(string $from_zone, string $to_zone)** - Get pricing
   - Purpose: Retrieve zone-to-zone pricing
   - Returns: Price data or false

8. **iat_create_booking(array $data)** - Create booking
   - Purpose: Insert new booking record
   - Returns: New booking ID or false

9. **iat_update_booking_status(int $id, string $status)** - Update booking status
   - Purpose: Change booking status
   - Returns: Boolean success

### Geocoding Functions
10. **iat_geocode_address(string $address)** - Geocode address
    - File: includes/geocoding/class-iat-api-rotator.php
    - Purpose: Convert address to coordinates
    - Returns: Coordinates or error

11. **iat_get_next_available_provider()** - Get next API provider
    - Purpose: Implement API rotation logic
    - Returns: Provider instance or null

12. **iat_cache_geocoding(string $address, array $data)** - Cache geocoding result
    - Purpose: Store geocoding result in cache
    - Returns: Boolean success

### Zone Detection Functions
13. **iat_detect_zone(float $lat, float $lng)** - Detect zone
    - File: includes/zones/class-iat-zone-detector.php
    - Purpose: Determine which zone coordinates belong to
    - Returns: Zone code or null

14. **iat_is_point_in_polygon(array $point, array $polygon)** - Point in polygon
    - Purpose: Ray casting algorithm implementation
    - Returns: Boolean result

### Pricing Functions
15. **iat_calculate_price(string $from_addr, string $to_addr)** - Calculate price
    - File: includes/frontend/class-iat-pricing-engine.php
    - Purpose: Calculate total booking price
    - Returns: Price breakdown

16. **iat_get_zone_from_address(string $address)** - Get zone from address
    - Purpose: Geocode and detect zone
    - Returns: Zone data or error

### Security Functions
17. **iat_verify_recaptcha(string $token)** - Verify reCAPTCHA
    - File: includes/security/class-iat-security.php
    - Purpose: Validate reCAPTCHA response
    - Returns: Boolean success

18. **iat_check_rate_limit(string $ip)** - Check rate limit
    - Purpose: Implement IP-based rate limiting
    - Returns: Boolean allowed

19. **iat_sanitize_input(string $input)** - Sanitize input
    - Purpose: Clean user input for security
    - Returns: Sanitized string

### Utility Functions
20. **iat_generate_booking_id()** - Generate booking ID
    - File: includes/utils/class-iat-helper.php
    - Purpose: Create unique booking identifier
    - Returns: 32-character string

21. **iat_generate_token()** - Generate security token
    - Purpose: Create secure tokens for actions
    - Returns: 64-character string

22. **iat_validate_phone(string $phone)** - Validate phone number
    - Purpose: E.164 format validation
    - Returns: Boolean valid

23. **iat_format_datetime(string $datetime)** - Format datetime
    - Purpose: Format dates for Istanbul timezone
    - Returns: Formatted datetime string

### Admin Functions
24. **iat_render_admin_page(string $page)** - Render admin page
    - File: includes/admin/ classes
    - Purpose: Display admin interface
    - Returns: HTML output

25. **iat_handle_form_submission()** - Handle form submission
    - Purpose: Process admin form data
    - Returns: Success/error response

### Email Functions
26. **iat_send_notification(string $type, array $data)** - Send notification
    - File: includes/class-iat-email-manager.php
    - Purpose: Send email notifications
    - Returns: Boolean success

### Cron Functions
27. **iat_auto_confirm_pending_bookings()** - Auto-confirm bookings
    - File: includes/class-iat-cron-manager.php
    - Purpose: Confirm bookings after 24 hours
    - Returns: Processed count

28. **iat_cleanup_old_data()** - Cleanup old data
    - Purpose: Remove expired cache and old bookings
    - Returns: Cleaned count

## [Classes]
### Core Plugin Classes
1. **IAT_Main** - Main plugin class
   - File: includes/class-iat-main.php
   - Pattern: Singleton
   - Purpose: Plugin initialization and module management
   - Key Methods:
     - `get_instance()` - Get singleton instance
     - `__construct()` - Initialize hooks and modules
     - `run()` - Start plugin execution
     - `activate()` - Handle activation
     - `deactivate()` - Handle deactivation

2. **IAT_Activator** - Plugin activation handler
   - File: includes/class-iat-activator.php
   - Pattern: Singleton
   - Purpose: Database setup and initial configuration
   - Key Methods:
     - `activate()` - Main activation logic
     - `create_tables()` - Create database tables
     - `insert_default_data()` - Insert default regions and pricing
     - `flush_rewrite_rules()` - Update WordPress rewrite rules

3. **IAT_Deactivator** - Plugin deactivation handler
   - File: includes/class-iat-deactivator.php
   - Pattern: Singleton
   - Purpose: Cleanup operations
   - Key Methods:
     - `deactivate()` - Main deactivation logic

### Database Management Classes
4. **IAT_DB_Manager** - Database operations manager
   - File: includes/database/class-iat-db-manager.php
   - Pattern: Singleton
   - Purpose: All database operations and table management
   - Key Methods:
     - `create_tables()` - Create all plugin tables
     - `get_table_name(string $table)` - Get full table name
     - `region_crud_methods()` - Region CRUD operations
     - `pricing_crud_methods()` - Pricing CRUD operations
     - `booking_crud_methods()` - Booking CRUD operations
     - `api_usage_methods()` - API usage tracking
     - `geocache_methods()` - Geocoding cache management

### Admin Interface Classes
5. **IAT_Admin_Regions** - Regions management
   - File: includes/admin/class-iat-admin-regions.php
   - Pattern: Singleton
   - Purpose: Admin interface for region management
   - Key Methods:
     - `__construct()` - Register admin hooks
     - `add_admin_menu()` - Add admin menu items
     - `render_regions_page()` - Display regions list
     - `render_add_region_page()` - Display add region form
     - `render_edit_region_page()` - Display edit region form
     - `handle_region_form_submission()` - Process form data
     - `handle_region_deletion()` - Process region deletion

6. **IAT_Admin_Pricings** - Pricing management
   - File: includes/admin/class-iat-admin-pricings.php
   - Pattern: Singleton
   - Purpose: Admin interface for pricing management
   - Key Methods:
     - `__construct()` - Register admin hooks
     - `add_admin_menu()` - Add admin menu items
     - `render_pricings_page()` - Display pricing matrix
     - `render_add_pricing_page()` - Display add pricing form
     - `render_edit_pricing_page()` - Display edit pricing form
     - `handle_pricing_form_submission()` - Process form data
     - `handle_pricing_deletion()` - Process pricing deletion
     - `handle_bulk_import()` - Import pricing from file

7. **IAT_Admin_Bookings** - Bookings management
   - File: includes/admin/class-iat-admin-bookings.php
   - Pattern: Singleton
   - Purpose: Admin interface for booking management
   - Key Methods:
     - `__construct()` - Register admin hooks
     - `add_admin_menu()` - Add admin menu items
     - `render_bookings_page()` - Display bookings list
     - `render_booking_detail_page()` - Display booking details
     - `handle_status_change()` - Change booking status
     - `handle_booking_deletion()` - Delete booking
     - `ajax_get_booking_details()` - AJAX booking details

8. **IAT_Admin_Options** - Service options management
   - File: includes/admin/class-iat-admin-options.php
   - Pattern: Singleton
   - Purpose: Admin interface for service options
   - Key Methods:
     - `__construct()` - Register admin hooks
     - `add_admin_menu()` - Add admin menu items
     - `render_options_page()` - Display options list
     - `render_add_option_page()` - Display add option form
     - `render_edit_option_page()` - Display edit option form
     - `handle_option_form_submission()` - Process form data
     - `handle_option_deletion()` - Delete option

9. **IAT_Settings** - Plugin settings management
   - File: includes/admin/class-iat-settings.php
   - Pattern: Singleton
   - Purpose: Admin interface for plugin settings
   - Key Methods:
     - `__construct()` - Register admin hooks
     - `add_settings_page()` - Add settings page
     - `render_settings_page()` - Display settings form
     - `register_settings()` - Register WordPress settings
     - `render_api_keys_section()` - Display API keys section
     - `render_contact_settings_section()` - Display contact section
     - `render_recaptcha_section()` - Display reCAPTCHA section
     - `sanitize_api_keys(array $input)` - Sanitize API keys

### Frontend Classes
10. **IAT_Booking_Form** - Booking form handler
    - File: includes/frontend/class-iat-booking-form.php
    - Purpose: Frontend booking form rendering and processing
    - Key Methods:
      - `__construct()` - Register frontend hooks
      - `render_booking_form()` - Display booking form
      - `handle_geocode_request()` - Process geocoding AJAX
      - `handle_quote_request()` - Process quote AJAX
      - `handle_booking_request()` - Process booking AJAX

11. **IAT_Pricing_Engine** - Pricing calculation engine
    - File: includes/frontend/class-iat-pricing-engine.php
    - Purpose: Calculate booking prices
    - Key Methods:
      - `calculate(string $from_addr, string $to_addr)` - Main calculation
      - `get_zone_from_address(string $address)` - Get zone from address
      - `lookup_price(string $from_code, string $to_code)` - Lookup price
      - `add_option_pricing(array $price_data, array $options)` - Add options

12. **IAT_Shortcodes** - Shortcode management
    - File: includes/class-iat-shortcodes.php
    - Purpose: Handle WordPress shortcodes
    - Key Methods:
      - `__construct()` - Register shortcode hooks
      - `register_shortcodes()` - Register all shortcodes
      - `render_booking_form_shortcode()` - Render booking form

### Geocoding and API Classes
13. **IAT_API_Rotator** - API rotation manager
    - File: includes/geocoding/class-iat-api-rotator.php
    - Purpose: Manage API provider rotation and failover
    - Key Methods:
      - `__construct()` - Initialize providers
      - `add_provider(IAT_Geocoder_Interface $provider, int $priority)` - Add provider
      - `geocode(string $address)` - Main geocoding method
      - `autocomplete(string $query)` - Address autocomplete
      - `get_next_available()` - Get next available provider
      - `log_usage(string $provider, int $index)` - Log API usage

14. **IAT_Nominatim** - Nominatim geocoding service
    - File: includes/geocoding/class-iat-nominatim.php
    - Implements: IAT_Geocoder_Interface
    - Purpose: Free geocoding using OpenStreetMap
    - Key Methods:
      - `geocode(string $address)` - Geocode address
      - `check_limit()` - Check rate limits

15. **IAT_Yandex_Geocoder** - Yandex geocoding service
    - File: includes/geocoding/class-iat-yandex-geocoder.php
    - Implements: IAT_Geocoder_Interface
    - Purpose: Yandex Maps geocoding and autocomplete
    - Key Methods:
      - `geocode(string $address)` - Geocode address
      - `check_limit()` - Check API limits

16. **IAT_Google_Geocoder** - Google geocoding service
    - File: includes/geocoding/class-iat-google-geocoder.php
    - Implements: IAT_Geocoder_Interface
    - Purpose: Google Maps geocoding and autocomplete
    - Key Methods:
      - `geocode(string $address)` - Geocode address
      - `check_limit()` - Check API limits

### Zone Detection Classes
17. **IAT_Zone_Detector** - Zone detection manager
    - File: includes/zones/class-iat-zone-detector.php
    - Purpose: Detect which zone coordinates belong to
    - Key Methods:
      - `detect_zone(float $lat, float $lng)` - Main detection method
      - `is_in_polygon(array $point, array $polygon)` - Point-in-polygon algorithm
      - `get_cache(string $hash)` - Get cached result
      - `set_cache(string $hash, array $data)` - Set cache result

### Security and Utility Classes
18. **IAT_Security** - Security management
    - File: includes/security/class-iat-security.php
    - Purpose: Handle all security features
    - Key Methods:
      - `__construct()` - Register security hooks
      - `verify_nonce(string $nonce, string $action)` - Verify nonce
      - `verify_recaptcha(string $token)` - Verify reCAPTCHA
      - `check_rate_limit(string $ip)` - Check rate limits
      - `sanitize_input(string $input)` - Sanitize input
      - `prepare_sql(string $sql, array $params)` - Prepare SQL

19. **IAT_Helper** - Utility functions
    - File: includes/utils/class-iat-helper.php
    - Purpose: Common utility functions
    - Key Methods:
      - `generate_booking_id()` - Generate booking ID
      - `generate_token()` - Generate security token
      - `validate_phone(string $phone)` - Validate phone
      - `validate_email(string $email)` - Validate email
      - `format_datetime(string $datetime)` - Format datetime

### Email and Notification Classes
20. **IAT_Email_Manager** - Email management
    - File: includes/class-iat-email-manager.php
    - Purpose: Handle all email notifications
    - Key Methods:
      - `__construct()` - Register email hooks
      - `send_notification(string $type, array $data)` - Send notification
      - `render_email_template(string $template, array $data)` - Render template
      - `setup_smtp()` - Configure SMTP settings

21. **IAT_Cron_Manager** - Scheduled tasks
    - File: includes/class-iat-cron-manager.php
    - Purpose: Handle WordPress cron jobs
    - Key Methods:
      - `__construct()` - Register cron hooks
      - `schedule_cron_jobs()` - Schedule all cron jobs
      - `auto_confirm_pending_bookings()` - Auto-confirm bookings
      - `cleanup_old_data()` - Cleanup old data
      - `cleanup_geocache()` - Cleanup geocache

### Import/Export Classes
22. **IAT_Price_Importer** - Price import functionality
    - File: includes/import/class-iat-price-importer.php
    - Purpose: Import pricing data from files
    - Key Methods:
      - `import_from_file(string $filepath)` - Import from file
      - `import_from_string(string $content)` - Import from string
      - `parse_price_list(string $content)` - Parse price list
      - `create_regions_from_price_list(array $parsed)` - Create regions
      - `create_pricings_from_price_list(array $parsed)` - Create pricings

## [Dependencies]
### PHP Requirements
- **PHP**: 8.0 or higher
- **WordPress**: 6.0 or higher
- **MySQL**: 5.7 or higher
- **Required Extensions**: json, mbstring, openssl, pdo_mysql

### PHP Dependencies (Composer)
```json
{
    "require": {
        "php": ">=8.0",
        "composer/installers": "^2.0",
        "ext-json": "*",
        "ext-mbstring": "*",
        "ext-openssl": "*",
        "ext-pdo_mysql": "*"
    },
    "require-dev": {
        "squizlabs/php_codesniffer": "^3.7",
        "wp-coding-standards/wpcs": "^3.0",
        "phpunit/phpunit": "^9.5",
        "phpstan/phpstan": "^1.0"
    }
}
```

### NPM Dependencies
```json
{
    "devDependencies": {
        "@wordpress/scripts": "^26.0.0",
        "webpack": "^5.0.0",
        "webpack-cli": "^5.1.0",
        "css-loader": "^6.8.0",
        "mini-css-extract-plugin": "^2.7.0",
        "sass": "^1.60.0",
        "sass-loader": "^13.0.0"
    },
    "dependencies": {
        "leaflet": "^1.9.0",
        "leaflet.markercluster": "^1.5.0"
    }
}
```

### WordPress Functions Used
- **Admin Interface**: add_menu_page(), add_submenu_page(), add_settings_section(), add_settings_field(), register_setting()
- **Database**: dbDelta(), $wpdb methods, prepare statements
- **Security**: wp_create_nonce(), wp_verify_nonce(), sanitize_text_field(), esc_html()
- **AJAX**: wp_ajax_, wp_ajax_nopriv_ actions
- **Shortcodes**: add_shortcode()
- **Cron**: wp_schedule_event(), wp_cron()
- **Email**: wp_mail(), wp_mail_from(), wp_mail_from_name()

### External APIs
- **reCAPTCHA v3**: Google reCAPTCHA for bot protection
- **Nominatim**: OpenStreetMap free geocoding (1 req/sec limit)
- **Yandex Maps**: Geocoding and autocomplete (25,000 calls/day per key)
- **Google Maps**: Geocoding and autocomplete (3,000 calls/month per key)

### JavaScript Libraries
- **Leaflet.js**: Map display and polygon visualization
- **Leaflet.markercluster**: Marker clustering for admin interface
- **Vanilla JS**: No frameworks, pure ES6+ for frontend

## [Testing]
### Unit Tests
1. **Database Tests** (`tests/unit/database/`)
   - Table creation and structure validation
   - CRUD operations for all entities
   - Foreign key constraints and relationships
   - Index creation and performance

2. **Security Tests** (`tests/unit/security/`)
   - Nonce generation and validation
   - Input sanitization and validation
   - Rate limiting functionality
   - reCAPTCHA integration

3. **Utility Tests** (`tests/unit/utils/`)
   - Booking ID generation uniqueness
   - Token generation security
   - Phone and email validation
   - Date/time formatting

4. **API Tests** (`tests/unit/api/`)
   - Geocoding API responses
   - API rotation logic
   - Rate limiting and failover
   - Cache functionality

### Integration Tests
1. **Admin Interface Tests** (`tests/integration/admin/`)
   - Admin page rendering
   - Form submission handling
   - Region management workflows
   - Pricing management workflows
   - Booking management workflows

2. **Frontend Tests** (`tests/integration/frontend/`)
   - Booking form rendering
   - Multi-step form navigation
   - Address autocomplete
   - Price calculation
   - Return trip functionality

3. **Email Tests** (`tests/integration/email/`)
   - Email template rendering
   - SMTP configuration
   - Notification triggers
   - Email content validation

### End-to-End Tests
1. **Full Booking Flow** (`tests/e2e/`)
   - Complete booking process
   - Email notifications
   - Admin approval workflow
   - Return trip booking
   - Cancellation process

2. **API Integration Tests** (`tests/e2e/api/`)
   - Geocoding accuracy
   - Zone detection precision
   - API failover scenarios
   - Rate limiting behavior

### Test Configuration
```php
// phpunit.xml configuration
<phpunit bootstrap="tests/bootstrap.php">
    <testsuites>
        <testsuite name="Unit Tests">
            <directory>tests/unit</directory>
        </testsuite>
        <testsuite name="Integration Tests">
            <directory>tests/integration</directory>
        </testsuite>
        <testsuite name="End-to-End Tests">
            <directory>tests/e2e</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

### Test Data
- **Mock Regions**: 13 predefined zones with GeoJSON
- **Mock Pricing**: Complete pricing matrix for all zone combinations
- **Mock Bookings**: Various booking scenarios and edge cases
- **Mock API Responses**: Simulated geocoding and zone detection responses

## [Implementation Order]
### Phase 1: Foundation (Week 1)
1. **Project Setup**
   - Create project structure
   - Initialize Composer and NPM
   - Set up development environment
   - Create basic plugin files

2. **Core Plugin Architecture**
   - Implement IAT_Main class
   - Create activation/deactivation system
   - Set up basic WordPress hooks
   - Create plugin header and metadata

3. **Database Foundation**
   - Implement IAT_DB_Manager
   - Create all 7 database tables
   - Set up table relationships and constraints
   - Create basic CRUD methods

### Phase 2: Admin Interface (Week 2)
4. **Admin Framework**
   - Create admin menu structure
   - Implement basic admin page templates
   - Set up admin CSS and JavaScript loading
   - Create admin helper functions

5. **Regions Management**
   - Implement IAT_Admin_Regions
   - Create region CRUD operations
   - Add GeoJSON upload and validation
   - Integrate Leaflet.js map preview

6. **Pricing Management**
   - Implement IAT_Admin_Pricings
   - Create pricing matrix interface
   - Add bulk import functionality
   - Implement price validation

### Phase 3: Core Functionality (Week 3)
7. **Geocoding System**
   - Implement IAT_API_Rotator
   - Create IAT_Nominatim geocoder
   - Add Yandex and Google geocoders
   - Implement API usage tracking

8. **Zone Detection**
   - Implement IAT_Zone_Detector
   - Create point-in-polygon algorithm
   - Add overlap resolution logic
   - Implement caching system

9. **Pricing Engine**
   - Implement IAT_Pricing_Engine
   - Create zone-to-zone pricing lookup
   - Add option pricing integration
   - Implement return trip pricing

### Phase 4: Frontend and Booking (Week 4)
10. **Frontend Framework**
    - Implement IAT_Booking_Form
    - Create multi-step form structure
    - Add frontend CSS and JavaScript
    - Implement form validation

11. **Booking System**
    - Create booking CRUD operations
    - Implement booking state machine
    - Add return trip functionality
    - Create booking notifications

12. **Security Implementation**
    - Implement IAT_Security class
    - Add reCAPTCHA integration
    - Create rate limiting system
    - Add input validation and sanitization

### Phase 5: Advanced Features (Week 5)
13. **Email System**
    - Implement IAT_Email_Manager
    - Create email templates
    - Add SMTP integration
    - Implement notification triggers

14. **Cron Jobs**
    - Implement IAT_Cron_Manager
    - Add auto-confirmation functionality
    - Create cleanup jobs
    - Implement cache management

15. **Service Options**
    - Implement IAT_Admin_Options
    - Add option management interface
    - Create option pricing system
    - Integrate with booking flow

### Phase 6: Polish and Deployment (Week 6)
16. **Testing and Quality Assurance**
    - Write comprehensive unit tests
    - Create integration tests
    - Add end-to-end tests
    - Perform code review and optimization

17. **Documentation and Deployment**
    - Create user documentation
    - Write developer documentation
    - Create installation guide
    - Prepare deployment package

18. **Final Testing and Release**
    - Complete system testing
    - Performance optimization
    - Security audit
    - Prepare for production release

### Parallel Development Opportunities
- **Database and Admin Interface**: Can be developed in parallel
- **Geocoding and Zone Detection**: Can be developed together
- **Frontend and Security**: Can be developed in parallel
- **Email and Cron**: Can be developed together
- **Testing and Documentation**: Can be done throughout development

### Risk Mitigation
- **API Dependencies**: Implement fallback mechanisms early
- **Performance**: Monitor database queries and optimize as needed
- **Security**: Implement security measures throughout development
- **User Experience**: Test admin interface usability regularly
