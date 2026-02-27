<?php
/**
 * IAT Memory Security
 * 
 * Security management for the Istanbul Airport Transfer WordPress plugin memory bank.
 * Handles data encryption, access control, and security validation for sensitive memory operations.
 * 
 * @package Istanbul_Airport_Transfer
 * @subpackage Security
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * IAT Memory Security Class
 * 
 * Manages security aspects of memory bank operations including encryption,
 * access control, and data validation for sensitive operations.
 */
class IAT_Memory_Security {
    
    /**
     * Encryption key prefix
     * 
     * @var string
     */
    private static $key_prefix = 'iat_memory_';
    
    /**
     * Sensitive data keys that require encryption
     * 
     * @var array
     */
    private static $sensitive_keys = [
        'api_keys',
        'recaptcha_secret',
        'admin_email',
        'whatsapp_number',
        'phone_number',
        'payment_api_keys',
        'smtp_password'
    ];
    
    /**
     * Encrypt sensitive data
     * 
     * @param mixed $data Data to encrypt
     * @param string $key Optional key identifier for key derivation
     * @return string Encrypted data
     */
    public static function encrypt_sensitive_data($data, $key = '') {
        if (empty($data)) {
            return '';
        }
        
        // Serialize data if it's an array
        if (is_array($data) || is_object($data)) {
            $data = json_encode($data);
        }
        
        // Generate encryption key
        $encryption_key = self::generate_encryption_key($key);
        $iv = self::generate_iv($key);
        
        // Encrypt using AES-256-CBC
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $encryption_key, 0, $iv);
        
