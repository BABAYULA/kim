<?php
/**
 * IAT Memory Bank
 * 
 * A comprehensive memory management system for the Istanbul Airport Transfer WordPress plugin
 * that stores and retrieves critical configuration, state, and operational data.
 * 
 * @package Istanbul_Airport_Transfer
 * @subpackage Core
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * IAT Memory Bank Class
 * 
 * Singleton class that manages plugin memory including configuration, state, cache, and operational data.
 * Uses WordPress options, transients, and in-memory storage for optimal performance.
 */
class IAT_Memory_Bank {
    
    /**
     * Singleton instance
     * 
     * @var IAT_Memory_Bank
     */
    private static $instance = null;
    
    /**
     * In-memory storage for configuration data
     * 
     * @var array
     */
    private $memory_store = [];
    
    /**
     * In-memory storage for cache data
     * 
     * @var array
     */
    private $cache_store = [];
    
    /**
     * Cache TTL configurations
     * 
     * @var array
     */
    private $cache_config = [
        'geocoding' => 86400,      // 24 hours
        'zone_detection' => 604800, // 7 days
        'pricing_matrix' => 3600,   // 1 hour
        'regions' => 1800,          // 30 minutes
        'api_usage' => 3600,        // 1 hour
    ];
    
    /**
     * Get singleton instance
     * 
     * @return IAT_Memory_Bank
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {
        // Initialize memory bank
        $this->initialize();
    }
    
    /**
     * Initialize memory bank
     */
    private function initialize() {
        // Load configuration from database
        $this->load_all_config();
        
        // Schedule cleanup if not already scheduled
        add_action('init', [$this, 'schedule_cleanup']);
    }
    
    /**
     * Configuration Memory Operations
     */
    
    /**
     * Set configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $value Configuration value
     * @return void
     */
    public function set_config($key, $value) {
        $this->memory_store['config'][$key] = $value;
        update_option('iat_config_' . $key, $value, false);
    }
    
    /**
     * Get configuration value
     * 
     * @param string $key Configuration key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function get_config($key, $default = null) {
        if (isset($this->memory_store['config'][$key])) {
            return $this->memory_store['config'][$key];
        }
        
        $value = get_option('iat_config_' . $key, $default);
        $this->memory_store['config'][$key] = $value;
        return $value;
    }
    
    /**
     * Delete configuration value
     * 
     * @param string $key Configuration key
     * @return bool
     */
    public function delete_config($key) {
        unset($this->memory_store['config'][$key]);
        return delete_option('iat_config_' . $key);
    }
    
    /**
     * State Memory Operations
     */
    
    /**
     * Set state value with transient storage
     * 
     * @param string $key State key
     * @param mixed $value State value
     * @param int $ttl Time to live in seconds (default: 3600)
     * @return void
     */
    public function set_state($key, $value, $ttl = 3600) {
        $this->memory_store['state'][$key] = $value;
        set_transient('iat_state_' . $key, $value, $ttl);
    }
    
    /**
     * Get state value from transient storage
     * 
     * @param string $key State key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function get_state($key, $default = null) {
        if (isset($this->memory_store['state'][$key])) {
            return $this->memory_store['state'][$key];
        }
        
        $value = get_transient('iat_state_' . $key);
        if ($value === false) {
            $value = $default;
        }
        
        $this->memory_store['state'][$key] = $value;
        return $value;
    }
    
    /**
     * Delete state value
     * 
     * @param string $key State key
     * @return bool
     */
    public function delete_state($key) {
        unset($this->memory_store['state'][$key]);
        return delete_transient('iat_state_' . $key);
    }
    
    /**
     * Cache Memory Operations
     */
    
    /**
     * Set cache value with automatic TTL
     * 
     * @param string $key Cache key
     * @param mixed $value Cache value
     * @param string|int $ttl TTL in seconds or cache type for automatic TTL
     * @return void
     */
    public function set_cache($key, $value, $ttl = 'default') {
        // Determine TTL
        if (is_string($ttl) && isset($this->cache_config[$ttl])) {
            $ttl = $this->cache_config[$ttl];
        } elseif ($ttl === 'default') {
            $ttl = 3600; // 1 hour default
        }
        
        $this->cache_store[$key] = $value;
        set_transient('iat_cache_' . $key, $value, $ttl);
    }
    
    /**
     * Get cache value
     * 
     * @param string $key Cache key
     * @return mixed|null
     */
    public function get_cache($key) {
        if (isset($this->cache_store[$key])) {
            return $this->cache_store[$key];
        }
        
        $value = get_transient('iat_cache_' . $key);
        if ($value !== false) {
            $this->cache_store[$key] = $value;
        }
        
        return $value;
    }
    
    /**
     * Delete cache value
     * 
     * @param string $key Cache key
     * @return bool
     */
    public function delete_cache($key) {
        unset($this->cache_store[$key]);
        return delete_transient('iat_cache_' . $key);
    }
    
    /**
     * Operational Memory Operations
     */
    
    /**
     * Increment counter
     * 
     * @param string $key Counter key
     * @param int $amount Amount to increment (default: 1)
     * @return int New counter value
     */
    public function increment_counter($key, $amount = 1) {
        $current = $this->get_operational($key, 0);
        $new_value = $current + $amount;
        $this->set_operational($key, $new_value);
        return $new_value;
    }
    
    /**
     * Set operational value
     * 
     * @param string $key Operational key
     * @param mixed $value Operational value
     * @return void
     */
    public function set_operational($key, $value) {
        $this->memory_store['operational'][$key] = $value;
        update_option('iat_operational_' . $key, $value, false);
    }
    
