# Memory Bank System

## Overview
A comprehensive memory management system for the Istanbul Airport Transfer WordPress plugin that stores and retrieves critical configuration, state, and operational data.

## Memory Bank Structure

### 1. Configuration Memory
Stores plugin configuration that needs to persist across requests and sessions.

```php
// Configuration keys and their purposes
const CONFIG_KEYS = [
    'api_keys' => [
        'nominatim' => 'Free geocoding API keys',
        'yandex' => 'Yandex Maps API keys (10 keys)',
        'google' => 'Google Maps API keys (10 keys)',
        'recaptcha' => 'reCAPTCHA v3 keys'
    ],
    'contact_info' => [
        'admin_email' => 'Admin notification email',
        'whatsapp_number' => 'WhatsApp contact number',
        'phone_number' => 'Phone contact number'
    ],
    'booking_settings' => [
        'min_advance_hours' => 'Minimum advance booking time',
        'max_passengers' => 'Maximum passengers per booking',
        'max_luggage' => 'Maximum luggage per booking',
        'currency' => 'Default currency (EUR)'
    ]
];
```

### 2. State Memory
Tracks the current state of operations and user sessions.

```php
// State tracking keys
const STATE_KEYS = [
    'api_usage' => [
        'provider' => 'Current API provider in use',
        'call_count' => 'Daily API call count',
        'monthly_count' => 'Monthly API call count',
        'last_call_time' => 'Timestamp of last API call'
    ],
    'user_session' => [
        'booking_step' => 'Current step in booking process',
        'form_data' => 'Temporary form data',
        'geocoding_results' => 'Cached geocoding results'
    ],
    'system_status' => [
        'maintenance_mode' => 'System maintenance status',
        'api_health' => 'API provider health status',
        'last_cleanup' => 'Last cleanup operation timestamp'
    ]
];
```

### 3. Cache Memory
Temporary storage for frequently accessed data to improve performance.

```php
// Cache keys and TTL values
const CACHE_KEYS = [
    'geocoding' => [
        'ttl' => 86400, // 24 hours
        'description' => 'Geocoding results cache'
    ],
    'zone_detection' => [
        'ttl' => 604800, // 7 days
        'description' => 'Zone detection results cache'
    ],
    'pricing_matrix' => [
        'ttl' => 3600, // 1 hour
        'description' => 'Pricing matrix cache'
    ],
    'regions' => [
        'ttl' => 1800, // 30 minutes
        'description' => 'Regions data cache'
    ]
];
```

### 4. Operational Memory
Stores operational data needed for plugin functionality.

```php
// Operational data keys
const OPERATIONAL_KEYS = [
    'booking_counters' => [
        'daily_bookings' => 'Daily booking count',
        'monthly_bookings' => 'Monthly booking count',
        'total_bookings' => 'Total booking count'
    ],
    'error_logs' => [
        'api_errors' => 'API error tracking',
        'validation_errors' => 'Form validation errors',
        'system_errors' => 'System error tracking'
    ],
    'performance_metrics' => [
        'response_times' => 'API response time tracking',
        'cache_hit_rate' => 'Cache hit rate metrics',
        'database_queries' => 'Database query performance'
    ]
];
```

## Memory Bank Implementation

### Core Memory Manager Class

