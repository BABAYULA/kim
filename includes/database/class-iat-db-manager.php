<?php
/**
 * IAT Database Manager
 *
 * @package     Istanbul_Airport_Transfer
 * @subpackage  Database
 * @since       1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * IAT Database Manager Class
 *
 * Handles all database operations for the plugin.
 */
class IAT_DB_Manager {

    /**
     * Instance of the class
     *
     * @var IAT_DB_Manager
     */
    private static $instance = null;

    /**
     * Database table prefix
     *
     * @var string
     */
    private $table_prefix;

    /**
     * Constructor
     */
    private function __construct() {
        global $wpdb;
        $this->table_prefix = $wpdb->prefix . 'iat_';
        $this->init_hooks();
    }

    /**
     * Get instance of the class
     *
     * @return IAT_DB_Manager
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize when needed
    }

    /**
     * Get full table name
     *
     * @param string $table Table name without prefix
     * @return string Full table name with prefix
     */
    public function get_table_name($table) {
        return $this->table_prefix . $table;
    }

    /**
     * Get regions table name
     *
     * @return string
     */
    public function get_regions_table() {
        return $this->get_table_name('regions');
    }

    /**
     * Get pricings table name
     *
     * @return string
     */
    public function get_pricings_table() {
        return $this->get_table_name('pricings');
    }

    /**
     * Get bookings table name
     *
     * @return string
     */
    public function get_bookings_table() {
        return $this->get_table_name('bookings');
    }

    /**
     * Get options table name
     *
     * @return string
     */
    public function get_options_table() {
        return $this->get_table_name('options');
    }

    /**
     * Get booking options table name
     *
     * @return string
     */
    public function get_booking_options_table() {
        return $this->get_table_name('booking_options');
    }

    /**
     * Get API usage table name
     *
     * @return string
     */
    public function get_api_usage_table() {
        return $this->get_table_name('api_usage');
    }

    /**
     * Get geocache table name
     *
     * @return string
     */
    public function get_geocache_table() {
        return $this->get_table_name('geocache');
    }

    /**
     * REGION CRUD OPERATIONS
     */

    /**
     * Create a new region
     *
     * @param array $data Region data
     * @return int|false Region ID on success, false on failure
     */
    public function create_region($data) {
        global $wpdb;

        $defaults = [
            'region_name' => '',
            'zone_code' => '',
            'zone_type' => 'european',
            'geojson' => '',
            'base_price_intra' => 0.00
        ];

        $data = wp_parse_args($data, $defaults);

        $result = $wpdb->insert(
            $this->get_regions_table(),
            [
                'region_name' => sanitize_text_field($data['region_name']),
                'zone_code' => sanitize_text_field($data['zone_code']),
                'zone_type' => sanitize_text_field($data['zone_type']),
                'geojson' => wp_kses_post($data['geojson']), // Allow some HTML for GeoJSON
                'base_price_intra' => floatval($data['base_price_intra'])
            ],
            ['%s', '%s', '%s', '%s', '%f']
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get region by ID
     *
     * @param int $id Region ID
     * @return object|false Region object on success, false on failure
     */
    public function get_region_by_id($id) {
        global $wpdb;

        $region = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->get_regions_table()} WHERE id = %d",
                $id
            )
        );

