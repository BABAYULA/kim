<?php
/**
 * IAT Activator
 *
 * @package     Istanbul_Airport_Transfer
 * @subpackage  Core
 * @since       1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * IAT Activator Class
 *
 * Handles plugin activation tasks.
 */
class IAT_Activator {

    /**
     * Activate the plugin
     */
    public static function activate() {
        // Check minimum PHP version
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            deactivate_plugins(plugin_basename(IAT_PLUGIN_FILE));
            wp_die(__('Istanbul Airport Transfer requires PHP 8.0 or higher.', 'istanbul-airport-transfer'));
        }

        // Check minimum WordPress version
        if (version_compare(get_bloginfo('version'), '6.0', '<')) {
            deactivate_plugins(plugin_basename(IAT_PLUGIN_FILE));
            wp_die(__('Istanbul Airport Transfer requires WordPress 6.0 or higher.', 'istanbul-airport-transfer'));
        }

        // Check database version and run migrations if needed
        $installed_ver = get_option('iat_db_version', '0.0.0');
        if (version_compare($installed_ver, IAT_VERSION, '<')) {
            self::create_database_tables();
            self::run_migrations($installed_ver);
            update_option('iat_db_version', IAT_VERSION);
        }

        // Set up default options
        self::set_default_options();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create database tables
     */
    private static function create_database_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Regions table
        $regions_table = $wpdb->prefix . 'iat_regions';
        $regions_sql = "CREATE TABLE $regions_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            region_name varchar(100) NOT NULL,
            zone_code varchar(50) UNIQUE NOT NULL,
            zone_type enum('european', 'anatolian', 'airport') NOT NULL,
            geojson text NOT NULL,
            base_price_intra decimal(10,2) DEFAULT 0.00,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY zone_code (zone_code)
        ) $charset_collate;";

        // Pricing table
        $pricings_table = $wpdb->prefix . 'iat_pricings';
        $pricings_sql = "CREATE TABLE $pricings_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            from_zone_code varchar(50) NOT NULL,
            to_zone_code varchar(50) NOT NULL,
            price_eur decimal(10,2) NOT NULL,
            is_bidirectional tinyint(1) DEFAULT 1,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY zone_pair (from_zone_code, to_zone_code),
            KEY from_zone (from_zone_code),
            KEY to_zone (to_zone_code)
        ) $charset_collate;";

        // Bookings table
        $bookings_table = $wpdb->prefix . 'iat_bookings';
        $bookings_sql = "CREATE TABLE $bookings_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            booking_id varchar(32) UNIQUE NOT NULL,
            status enum('pending', 'confirmed', 'auto_confirmed', 'cancelled') DEFAULT 'pending',
            linked_booking_id bigint(20) unsigned NULL,
            is_return_trip tinyint(1) DEFAULT 0,
            pickup_address text NOT NULL,
            pickup_lat decimal(10,7),
            pickup_lng decimal(10,7),
            pickup_zone_code varchar(50),
            dropoff_address text NOT NULL,
            dropoff_lat decimal(10,7),
            dropoff_lng decimal(10,7),
            dropoff_zone_code varchar(50),
            pickup_datetime datetime NOT NULL,
            flight_code varchar(10),
            has_tv_option tinyint(1) DEFAULT 0,
            passenger_count tinyint DEFAULT 1,
            luggage_count tinyint DEFAULT 1,
            passenger_names json,
            contact_phone varchar(20) NOT NULL,
            contact_email varchar(100) NOT NULL,
            price_eur decimal(10,2) NOT NULL,
            currency varchar(3) DEFAULT 'EUR',
            cancellation_token varchar(64),
            auto_confirm_deadline datetime,
            recaptcha_score decimal(3,2),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY booking_id (booking_id),
            KEY status (status),
            KEY pickup_datetime (pickup_datetime),
            KEY pickup_zone_code (pickup_zone_code),
            KEY dropoff_zone_code (dropoff_zone_code),
            KEY linked_booking_id (linked_booking_id)
        ) $charset_collate;";

        // Options table
        $options_table = $wpdb->prefix . 'iat_options';
        $options_sql = "CREATE TABLE $options_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            option_name varchar(100) NOT NULL,
            option_slug varchar(50) UNIQUE NOT NULL,
            price_eur decimal(10,2) NOT NULL DEFAULT 0,
            description text,
            is_active tinyint(1) DEFAULT 1,
            sort_order int DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY option_slug (option_slug),
            KEY is_active (is_active)
        ) $charset_collate;";

        // Booking options table
        $booking_options_table = $wpdb->prefix . 'iat_booking_options';
        $booking_options_sql = "CREATE TABLE $booking_options_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) unsigned NOT NULL,
            option_id bigint(20) unsigned NOT NULL,
            option_price_eur decimal(10,2) NOT NULL,
            PRIMARY KEY (id),
            KEY booking_id (booking_id),
            KEY option_id (option_id)
        ) $charset_collate;";

        // API usage tracking table
        $api_usage_table = $wpdb->prefix . 'iat_api_usage';
        $api_usage_sql = "CREATE TABLE $api_usage_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            api_provider varchar(20),
            api_key_index tinyint,
            call_date date,
            call_count int DEFAULT 0,
            monthly_count int DEFAULT 0,
            last_call_time datetime,
            PRIMARY KEY (id),
            UNIQUE KEY provider_key_date (api_provider, api_key_index, call_date),
            KEY api_provider (api_provider),
            KEY call_date (call_date)
        ) $charset_collate;";

        // Geocaching table
        $geocache_table = $wpdb->prefix . 'iat_geocache';
        $geocache_sql = "CREATE TABLE $geocache_table (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            address_hash varchar(64),
            lat decimal(10,7),
            lng decimal(10,7),
            formatted_address text,
            zone_code varchar(50),
            created_at datetime,
            updated_at datetime,
            PRIMARY KEY (id),
            UNIQUE KEY address_hash (address_hash),
            KEY zone_code (zone_code)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($regions_sql);
        dbDelta($pricings_sql);
        dbDelta($bookings_sql);
        dbDelta($options_sql);
        dbDelta($booking_options_sql);
        dbDelta($api_usage_sql);
        dbDelta($geocache_sql);

        // Update database version
        update_option('iat_db_version', '1.0.0');
    }

    /**
     * Set default options
     */
    private static function set_default_options() {
        // Set default plugin options
        $default_options = [
            'min_advance_hours' => 24,
            'max_passengers' => 5,
            'max_luggage' => 5,
            'currency' => 'EUR',
            'admin_email' => get_option('admin_email'),
            'whatsapp_number' => '',
            'phone_number' => '',
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
            'yandex_api_keys' => [],
            'google_api_keys' => [],
            'nominatim_enabled' => true,
        ];

        foreach ($default_options as $key => $value) {
            if (get_option('iat_' . $key) === false) {
                update_option('iat_' . $key, $value);
            }
        }
    }

    /**
     * Run database migrations from old version to current version
     *
     * This method handles incremental database schema changes between versions.
     * dbDelta() handles additions safely, but this method is for column renames,
     * drops, and data transformations that require manual migration.
     *
     * @param string $installed_ver Previously installed database version
     */
    private static function run_migrations($installed_ver) {
        global $wpdb;

        // Example: Migration from 1.0.0 to 1.1.0
        // if (version_compare($installed_ver, '1.1.0', '<')) {
        //     self::migrate_1_0_0_to_1_1_0();
        // }

        // Add future migrations below in version order
        // Each migration should be idempotent (safe to run multiple times)
    }

    /**
     * Example migration method - keep as template for future use
     *
     * @deprecated Keep as template for future migrations
     */
    private static function migrate_1_0_0_to_1_1_0() {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
        // Safe: Migration operation on existing column
        $wpdb->query(
            "ALTER TABLE {$wpdb->prefix}iat_bookings
             MODIFY COLUMN status ENUM('pending', 'confirmed', 'auto_confirmed', 'cancelled', 'completed')"
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
        // Safe: Adding index to improve query performance
        $wpdb->query(
            "ALTER TABLE {$wpdb->prefix}iat_bookings
             ADD INDEX created_at (created_at)"
        );
    }
}