        // Return base64 encoded encrypted data
        return base64_encode($encrypted);
    }
    
    /**
     * Decrypt sensitive data
     * 
     * @param string $data Encrypted data
     * @param string $key Optional key identifier for key derivation
     * @return mixed Decrypted data
     */
    public static function decrypt_sensitive_data($data, $key = '') {
        if (empty($data)) {
            return '';
        }
        
        // Decode base64
        $data = base64_decode($data);
        if ($data === false) {
            return '';
        }
        
        // Generate encryption key
        $encryption_key = self::generate_encryption_key($key);
        $iv = self::generate_iv($key);
        
        // Decrypt using AES-256-CBC
        $decrypted = openssl_decrypt($data, 'AES-256-CBC', $encryption_key, 0, $iv);
        
        if ($decrypted === false) {
            return '';
        }
        
        // Try to decode JSON if it's JSON data
        $json_data = json_decode($decrypted, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $json_data;
        }
        
        return $decrypted;
    }
    
    /**
     * Check if a key contains sensitive data
     * 
     * @param string $key Memory key to check
     * @return bool
     */
    public static function is_sensitive_key($key) {
        foreach (self::$sensitive_keys as $sensitive_key) {
            if (strpos($key, $sensitive_key) !== false) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Validate access to memory key
     * 
     * @param string $key Memory key
     * @param string $action Action being performed (read, write, delete)
     * @return bool
     */
    public static function validate_access($key, $action = 'read') {
        // Check if key requires special permissions
        if (self::is_sensitive_key($key)) {
            // Sensitive keys require admin privileges
            return current_user_can('manage_options');
        }
        
        // Operational keys require edit_posts or higher
        if (strpos($key, 'operational') !== false) {
            return current_user_can('manage_options') || current_user_can('edit_posts');
        }
        
        // State keys require at least read access
        if (strpos($key, 'state') !== false) {
            return current_user_can('read');
        }
        
        // Cache and general config keys are public
        return true;
    }
    
    /**
     * Sanitize memory key
     * 
     * @param string $key Memory key to sanitize
     * @return string Sanitized key
     */
    public static function sanitize_key($key) {
        // Remove any potentially dangerous characters
        $key = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
        
        // Limit length
        $key = substr($key, 0, 100);
        
        return $key;
    }
    
    /**
     * Validate data before storing in memory
     * 
     * @param string $key Memory key
     * @param mixed $data Data to validate
     * @return bool
     */
    public static function validate_data($key, $data) {
        // Check data size limits
        $data_size = strlen(serialize($data));
        if ($data_size > 1024 * 1024) { // 1MB limit
            return false;
        }
        
        // Validate sensitive data format
        if (self::is_sensitive_key($key)) {
            return self::validate_sensitive_data($key, $data);
        }
        
        return true;
    }
    
    /**
     * Validate sensitive data format
     * 
     * @param string $key Memory key
     * @param mixed $data Data to validate
     * @return bool
     */
    private static function validate_sensitive_data($key, $data) {
        // API keys should be strings
        if (strpos($key, 'api_keys') !== false) {
            if (is_array($data)) {
                foreach ($data as $provider => $keys) {
                    if (!is_array($keys)) {
                        return false;
                    }
                    foreach ($keys as $key_value) {
                        if (!is_string($key_value) || strlen($key_value) < 10) {
                            return false;
                        }
                    }
                }
            } else {
                return false;
            }
        }
        
        // Email validation
        if (strpos($key, 'email') !== false) {
            if (!is_string($data) || !filter_var($data, FILTER_VALIDATE_EMAIL)) {
                return false;
            }
        }
        
        // Phone number validation
        if (strpos($key, 'phone') !== false) {
            if (!is_string($data) || !preg_match('/^[+]?[\d\s\-\(\)]{7,20}$/', $data)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Generate encryption key
     * 
     * @param string $key Optional key identifier
     * @return string Encryption key
     */
    private static function generate_encryption_key($key = '') {
        // Use AUTH_KEY as base for encryption
        $base_key = defined('AUTH_KEY') ? AUTH_KEY : 'default_auth_key';
        
        // Add plugin-specific salt
        $salt = 'iat_memory_bank_salt';
        
        // Add optional key-specific salt
        $key_salt = !empty($key) ? '_' . $key : '';
        
        // Generate final key
        $final_key = hash('sha256', $base_key . $salt . $key_salt);
        
        return substr($final_key, 0, 32); // AES-256 requires 32-byte key
    }
    
    /**
     * Generate initialization vector
     * 
     * @param string $key Optional key identifier
     * @return string Initialization vector
     */
    private static function generate_iv($key = '') {
        // Use NONCE_KEY as base for IV
        $base_iv = defined('NONCE_KEY') ? NONCE_KEY : 'default_nonce_key';
        
        // Add plugin-specific salt
        $salt = 'iat_memory_iv_salt';
        
        // Add optional key-specific salt
        $key_salt = !empty($key) ? '_' . $key : '';
        
        // Generate final IV
        $final_iv = hash('sha256', $base_iv . $salt . $key_salt);
        
        return substr($final_iv, 0, 16); // AES-256-CBC requires 16-byte IV
    }
    
    /**
     * Hash sensitive data for comparison
     * 
     * @param mixed $data Data to hash
     * @return string Hashed data
     */
    public static function hash_sensitive_data($data) {
        if (is_array($data) || is_object($data)) {
            $data = json_encode($data);
        }
        
        return hash('sha256', $data);
    }
    
    /**
     * Log security event
     * 
     * @param string $event Security event description
     * @param string $key Memory key involved
     * @param string $user User performing action
     * @return void
     */
    public static function log_security_event($event, $key = '', $user = '') {
        if (empty($user)) {
            $user = wp_get_current_user()->user_login ?: 'anonymous';
        }
        
        $log_data = [
            'timestamp' => current_time('mysql'),
            'event' => $event,
            'key' => $key,
            'user' => $user,
            'ip' => self::get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ];
        
        error_log('IAT Memory Security Event: ' . json_encode($log_data));
    }
    
    /**
     * Get client IP address
     * 
     * @return string Client IP
     */
    private static function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        }
    }
    
    /**
     * Check for suspicious activity
     * 
     * @param string $action Action being performed
     * @param string $key Memory key involved
     * @return bool
     */
    public static function check_suspicious_activity($action, $key) {
        // Implement rate limiting for sensitive operations
        if (self::is_sensitive_key($key)) {
            $transient_key = 'iat_security_rate_limit_' . md5($key . '_' . self::get_client_ip());
            $count = get_transient($transient_key);
            
            if ($count >= 10) { // Limit to 10 attempts per hour
                self::log_security_event('Rate limit exceeded', $key);
                return false;
            }
            
            set_transient($transient_key, ($count ?: 0) + 1, 3600); // 1 hour
        }
        
        return true;
    }
    
    /**
     * Secure memory key for sensitive data
     * 
     * @param string $key Original key
     * @return string Secure key
     */
    public static function secure_key($key) {
        if (self::is_sensitive_key($key)) {
            return self::$key_prefix . md5($key);
        }
        return $key;
    }
    
    /**
     * Cleanup sensitive data from memory
     * 
     * @param string $key Memory key
     * @return void
     */
    public static function cleanup_sensitive_data($key) {
        if (self::is_sensitive_key($key)) {
            // Clear from WordPress options
            delete_option('iat_config_' . $key);
            
            // Clear from transients
            delete_transient('iat_state_' . $key);
            delete_transient('iat_cache_' . $key);
            
            // Clear from operational data
            delete_option('iat_operational_' . $key);
            
            // Log the cleanup
            self::log_security_event('Sensitive data cleanup', $key);
        }
    }
}