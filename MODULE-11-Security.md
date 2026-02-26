# Security Implementation Guide

This module implements comprehensive security measures for the Istanbul Airport Transfer WordPress plugin following OWASP best practices.

## 🛡️ Security Overview

The plugin implements multiple layers of security to protect against common web vulnerabilities while maintaining usability and performance.

## 🔒 Authentication & Authorization

### reCAPTCHA v3 Integration
```php
class IAT_Security {
    public function verify_recaptcha(string $token): bool {
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', [
            'body' => [
                'secret' => $this->get_recaptcha_secret(),
                'response' => $token,
                'remoteip' => $this->get_client_ip()
            ]
        ]);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $result = json_decode(wp_remote_retrieve_body($response), true);
        return $result['success'] && $result['score'] >= 0.5;
    }
}
```

**Configuration:**
- Minimum score: 0.5 (adjustable via settings)
- Site key: Public key for frontend
- Secret key: Private key for verification
- Remote IP tracking for enhanced security

### WordPress Nonce System
```php
// Form submission protection
wp_nonce_field('iat_booking_form', 'iat_nonce');

// AJAX request protection
if (!wp_verify_nonce($_POST['nonce'], 'iat_ajax_action')) {
    wp_die('Security check failed');
}

// URL action protection
$confirm_url = add_query_arg([
    'action' => 'confirm_booking',
    'token' => $booking_token,
    '_wpnonce' => wp_create_nonce('iat_confirm_booking')
], home_url());
```

## 🚫 Input Validation & Sanitization

### Comprehensive Input Validation
```php
class IAT_Validator {
    public function validate_phone(string $phone): string {
        // E.164 format validation
        if (!preg_match('/^\+[1-9]\d{1,14}$/', $phone)) {
            throw new InvalidArgumentException('Invalid phone format');
        }
        return sanitize_text_field($phone);
    }
    
    public function validate_email(string $email): string {
        if (!is_email($email)) {
            throw new InvalidArgumentException('Invalid email format');
        }
        return sanitize_email($email);
    }
    
    public function validate_address(string $address): string {
        if (strlen($address) < 5) {
            throw new InvalidArgumentException('Address too short');
        }
        return sanitize_textarea_field($address);
    }
    
    public function validate_datetime(string $datetime): string {
        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            throw new InvalidArgumentException('Invalid date format');
        }
        
        // Check minimum advance booking time
        $min_time = strtotime('+24 hours');
        if ($timestamp < $min_time) {
            throw new InvalidArgumentException('Booking must be at least 24 hours in advance');
        }
        
        return date('Y-m-d H:i:s', $timestamp);
    }
}
```

### SQL Injection Prevention
```php
class IAT_DB_Safe {
    public function get_booking_by_id(string $booking_id): ?array {
        global $wpdb;
        
        // Prepared statement usage
        $query = $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}iat_bookings WHERE booking_id = %s",
            $booking_id
        );
        
        return $wpdb->get_row($query, ARRAY_A);
    }
    
    public function create_booking(array $data): int {
        global $wpdb;
        
        // Data sanitization before insertion
        $clean_data = [
            'booking_id' => sanitize_key($data['booking_id']),
            'pickup_address' => sanitize_textarea_field($data['pickup_address']),
            'dropoff_address' => sanitize_textarea_field($data['dropoff_address']),
            'contact_email' => sanitize_email($data['contact_email']),
            'contact_phone' => sanitize_text_field($data['contact_phone']),
            'price_eur' => floatval($data['price_eur']),
            'created_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'iat_bookings',
            $clean_data,
            ['%s', '%s', '%s', '%s', '%s', '%f', '%s']
        );
        
        return $result ? $wpdb->insert_id : false;
    }
}
```

## 🚫 Cross-Site Scripting (XSS) Prevention

### Output Escaping
```php
class IAT_Escaper {
    public function escape_booking_data(array $booking): array {
        return [
            'booking_id' => esc_html($booking['booking_id']),
            'pickup_address' => esc_html($booking['pickup_address']),
            'dropoff_address' => esc_html($booking['dropoff_address']),
            'contact_email' => esc_html($booking['contact_email']),
            'contact_phone' => esc_html($booking['contact_phone']),
            'price_eur' => esc_html($booking['price_eur']),
            'status' => esc_html($booking['status'])
        ];
    }
    
    public function escape_admin_output(string $content): string {
        return wp_kses_post($content);
    }
    
    public function sanitize_geojson(string $geojson): string {
        // Allow only JSON structure, no HTML/JS
        if (!is_string($geojson) || !json_decode($geojson)) {
            throw new InvalidArgumentException('Invalid GeoJSON format');
        }
        return wp_json_encode(json_decode($geojson, true));
    }
}
```