        return $region ?: false;
    }

    /**
     * Get region by zone code
     *
     * @param string $zone_code Zone code
     * @return object|false Region object on success, false on failure
     */
    public function get_region_by_code($zone_code) {
        global $wpdb;

        $region = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->get_regions_table()} WHERE zone_code = %s",
                sanitize_text_field($zone_code)
            )
        );

        return $region ?: false;
    }

    /**
     * Get all regions
     *
     * @return array Array of region objects
     */
    public function get_all_regions() {
        global $wpdb;

        $regions = $wpdb->get_results("SELECT * FROM {$this->get_regions_table()} ORDER BY region_name ASC");

        return $regions ?: [];
    }

    /**
     * Update region
     *
     * @param int $id Region ID
     * @param array $data Region data to update
     * @return bool True on success, false on failure
     */
    public function update_region($id, $data) {
        global $wpdb;

        $allowed_fields = ['region_name', 'zone_code', 'zone_type', 'geojson', 'base_price_intra'];
        $update_data = [];

        foreach ($data as $field => $value) {
            if (in_array($field, $allowed_fields)) {
                switch ($field) {
                    case 'region_name':
                    case 'zone_code':
                    case 'zone_type':
                        $update_data[$field] = sanitize_text_field($value);
                        break;
                    case 'geojson':
                        $update_data[$field] = wp_kses_post($value);
                        break;
                    case 'base_price_intra':
                        $update_data[$field] = floatval($value);
                        break;
                }
            }
        }

        if (empty($update_data)) {
            return false;
        }

        $result = $wpdb->update(
            $this->get_regions_table(),
            $update_data,
            ['id' => $id],
            null,
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Delete region
     *
     * @param int $id Region ID
     * @return bool True on success, false on failure
     */
    public function delete_region($id) {
        global $wpdb;

        $result = $wpdb->delete(
            $this->get_regions_table(),
            ['id' => $id],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * PRICING CRUD OPERATIONS
     */

    /**
     * Create a new pricing entry
     *
     * @param array $data Pricing data
     * @return int|false Pricing ID on success, false on failure
     */
    public function create_pricing($data) {
        global $wpdb;

        $defaults = [
            'from_zone_code' => '',
            'to_zone_code' => '',
            'price_eur' => 0.00,
            'is_bidirectional' => 1
        ];

        $data = wp_parse_args($data, $defaults);

        $result = $wpdb->insert(
            $this->get_pricings_table(),
            [
                'from_zone_code' => sanitize_text_field($data['from_zone_code']),
                'to_zone_code' => sanitize_text_field($data['to_zone_code']),
                'price_eur' => floatval($data['price_eur']),
                'is_bidirectional' => intval($data['is_bidirectional'])
            ],
            ['%s', '%s', '%f', '%d']
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get pricing by zone codes
     *
     * @param string $from_zone From zone code
     * @param string $to_zone To zone code
     * @return object|false Pricing object on success, false on failure
     */
    public function get_pricing($from_zone, $to_zone) {
        global $wpdb;

        $pricing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->get_pricings_table()} 
                 WHERE (from_zone_code = %s AND to_zone_code = %s) 
                 OR (from_zone_code = %s AND to_zone_code = %s AND is_bidirectional = 1)",
                sanitize_text_field($from_zone),
                sanitize_text_field($to_zone),
                sanitize_text_field($to_zone),
                sanitize_text_field($from_zone)
            )
        );

        return $pricing ?: false;
    }

    /**
     * Get all pricings
     *
     * @return array Array of pricing objects
     */
    public function get_all_pricings() {
        global $wpdb;

        $pricings = $wpdb->get_results("SELECT * FROM {$this->get_pricings_table()}");

        return $pricings ?: [];
    }

    /**
     * BOOKING CRUD OPERATIONS
     */

    /**
     * Create a new booking
     *
     * @param array $data Booking data
     * @return int|false Booking ID on success, false on failure
     */
    public function create_booking($data) {
        global $wpdb;

        $defaults = [
            'booking_id' => $this->generate_booking_id(),
            'status' => 'pending',
            'linked_booking_id' => null,
            'is_return_trip' => 0,
            'pickup_address' => '',
            'pickup_lat' => null,
            'pickup_lng' => null,
            'pickup_zone_code' => null,
            'dropoff_address' => '',
            'dropoff_lat' => null,
            'dropoff_lng' => null,
            'dropoff_zone_code' => null,
            'pickup_datetime' => current_time('mysql'),
            'flight_code' => '',
            'has_tv_option' => 0,
            'passenger_count' => 1,
            'luggage_count' => 1,
            'passenger_names' => null,
            'contact_phone' => '',
            'contact_email' => '',
            'price_eur' => 0.00,
            'currency' => 'EUR',
            'cancellation_token' => wp_generate_password(32, false),
            'auto_confirm_deadline' => date('Y-m-d H:i:s', strtotime('+24 hours')),
            'recaptcha_score' => null
        ];

        $data = wp_parse_args($data, $defaults);

        $result = $wpdb->insert(
            $this->get_bookings_table(),
            [
                'booking_id' => sanitize_text_field($data['booking_id']),
                'status' => sanitize_text_field($data['status']),
                'linked_booking_id' => $data['linked_booking_id'] ? intval($data['linked_booking_id']) : null,
                'is_return_trip' => intval($data['is_return_trip']),
                'pickup_address' => sanitize_textarea_field($data['pickup_address']),
                'pickup_lat' => $data['pickup_lat'] ? floatval($data['pickup_lat']) : null,
                'pickup_lng' => $data['pickup_lng'] ? floatval($data['pickup_lng']) : null,
                'pickup_zone_code' => $data['pickup_zone_code'] ? sanitize_text_field($data['pickup_zone_code']) : null,
                'dropoff_address' => sanitize_textarea_field($data['dropoff_address']),
                'dropoff_lat' => $data['dropoff_lat'] ? floatval($data['dropoff_lat']) : null,
                'dropoff_lng' => $data['dropoff_lng'] ? floatval($data['dropoff_lng']) : null,
                'dropoff_zone_code' => $data['dropoff_zone_code'] ? sanitize_text_field($data['dropoff_zone_code']) : null,
                'pickup_datetime' => sanitize_text_field($data['pickup_datetime']),
                'flight_code' => sanitize_text_field($data['flight_code']),
                'has_tv_option' => intval($data['has_tv_option']),
                'passenger_count' => intval($data['passenger_count']),
                'luggage_count' => intval($data['luggage_count']),
                'passenger_names' => $data['passenger_names'] ? wp_json_encode($data['passenger_names']) : null,
                'contact_phone' => sanitize_text_field($data['contact_phone']),
                'contact_email' => sanitize_email($data['contact_email']),
                'price_eur' => floatval($data['price_eur']),
                'currency' => sanitize_text_field($data['currency']),
                'cancellation_token' => sanitize_text_field($data['cancellation_token']),
                'auto_confirm_deadline' => sanitize_text_field($data['auto_confirm_deadline']),
                'recaptcha_score' => $data['recaptcha_score'] ? floatval($data['recaptcha_score']) : null
            ],
            [
                '%s', '%s', '%d', '%d', '%s', '%f', '%f', '%s', '%s', '%f', '%f', '%s', '%s', '%s', '%d', '%d', '%d', '%s', '%s', '%s', '%f', '%s', '%s', '%s', '%f'
            ]
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get booking by ID
     *
     * @param int $id Booking ID
     * @return object|false Booking object on success, false on failure
     */
    public function get_booking_by_id($id) {
        global $wpdb;

        $booking = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->get_bookings_table()} WHERE id = %d",
                $id
            )
        );

        if ($booking && $booking->passenger_names) {
            $booking->passenger_names = json_decode($booking->passenger_names, true);
        }

        return $booking ?: false;
    }

    /**
     * Get booking by booking ID
     *
     * @param string $booking_id Booking ID string
     * @return object|false Booking object on success, false on failure
     */
    public function get_booking_by_booking_id($booking_id) {
        global $wpdb;

        $booking = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->get_bookings_table()} WHERE booking_id = %s",
                sanitize_text_field($booking_id)
            )
        );

        if ($booking && $booking->passenger_names) {
            $booking->passenger_names = json_decode($booking->passenger_names, true);
        }

        return $booking ?: false;
    }

    /**
     * Update booking status
     *
     * @param int $id Booking ID
     * @param string $status New status
     * @return bool True on success, false on failure
     */
    public function update_booking_status($id, $status) {
        global $wpdb;

        $valid_statuses = ['pending', 'confirmed', 'auto_confirmed', 'cancelled'];
        if (!in_array($status, $valid_statuses)) {
            return false;
        }

        $result = $wpdb->update(
            $this->get_bookings_table(),
            ['status' => sanitize_text_field($status)],
            ['id' => $id],
            ['%s'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * OPTION CRUD OPERATIONS
     */

    /**
     * Create a new option
     *
     * @param array $data Option data
     * @return int|false Option ID on success, false on failure
     */
    public function create_option($data) {
        global $wpdb;

        $defaults = [
            'option_name' => '',
            'option_slug' => '',
            'price_eur' => 0.00,
            'description' => '',
            'is_active' => 1,
            'sort_order' => 0
        ];

        $data = wp_parse_args($data, $defaults);

        $result = $wpdb->insert(
            $this->get_options_table(),
            [
                'option_name' => sanitize_text_field($data['option_name']),
                'option_slug' => sanitize_text_field($data['option_slug']),
                'price_eur' => floatval($data['price_eur']),
                'description' => sanitize_textarea_field($data['description']),
                'is_active' => intval($data['is_active']),
                'sort_order' => intval($data['sort_order'])
            ],
            ['%s', '%s', '%f', '%s', '%d', '%d']
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * Get all active options
     *
     * @return array Array of active option objects
     */
    public function get_active_options() {
        global $wpdb;

        $options = $wpdb->get_results(
            "SELECT * FROM {$this->get_options_table()} 
             WHERE is_active = 1 
             ORDER BY sort_order ASC, option_name ASC"
        );

        return $options ?: [];
    }

    /**
     * API USAGE CRUD OPERATIONS
     */

    /**
     * Log API usage
     *
     * @param string $provider API provider
     * @param int $key_index API key index
     * @return bool True on success, false on failure
     */
    public function log_api_usage($provider, $key_index) {
        global $wpdb;

        $today = current_time('Y-m-d');
        $current_time = current_time('mysql');

        // Check if record exists for today
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id FROM {$this->get_api_usage_table()} 
                 WHERE api_provider = %s AND api_key_index = %d AND call_date = %s",
                sanitize_text_field($provider),
                intval($key_index),
                sanitize_text_field($today)
            )
        );

        if ($existing) {
            // Update existing record
            $result = $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$this->get_api_usage_table()} 
                     SET call_count = call_count + 1, 
                         monthly_count = monthly_count + 1,
                         last_call_time = %s
                     WHERE id = %d",
                    sanitize_text_field($current_time),
                    $existing->id
                )
            );
        } else {
            // Insert new record
            $result = $wpdb->insert(
                $this->get_api_usage_table(),
                [
                    'api_provider' => sanitize_text_field($provider),
                    'api_key_index' => intval($key_index),
                    'call_date' => sanitize_text_field($today),
                    'call_count' => 1,
                    'monthly_count' => 1,
                    'last_call_time' => sanitize_text_field($current_time)
                ],
                ['%s', '%d', '%s', '%d', '%d', '%s']
            );
        }

        return $result !== false;
    }

    /**
     * GEOCACHE CRUD OPERATIONS
     */

    /**
     * Set geocache entry
     *
     * @param string $address Address to cache
     * @param array $data Geocoding result data
     * @return bool True on success, false on failure
     */
    public function set_geocache($address, $data) {
        global $wpdb;

        $address_hash = md5($address);
        $current_time = current_time('mysql');

        $result = $wpdb->replace(
            $this->get_geocache_table(),
            [
                'address_hash' => $address_hash,
                'lat' => floatval($data['lat']),
                'lng' => floatval($data['lng']),
                'formatted_address' => sanitize_textarea_field($data['formatted_address']),
                'zone_code' => sanitize_text_field($data['zone_code']),
                'created_at' => $current_time,
                'updated_at' => $current_time
            ],
            ['%s', '%f', '%f', '%s', '%s', '%s', '%s']
        );

        return $result !== false;
    }

    /**
     * Get geocache entry
     *
     * @param string $address Address to look up
     * @return array|false Cached data on success, false on failure
     */
    public function get_geocache($address) {
        global $wpdb;

        $address_hash = md5($address);

        $cached = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->get_geocache_table()} WHERE address_hash = %s",
                $address_hash
            )
        );

        if (!$cached) {
            return false;
        }

        return [
            'lat' => floatval($cached->lat),
            'lng' => floatval($cached->lng),
            'formatted_address' => $cached->formatted_address,
            'zone_code' => $cached->zone_code
        ];
    }

    /**
     * Generate unique booking ID
     *
     * @return string Unique booking ID
     */
    private function generate_booking_id() {
        return 'IAT' . date('Ymd') . substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserializing
     */
    public function __wakeup() {
        throw new Exception('Cannot unserialize singleton');
    }
}