```php
class IAT_Memory_Bank {
    private static $instance = null;
    private $memory_store = [];
    private $cache_store = [];
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    // Configuration memory operations
    public function set_config($key, $value) {
        $this->memory_store['config'][$key] = $value;
        update_option('iat_config_' . $key, $value);
    }
    
    public function get_config($key, $default = null) {
        if (isset($this->memory_store['config'][$key])) {
            return $this->memory_store['config'][$key];
        }
        
        $value = get_option('iat_config_' . $key, $default);
        $this->memory_store['config'][$key] = $value;
        return $value;
    }
    
    // State memory operations
    public function set_state($key, $value) {
        $this->memory_store['state'][$key] = $value;
        set_transient('iat_state_' . $key, $value, 3600);
    }
    
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
    
    // Cache memory operations
    public function set_cache($key, $value, $ttl = 3600) {
        $this->cache_store[$key] = $value;
        set_transient('iat_cache_' . $key, $value, $ttl);
    }
    
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
    
    // Operational memory operations
    public function increment_counter($key, $amount = 1) {
        $current = $this->get_operational($key, 0);
        $new_value = $current + $amount;
        $this->set_operational($key, $new_value);
        return $new_value;
    }
    
    public function set_operational($key, $value) {
        $this->memory_store['operational'][$key] = $value;
        update_option('iat_operational_' . $key, $value);
    }
    
    public function get_operational($key, $default = null) {
        if (isset($this->memory_store['operational'][$key])) {
            return $this->memory_store['operational'][$key];
        }
        
        $value = get_option('iat_operational_' . $key, $default);
        $this->memory_store['operational'][$key] = $value;
        return $value;
    }
    
    // Bulk operations
    public function load_all_config() {
        global $wpdb;
        $results = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'iat_config_%'");
        
        foreach ($results as $result) {
            $key = str_replace('iat_config_', '', $result->option_name);
            $this->memory_store['config'][$key] = maybe_unserialize($result->option_value);
        }
    }
    
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
    
    private function clear_transient_pattern($pattern) {
        global $wpdb;
        $like_pattern = str_replace('*', '%', $pattern);
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $like_pattern
        ));
    }
}
```

### Memory Bank Usage Examples

```php
// Initialize memory bank
$memory_bank = IAT_Memory_Bank::get_instance();

// Store API configuration
$memory_bank->set_config('api_keys_yandex', [
    'key1' => 'your_yandex_key_1',
    'key2' => 'your_yandex_key_2',
    // ... more keys
]);

// Track API usage
$current_usage = $memory_bank->get_state('api_usage_yandex_count', 0);
$memory_bank->set_state('api_usage_yandex_count', $current_usage + 1);

// Cache geocoding results
$geocoding_result = [
    'lat' => 41.0082,
    'lng' => 28.9784,
    'address' => 'Istanbul, Turkey'
];
$memory_bank->set_cache('geocode_' . md5('Istanbul'), $geocoding_result, 86400);

// Track booking counters
$daily_bookings = $memory_bank->increment_counter('daily_bookings');
$monthly_bookings = $memory_bank->increment_counter('monthly_bookings');

// Store system status
$memory_bank->set_state('system_status_api_health', [
    'nominatim' => 'healthy',
    'yandex' => 'healthy',
    'google' => 'healthy'
]);
```

## Memory Bank Management

### Cleanup Operations

```php
class IAT_Memory_Cleanup {
    public static function schedule_cleanup() {
        if (!wp_next_scheduled('iat_cleanup_memory')) {
            wp_schedule_event(time(), 'hourly', 'iat_cleanup_memory');
        }
    }
    
    public static function cleanup_memory() {
        $memory_bank = IAT_Memory_Bank::get_instance();
        
        // Clean old cache entries
        $memory_bank->clear_cache();
        
        // Clean old state data
        self::cleanup_old_transients();
        
        // Clean old operational data
        self::cleanup_old_operational_data();
        
        // Update cleanup timestamp
        $memory_bank->set_operational('last_cleanup', current_time('mysql'));
    }
    
    private static function cleanup_old_transients() {
        global $wpdb;
        $expiration_time = time() - (24 * 3600); // 24 hours ago
        
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} 
             WHERE option_name LIKE %s 
             AND option_value < %d",
            '_transient_timeout_%',
            $expiration_time
        ));
    }
    
    private static function cleanup_old_operational_data() {
        $memory_bank = IAT_Memory_Bank::get_instance();
        
        // Keep only last 30 days of counters
        $current_date = date('Y-m-d');
        $cutoff_date = date('Y-m-d', strtotime('-30 days'));
        
        // Clean daily counters older than 30 days
        $daily_counters = $memory_bank->get_operational('daily_counters', []);
        foreach ($daily_counters as $date => $count) {
            if ($date < $cutoff_date) {
                unset($daily_counters[$date]);
            }
        }
        $memory_bank->set_operational('daily_counters', $daily_counters);
    }
}
```