## 🚫 Cross-Site Request Forgery (CSRF) Protection

### Comprehensive CSRF Protection
```php
class IAT_CSRF_Protection {
    public function __construct() {
        add_action('admin_init', [$this, 'check_admin_nonce']);
        add_action('wp_ajax_iat_action', [$this, 'verify_ajax_nonce']);
    }
    
    public function check_admin_nonce() {
        if (isset($_POST['iat_admin_action'])) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'iat_admin_action')) {
                wp_die('CSRF token verification failed');
            }
        }
    }
    
    public function verify_ajax_nonce() {
        check_ajax_referer('iat_ajax_nonce', 'security');
    }
    
    public function generate_action_url(string $action, array $params = []): string {
        $params['_wpnonce'] = wp_create_nonce('iat_' . $action);
        return add_query_arg($params, admin_url('admin-ajax.php'));
    }
}
```

## 🔐 API Key Security

### Secure API Key Management
```php
class IAT_API_Security {
    public function save_api_keys(array $keys): bool {
        // Encrypt API keys before saving
        $encrypted_keys = [];
        foreach ($keys as $provider => $key_list) {
            $encrypted_keys[$provider] = array_map(function($key) {
                return $this->encrypt_key($key);
            }, $key_list);
        }
        
        return update_option('iat_api_keys_encrypted', $encrypted_keys);
    }
    
    public function get_api_key(string $provider, int $index): ?string {
        $encrypted_keys = get_option('iat_api_keys_encrypted', []);
        if (isset($encrypted_keys[$provider][$index])) {
            return $this->decrypt_key($encrypted_keys[$provider][$index]);
        }
        return null;
    }
    
    private function encrypt_key(string $key): string {
        $key = hash('sha256', AUTH_KEY . $key);
        return base64_encode(openssl_encrypt($key, 'AES-256-CBC', AUTH_KEY, 0, substr(AUTH_KEY, 0, 16)));
    }
    
    private function decrypt_key(string $encrypted_key): string {
        return openssl_decrypt(base64_decode($encrypted_key), 'AES-256-CBC', AUTH_KEY, 0, substr(AUTH_KEY, 0, 16));
    }
}
```

## 🚫 Rate Limiting

### IP-Based Rate Limiting
```php
class IAT_Rate_Limiter {
    private $max_requests = 10;
    private $timeframe = 60; // 60 seconds
    
    public function is_allowed(string $ip, string $action = 'default'): bool {
        $transient_key = 'iat_rate_limit_' . md5($ip . $action);
        $requests = get_transient($transient_key);
        
        if ($requests === false) {
            set_transient($transient_key, 1, $this->timeframe);
            return true;
        }
        
        if ($requests >= $this->max_requests) {
            return false;
        }
        
        set_transient($transient_key, $requests + 1, $this->timeframe);
        return true;
    }
    
    public function log_blocked_request(string $ip, string $action): void {
        error_log(sprintf(
            '[IAT Security] Rate limit exceeded: IP %s, Action %s, Time %s',
            $ip,
            $action,
            current_time('mysql')
        ));
    }
}
```

## 🔒 File Upload Security

### GeoJSON Upload Validation
```php
class IAT_File_Upload_Security {
    public function validate_geojson_upload($file): array {
        // Check file type
        $allowed_types = ['application/json', 'text/plain'];
        if (!in_array($file['type'], $allowed_types)) {
            throw new Exception('Invalid file type. Only JSON files are allowed.');
        }
        
        // Check file size (max 1MB)
        if ($file['size'] > 1048576) {
            throw new Exception('File too large. Maximum size is 1MB.');
        }
        
        // Read and validate JSON structure
        $content = file_get_contents($file['tmp_name']);
        $json = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON format.');
        }
        
        // Validate GeoJSON structure
        if (!isset($json['type']) || $json['type'] !== 'FeatureCollection') {
            throw new Exception('Invalid GeoJSON format. Must be FeatureCollection.');
        }
        
        return $json;
    }
}
```