    /**
     * Get operational value
     * 
     * @param string $key Operational key
     * @param mixed $default Default value if key not found
     * @return mixed
     */
    public function get_operational($key, $default = null) {
        if (isset($this->memory_store['operational'][$key])) {
            return $this->memory_store['operational'][$key];
        }
        
        $value = get_option('iat_operational_' . $key, $default);
        $this->memory_store['operational'][$key] = $value;
        return $value;
    }
    
    /**
     * Delete operational value
     * 
     * @param string $key Operational key
     * @return bool
     */
    public function delete_operational($key) {
        unset($this->memory_store['operational'][$key]);
        return delete_option('iat_operational_' . $key);
    }
    
    /**
     * Bulk Operations
     */
    
    /**
     * Load all configuration from database
     * 
     * @return void
     */
    public function load_all_config() {
        global $wpdb;
        
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('iat_config_') . '%'
            )
        );
        
        foreach ($results as $result) {
            $key = str_replace('iat_config_', '', $result->option_name);
            $this->memory_store['config'][$key] = maybe_unserialize($result->option_value);
        }
    }
    
    /**
     * Clear cache with optional pattern
     * 
     * @param string $pattern Cache key pattern to clear (empty for all)
     * @return void
     */
    public function clear_cache($pattern = '') {
        if (empty($pattern)) {
            $this->cache_store = [];
            $this->clear_transient_pattern('iat_cache_*');
        } else {
            foreach ($this->cache_store as $key => $value) {
                if (strpos($key, $pattern) !== false) {
                    unset($this->cache_store[$key]);
                    delete_transient('iat_cache_' . $key);
                }
            }
        }
    }
    
    /**
     * Clear all state data
     * 
     * @return void
     */
    public function clear_state() {
        $this->memory_store['state'] = [];
        $this->clear_transient_pattern('iat_state_*');
    }
    
    /**
     * Clear all operational data
     * 
     * @return void
     */
    public function clear_operational() {
        $this->memory_store['operational'] = [];
        
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('iat_operational_') . '%'
            )
        );
    }
    
    /**
     * Clear all memory bank data
     * 
     * @return void
     */
    public function clear_all() {
        $this->memory_store = [];
        $this->cache_store = [];
        
        // Clear all transients and options
        $this->clear_transient_pattern('iat_*');
        
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                $wpdb->esc_like('iat_config_') . '%',
                $wpdb->esc_like('iat_operational_') . '%'
            )
        );
    }
    
    /**
     * Utility Methods
     */
    
    /**
     * Clear transient pattern
     * 
     * @param string $pattern Transient pattern to clear
     * @return void
     */
    private function clear_transient_pattern($pattern) {
        global $wpdb;
        $like_pattern = str_replace('*', '%', $pattern);
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $like_pattern
            )
        );
    }
    
    /**
     * Schedule cleanup operations
     * 
     * @return void
     */
    public function schedule_cleanup() {
        if (!wp_next_scheduled('iat_cleanup_memory')) {
            wp_schedule_event(time(), 'hourly', 'iat_cleanup_memory');
        }
        
        add_action('iat_cleanup_memory', [$this, 'cleanup_memory']);
    }
    
    /**
     * Cleanup memory operations
     * 
     * @return void
     */
    public function cleanup_memory() {
        // Clean old cache entries (handled by WordPress transients)
        
        // Clean old state data
        $this->cleanup_old_transients();
        
        // Clean old operational data
        $this->cleanup_old_operational_data();
        
        // Update cleanup timestamp
        $this->set_operational('last_cleanup', current_time('mysql'));
    }
    
    /**
     * Cleanup old transients
     * 
     * @return void
     */
    private function cleanup_old_transients() {
        global $wpdb;
        $expiration_time = time() - (24 * 3600); // 24 hours ago
        
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} 
                 WHERE option_name LIKE %s 
                 AND option_value < %d",
                '_transient_timeout_%',
                $expiration_time
            )
        );
    }
    
    /**
     * Cleanup old operational data
     * 
     * @return void
     */
    private function cleanup_old_operational_data() {
        // Keep only last 30 days of counters
        $current_date = date('Y-m-d');
        $cutoff_date = date('Y-m-d', strtotime('-30 days'));
        
        // Clean daily counters older than 30 days
        $daily_counters = $this->get_operational('daily_counters', []);
        foreach ($daily_counters as $date => $count) {
            if ($date < $cutoff_date) {
                unset($daily_counters[$date]);
            }
        }
        $this->set_operational('daily_counters', $daily_counters);
        
        // Clean monthly counters older than 12 months
        $monthly_counters = $this->get_operational('monthly_counters', []);
        $cutoff_month = date('Y-m', strtotime('-12 months'));
        
        foreach ($monthly_counters as $month => $count) {
            if ($month < $cutoff_month) {
                unset($monthly_counters[$month]);
            }
        }
        $this->set_operational('monthly_counters', $monthly_counters);
    }
    
    /**
     * Get memory bank status
     * 
     * @return array
     */
    public function get_status() {
        return [
            'config_size' => count($this->memory_store['config'] ?? []),
            'state_size' => count($this->memory_store['state'] ?? []),
            'cache_size' => count($this->cache_store),
            'operational_size' => count($this->memory_store['operational'] ?? []),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true),
            'last_cleanup' => $this->get_operational('last_cleanup', 'Never'),
        ];
    }
    
    /**
     * Log memory usage
     * 
     * @return void
     */
    public function log_usage() {
        $status = $this->get_status();
        
        error_log('IAT Memory Bank Status: ' . json_encode([
            'timestamp' => current_time('mysql'),
            'status' => $status
        ]));
    }
    
    /**
     * Reset memory bank (for testing)
     * 
     * @return void
     */
    public function reset() {
        $this->clear_all();
        $this->load_all_config();
    }
}