### Memory Bank Monitoring

```php
class IAT_Memory_Monitor {
    public static function get_memory_status() {
        $memory_bank = IAT_Memory_Bank::get_instance();
        
        return [
            'config_size' => count($memory_bank->memory_store['config'] ?? []),
            'state_size' => count($memory_bank->memory_store['state'] ?? []),
            'cache_size' => count($memory_bank->cache_store),
            'operational_size' => count($memory_bank->memory_store['operational'] ?? []),
            'cache_hit_rate' => self::calculate_cache_hit_rate(),
            'memory_usage' => memory_get_usage(true),
            'memory_peak' => memory_get_peak_usage(true)
        ];
    }
    
    private static function calculate_cache_hit_rate() {
        // Implementation for cache hit rate calculation
        // This would track cache hits vs misses
        return 0.85; // Example value
    }
    
    public static function log_memory_usage() {
        $status = self::get_memory_status();
        
        error_log('IAT Memory Bank Status: ' . json_encode([
            'timestamp' => current_time('mysql'),
            'status' => $status
        ]));
    }
}
```

## Integration with Existing Systems

### Database Integration

```php
// Extend existing IAT_DB_Manager to work with memory bank
class IAT_DB_Manager_Extended extends IAT_DB_Manager {
    public function get_region_by_code($zone_code) {
        $memory_bank = IAT_Memory_Bank::get_instance();
        
        // Check cache first
        $cache_key = 'region_' . $zone_code;
        $cached = $memory_bank->get_cache($cache_key);
        if ($cached) {
            return $cached;
        }
        
        // Query database
        $result = parent::get_region_by_code($zone_code);
        
        // Cache result
        if ($result) {
            $memory_bank->set_cache($cache_key, $result, 1800); // 30 minutes
        }
        
        return $result;
    }
}
```

### API Integration

```php
// Extend API rotator to use memory bank
class IAT_API_Rotator_Extended extends IAT_API_Rotator {
    public function geocode($address) {
        $memory_bank = IAT_Memory_Bank::get_instance();
        $cache_key = 'geocode_' . md5($address);
        
        // Check cache first
        $cached = $memory_bank->get_cache($cache_key);
        if ($cached) {
            return $cached;
        }
        
        // Perform geocoding
        $result = parent::geocode($address);
        
        // Cache result
        if ($result) {
            $memory_bank->set_cache($cache_key, $result, 86400); // 24 hours
        }
        
        return $result;
    }
}
```

## Security Considerations

### Data Encryption

```php
class IAT_Memory_Security {
    public static function encrypt_sensitive_data($data) {
        if (is_array($data)) {
            $data = json_encode($data);
        }
        
        $key = hash('sha256', AUTH_KEY);
        $iv = substr(hash('sha256', NONCE_KEY), 0, 16);
        
        return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv));
    }
    
    public static function decrypt_sensitive_data($data) {
        $key = hash('sha256', AUTH_KEY);
        $iv = substr(hash('sha256', NONCE_KEY), 0, 16);
        
        $decrypted = openssl_decrypt(base64_decode($data), 'AES-256-CBC', $key, 0, $iv);
        return json_decode($decrypted, true);
    }
}
```

### Access Control

```php
class IAT_Memory_Access_Control {
    public static function check_access($key, $action = 'read') {
        // Check if user has permission to access memory key
        if (strpos($key, 'api_keys') !== false) {
            return current_user_can('manage_options');
        }
        
        if (strpos($key, 'operational') !== false) {
            return current_user_can('manage_options') || current_user_can('edit_posts');
        }
        
        return true; // Public access for cache and general config
    }
}
```

This memory bank system provides a comprehensive solution for managing plugin state, configuration, and operational data while maintaining performance, security, and reliability.