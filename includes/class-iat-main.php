<?php
/**
 * IAT Main
 *
 * @package     Istanbul_Airport_Transfer
 * @subpackage  Core
 * @since       1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * IAT Main Class
 *
 * Main class that initializes and manages the plugin.
 * Uses singleton pattern to ensure only one instance exists.
 */
class IAT_Main {

    /**
     * Plugin instance
     *
     * @var IAT_Main
     */
    private static $instance = null;

    /**
     * Plugin version
     *
     * @var string
     */
    private $version;

    /**
     * Constructor
     */
    private function __construct() {
        $this->version = IAT_VERSION;
        $this->init();
    }

    /**
     * Get singleton instance
     *
     * @return IAT_Main
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize plugin
     */
    private function init() {
        // Load plugin textdomain
        add_action('init', [$this, 'load_textdomain']);

        // Initialize all plugin modules
        add_action('init', [$this, 'init_modules'], 20);

        // Initialize frontend
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts']);

        // Initialize admin
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('admin_menu', [$this, 'add_admin_menu']);

        // Initialize AJAX handlers
        $this->init_ajax_handlers();

        // Initialize shortcodes
        $this->init_shortcodes();
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'istanbul-airport-transfer',
            false,
            dirname(plugin_basename(IAT_PLUGIN_FILE)) . '/languages'
        );
    }

    /**
     * Initialize plugin modules
     */
    public function init_modules() {
        // Initialize database manager
        $this->init_database_manager();

        // Initialize security
        $this->init_security();

        // Initialize geocoding
        $this->init_geocoding();

        // Initialize zone detection
        $this->init_zone_detection();

        // Initialize pricing engine
        $this->init_pricing_engine();

        // Initialize booking system
        $this->init_booking_system();

        // Initialize email manager
        $this->init_email_manager();

        // Initialize cron manager
        $this->init_cron_manager();
    }

    /**
     * Initialize database manager
     */
    private function init_database_manager() {
        if (class_exists('IAT_DB_Manager')) {
            IAT_DB_Manager::get_instance();
        }
    }

    /**
     * Initialize security
     */
    private function init_security() {
        if (class_exists('IAT_Security')) {
            IAT_Security::get_instance();
        }
    }

    /**
     * Initialize geocoding
     */
    private function init_geocoding() {
        if (class_exists('IAT_API_Rotator')) {
            IAT_API_Rotator::get_instance();
        }
    }

    /**
     * Initialize zone detection
     */
    private function init_zone_detection() {
        if (class_exists('IAT_Zone_Detector')) {
            IAT_Zone_Detector::get_instance();
        }
    }

    /**
     * Initialize pricing engine
     */
    private function init_pricing_engine() {
        if (class_exists('IAT_Pricing_Engine')) {
            IAT_Pricing_Engine::get_instance();
        }
    }

    /**
     * Initialize booking system
     */
    private function init_booking_system() {
        if (class_exists('IAT_Booking_Form')) {
            IAT_Booking_Form::get_instance();
        }
    }

    /**
     * Initialize email manager
     */
    private function init_email_manager() {
        if (class_exists('IAT_Email_Manager')) {
            IAT_Email_Manager::get_instance();
        }
    }

    /**
     * Initialize cron manager
     */
    private function init_cron_manager() {
        if (class_exists('IAT_Cron_Manager')) {
            IAT_Cron_Manager::get_instance();
        }
    }

    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        wp_enqueue_style(
            'iat-frontend-style',
            IAT_PLUGIN_URL . 'assets/css/public-booking.css',
            [],
            $this->version
        );

        wp_enqueue_script(
            'iat-frontend-script',
            IAT_PLUGIN_URL . 'assets/js/public-booking-form.js',
            ['jquery'],
            $this->version,
            true
        );

        // Localize script with AJAX URL
        wp_localize_script('iat-frontend-script', 'iat_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('iat_nonce')
        ]);
    }

    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'ist-an-hava-tasima') !== false) {
            wp_enqueue_style(
                'iat-admin-style',
                IAT_PLUGIN_URL . 'assets/css/admin-styles.css',
                [],
                $this->version
            );

            wp_enqueue_script(
                'iat-admin-script',
                IAT_PLUGIN_URL . 'assets/js/admin-main.js',
                ['jquery'],
                $this->version,
                true
            );
        }
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            __('Istanbul Airport Transfer', 'istanbul-airport-transfer'),
            __('Istanbul Airport Transfer', 'istanbul-airport-transfer'),
            'manage_options',
            'ist-an-hava-tasima',
            [$this, 'admin_dashboard'],
            'dashicons-location-alt',
            30
        );

        add_submenu_page(
            'ist-an-hava-tasima',
            __('Bookings', 'istanbul-airport-transfer'),
            __('Bookings', 'istanbul-airport-transfer'),
            'manage_options',
            'iat-bookings',
            [$this, 'admin_bookings']
        );

        add_submenu_page(
            'ist-an-hava-tasima',
            __('Zones', 'istanbul-airport-transfer'),
            __('Zones', 'istanbul-airport-transfer'),
            'manage_options',
            'iat-zones',
            [$this, 'admin_zones']
        );

        add_submenu_page(
            'ist-an-hava-tasima',
            __('Pricing', 'istanbul-airport-transfer'),
            __('Pricing', 'istanbul-airport-transfer'),
            'manage_options',
            'iat-pricing',
            [$this, 'admin_pricing']
        );

        add_submenu_page(
            'ist-an-hava-tasima',
            __('Settings', 'istanbul-airport-transfer'),
            __('Settings', 'istanbul-airport-transfer'),
            'manage_options',
            'iat-settings',
            [$this, 'admin_settings']
        );
    }

    /**
     * Admin dashboard
     */
    public function admin_dashboard() {
        include IAT_PLUGIN_DIR . 'admin/views/dashboard.php';
    }

    /**
     * Admin bookings page
     */
    public function admin_bookings() {
        include IAT_PLUGIN_DIR . 'admin/views/bookings.php';
    }

    /**
     * Admin zones page
     */
    public function admin_zones() {
        include IAT_PLUGIN_DIR . 'admin/views/zones.php';
    }

    /**
     * Admin pricing page
     */
    public function admin_pricing() {
        include IAT_PLUGIN_DIR . 'admin/views/pricing.php';
    }

    /**
     * Admin settings page
     */
    public function admin_settings() {
        include IAT_PLUGIN_DIR . 'admin/views/settings.php';
    }

    /**
     * Initialize AJAX handlers
     */
    private function init_ajax_handlers() {
        // Frontend AJAX handlers
        add_action('wp_ajax_iat_get_quote', [$this, 'handle_get_quote']);
        add_action('wp_ajax_nopriv_iat_get_quote', [$this, 'handle_get_quote']);
        
        add_action('wp_ajax_iat_make_booking', [$this, 'handle_make_booking']);
        add_action('wp_ajax_nopriv_iat_make_booking', [$this, 'handle_make_booking']);
        
        add_action('wp_ajax_iat_geocode_address', [$this, 'handle_geocode_address']);
        add_action('wp_ajax_nopriv_iat_geocode_address', [$this, 'handle_geocode_address']);

        // Admin AJAX handlers
        add_action('wp_ajax_iat_save_zone', [$this, 'handle_save_zone']);
        add_action('wp_ajax_iat_delete_zone', [$this, 'handle_delete_zone']);
        add_action('wp_ajax_iat_save_pricing', [$this, 'handle_save_pricing']);
        add_action('wp_ajax_iat_delete_pricing', [$this, 'handle_delete_pricing']);
    }

    /**
     * Initialize shortcodes
     */
    private function init_shortcodes() {
        if (class_exists('IAT_Shortcodes')) {
            IAT_Shortcodes::init();
        }
    }

    /**
     * Handle get quote AJAX
     */
    public function handle_get_quote() {
        // Implementation will be added later
        wp_die(__('Method not implemented yet.', 'istanbul-airport-transfer'));
    }

    /**
     * Handle make booking AJAX
     */
    public function handle_make_booking() {
        // Implementation will be added later
        wp_die(__('Method not implemented yet.', 'istanbul-airport-transfer'));
    }

    /**
     * Handle geocode address AJAX
     */
    public function handle_geocode_address() {
        // Implementation will be added later
        wp_die(__('Method not implemented yet.', 'istanbul-airport-transfer'));
    }

    /**
     * Handle save zone AJAX
     */
    public function handle_save_zone() {
        // Implementation will be added later
        wp_die(__('Method not implemented yet.', 'istanbul-airport-transfer'));
    }

    /**
     * Handle delete zone AJAX
     */
    public function handle_delete_zone() {
        // Implementation will be added later
        wp_die(__('Method not implemented yet.', 'istanbul-airport-transfer'));
    }

    /**
     * Handle save pricing AJAX
     */
    public function handle_save_pricing() {
        // Implementation will be added later
        wp_die(__('Method not implemented yet.', 'istanbul-airport-transfer'));
    }

    /**
     * Handle delete pricing AJAX
     */
    public function handle_delete_pricing() {
        // Implementation will be added later
        wp_die(__('Method not implemented yet.', 'istanbul-airport-transfer'));
    }

    /**
     * Get plugin version
     *
     * @return string
     */
    public function get_version() {
        return $this->version;
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