## 🚫 Directory Traversal Protection

### Secure File Operations
```php
class IAT_File_Security {
    public function secure_file_path(string $path): string {
        // Remove dangerous characters
        $path = str_replace(['..', '/', '\\', '%00'], '', $path);
        
        // Ensure path is within plugin directory
        $plugin_dir = plugin_dir_path(__FILE__);
        $real_path = realpath($plugin_dir . $path);
        
        if (strpos($real_path, $plugin_dir) !== 0) {
            throw new Exception('Access denied: Invalid file path');
        }
        
        return $real_path;
    }
}
```

## 🔒 Session Security

### Secure Session Management
```php
class IAT_Session_Security {
    public function __construct() {
        // Regenerate session ID periodically
        add_action('init', [$this, 'regenerate_session']);
    }
    
    public function regenerate_session() {
        if (!session_id()) {
            session_start();
        }
        
        // Regenerate session ID every 30 minutes
        if (!isset($_SESSION['last_regeneration']) || 
            time() - $_SESSION['last_regeneration'] > 1800) {
            session_regenerate_id(true);
            $_SESSION['last_regeneration'] = time();
        }
    }
    
    public function set_secure_cookie(string $name, string $value): void {
        setcookie(
            $name,
            $value,
            [
                'expires' => time() + 3600,
                'path' => '/',
                'secure' => is_ssl(),
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );
    }
}
```

## 🚫 Error Handling Security

### Secure Error Reporting
```php
class IAT_Error_Handler {
    public function __construct() {
        set_error_handler([$this, 'handle_error']);
        set_exception_handler([$this, 'handle_exception']);
    }
    
    public function handle_error(int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        // Log error details
        $this->log_error($severity, $message, $file, $line);
        
        // Don't display errors in production
        if (!WP_DEBUG) {
            return true;
        }
        
        return false;
    }
    
    public function handle_exception(Throwable $exception): void {
        $this->log_error(
            E_ERROR,
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine()
        );
        
        if (!WP_DEBUG) {
            wp_die('An error occurred. Please try again later.');
        }
    }
    
    private function log_error(int $severity, string $message, string $file, int $line): void {
        $error_data = [
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'timestamp' => current_time('mysql'),
            'ip' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        
        error_log(json_encode($error_data));
    }
}
```

## 🔒 WordPress Security Best Practices

### Security Headers
```php
class IAT_Security_Headers {
    public function __construct() {
        add_action('send_headers', [$this, 'add_security_headers']);
    }
    
    public function add_security_headers() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy
        if (defined('WP_DEBUG') && WP_DEBUG) {
            header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' *.googleapis.com *.gstatic.com; style-src 'self' 'unsafe-inline' *.googleapis.com; img-src 'self' data: *.googleapis.com *.gstatic.com; font-src 'self' *.googleapis.com *.gstatic.com;");
        } else {
            header("Content-Security-Policy: default-src 'self'; script-src 'self' *.googleapis.com *.gstatic.com; style-src 'self' *.googleapis.com; img-src 'self' data: *.googleapis.com *.gstatic.com; font-src 'self' *.googleapis.com *.gstatic.com;");
        }
    }
}
```

## 🚫 Database Security

### Database Hardening
```php
class IAT_DB_Security {
    public function __construct() {
        add_action('init', [$this, 'harden_database']);
    }
    
    public function harden_database() {
        global $wpdb;
        
        // Disable database errors in production
        if (!WP_DEBUG) {
            $wpdb->hide_errors();
        }
        
        // Set database charset
        $wpdb->set_charset($wpdb->dbh, 'utf8mb4', 'utf8mb4_unicode_ci');
        
        // Enable query logging in debug mode
        if (WP_DEBUG) {
            $wpdb->save_queries = true;
        }
    }
    
    public function cleanup_old_data() {
        global $wpdb;
        
        // Clean old geocache entries
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}iat_geocache WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
                30
            )
        );
        
        // Clean old API usage logs
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}iat_api_usage WHERE call_date < DATE_SUB(NOW(), INTERVAL %d DAY)",
                90
            )
        );
    }
}
```

## 🔒 Security Testing

### Security Audit Functions
```php
class IAT_Security_Audit {
    public function run_security_audit(): array {
        $issues = [];
        
        // Check for debug mode in production
        if (WP_DEBUG && !WP_ENVIRONMENT_TYPE === 'development') {
            $issues[] = 'Debug mode enabled in production environment';
        }
        
        // Check for weak file permissions
        $plugin_dir = plugin_dir_path(__FILE__);
        if (substr(sprintf('%o', fileperms($plugin_dir)), -3) !== '755') {
            $issues[] = 'Plugin directory has incorrect permissions';
        }
        
        // Check for exposed API keys
        $api_keys = get_option('iat_api_keys_encrypted');
        if (empty($api_keys)) {
            $issues[] = 'No API keys configured';
        }
        
        // Check for outdated WordPress version
        if (version_compare($GLOBALS['wp_version'], '6.0', '<')) {
            $issues[] = 'WordPress version is outdated';
        }
        
        return $issues;
    }
    
    public function generate_security_report(): string {
        $issues = $this->run_security_audit();
        
        $report = "Security Audit Report - " . current_time('mysql') . "\n";
        $report .= "==========================================\n\n";
        
        if (empty($issues)) {
            $report .= "✅ No security issues found.\n";
        } else {
            $report .= "⚠️  Security Issues Found:\n";
            foreach ($issues as $issue) {
                $report .= "- $issue\n";
            }
        }
        
        return $report;
    }
}
```

## 🚨 Security Incident Response

### Incident Handling
```php
class IAT_Security_Incident {
    public function log_security_incident(string $type, string $description, array $context = []): void {
        $incident = [
            'type' => $type,
            'description' => $description,
            'context' => $context,
            'timestamp' => current_time('mysql'),
            'ip' => $this->get_client_ip(),
            'user_id' => get_current_user_id(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ];
        
        // Log to file
        error_log('[IAT Security Incident] ' . json_encode($incident));
        
        // Send alert email if configured
        $admin_email = get_option('admin_email');
        if ($admin_email) {
            wp_mail(
                $admin_email,
                'Security Incident Alert - Istanbul Airport Transfer',
                'Security incident detected: ' . $description . "\n\nDetails: " . print_r($context, true)
            );
        }
    }
    
    public function handle_suspicious_activity(string $activity, string $ip): void {
        // Log the activity
        $this->log_security_incident('suspicious_activity', $activity, ['ip' => $ip]);
        
        // Block IP if too many incidents
        $transient_key = 'iat_blocked_ips';
        $blocked_ips = get_transient($transient_key) ?: [];
        
        if (!in_array($ip, $blocked_ips)) {
            $blocked_ips[] = $ip;
            set_transient($transient_key, $blocked_ips, DAY_IN_SECONDS);
        }
    }
}
```

## 📋 Security Checklist

### Development Phase
- [ ] Input validation for all user inputs
- [ ] Output escaping for all dynamic content
- [ ] Prepared statements for all database queries
- [ ] Nonce verification for all forms and AJAX requests
- [ ] reCAPTCHA v3 integration
- [ ] Rate limiting implementation
- [ ] File upload validation
- [ ] API key encryption
- [ ] Security headers configuration

### Testing Phase
- [ ] XSS attack simulation
- [ ] SQL injection attempt testing
- [ ] CSRF attack prevention testing
- [ ] Rate limiting effectiveness testing
- [ ] Input validation boundary testing
- [ ] File upload security testing

### Production Phase
- [ ] Debug mode disabled
- [ ] Security audit report generation
- [ ] Incident response plan documentation
- [ ] Regular security updates monitoring
- [ ] Security log monitoring setup

## 🔒 Security Configuration

### Recommended WordPress Configuration
```php
// wp-config.php additions
define('DISALLOW_FILE_EDIT', true);
define('FORCE_SSL_ADMIN', true);
define('WP_MEMORY_LIMIT', '256M');
define('WP_MAX_MEMORY_LIMIT', '512M');

// Disable XML-RPC
add_filter('xmlrpc_enabled', '__return_false');

// Disable REST API for non-logged users
add_filter('rest_authentication_errors', function($result) {
    if (!empty($result)) {
        return $result;
    }
    if (!is_user_logged_in()) {
        return new WP_Error('rest_not_logged_in', 'You are not currently logged in.', array('status' => 401));
    }
    return $result;
});
```

This security implementation provides comprehensive protection against common web vulnerabilities while maintaining the plugin's functionality and performance. Regular security audits and updates should be performed to maintain the highest security standards.