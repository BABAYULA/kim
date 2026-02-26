# Error Handling and Logging Strategy

This module implements a comprehensive error handling and logging system for the Istanbul Airport Transfer WordPress plugin to ensure reliable operation and easy debugging.

## 📊 Error Handling Overview

The plugin implements a multi-layered error handling approach with structured logging, user-friendly error messages, and comprehensive debugging capabilities.

## 🔧 Error Types and Classification

### Error Categories
```php
class IAT_Error_Types {
    const VALIDATION_ERROR = 'validation_error';
    const DATABASE_ERROR = 'database_error';
    const API_ERROR = 'api_error';
    const SECURITY_ERROR = 'security_error';
    const SYSTEM_ERROR = 'system_error';
    const USER_ERROR = 'user_error';
}
```

### Error Severity Levels
```php
class IAT_Error_Severity {
    const DEBUG = 'debug';
    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';
    const CRITICAL = 'critical';
}
```

## 📝 Structured Logging System

### Log Entry Structure
```php
class IAT_Log_Entry {
    public function create_log_entry(
        string $severity,
        string $message,
        string $context,
        array $data = [],
        string $file = '',
        int $line = 0
    ): array {
        return [
            'timestamp' => current_time('mysql'),
            'severity' => $severity,
            'message' => $message,
            'context' => $context,
            'data' => $data,
            'file' => $file,
            'line' => $line,
            'ip' => $this->get_client_ip(),
            'user_id' => get_current_user_id(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            'request_uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown'
        ];
    }
}
```

### JSON Log Format
```php
class IAT_Logger {
    private $log_file;
    private $log_level;
    
    public function __construct() {
        $this->log_file = WP_CONTENT_DIR . '/logs/iat-plugin.log';
        $this->log_level = defined('WP_DEBUG') && WP_DEBUG ? 'debug' : 'error';
        
        // Ensure log directory exists
        $log_dir = dirname($this->log_file);
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
    }
    
    public function log(string $severity, string $message, array $context = []): void {
        if ($this->should_log($severity)) {
            $entry = $this->create_log_entry($severity, $message, $context);
            $this->write_log($entry);
        }
    }
    
    private function write_log(array $entry): void {
        $log_line = json_encode($entry, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        
        // Atomic file write
        $temp_file = $this->log_file . '.tmp';
        file_put_contents($temp_file, $log_line, FILE_APPEND | LOCK_EX);
        rename($temp_file, $this->log_file);
    }
    
    private function should_log(string $severity): bool {
        $levels = ['debug' => 1, 'info' => 2, 'warning' => 3, 'error' => 4, 'critical' => 5];
        return $levels[$severity] >= $levels[$this->log_level];
    }
}
```

## 🚨 Error Handling Classes

### Database Error Handler
```php
class IAT_DB_Error_Handler {
    private $logger;
    
    public function __construct() {
        $this->logger = new IAT_Logger();
    }
    
    public function handle_db_error(string $operation, string $query, $wpdb_error): WP_Error {
        $error_data = [
            'operation' => $operation,
            'query' => $query,
            'wpdb_error' => $wpdb->last_error,
            'error_number' => $wpdb->last_result
        ];
        
        $this->logger->log('error', "Database operation failed: {$operation}", $error_data);
        
        return new WP_Error(
            'database_error',
            'A database error occurred. Please try again later.',
            $error_data
        );
    }
    
    public function handle_query_timeout(string $query): WP_Error {
        $this->logger->log('warning', "Query timeout detected", ['query' => $query]);
        
        return new WP_Error(
            'query_timeout',
            'The request is taking longer than expected. Please try again.',
            ['query' => $query]
        );
    }
}
```

### API Error Handler
```php
class IAT_API_Error_Handler {
    private $logger;
    
    public function __construct() {
        $this->logger = new IAT_Logger();
    }
    
    public function handle_api_error(string $provider, string $endpoint, $response): WP_Error {
        $error_data = [
            'provider' => $provider,
            'endpoint' => $endpoint,
            'response_code' => wp_remote_retrieve_response_code($response),
            'response_message' => wp_remote_retrieve_response_message($response),
            'response_body' => wp_remote_retrieve_body($response)
        ];
        
        $this->logger->log('error', "API request failed: {$provider}", $error_data);
        
        return new WP_Error(
            'api_error',
            'Unable to complete the request. Please try again later.',
            $error_data
        );
    }
    
    public function handle_rate_limit(string $provider, string $ip): WP_Error {
        $this->logger->log('warning', "API rate limit exceeded", [
            'provider' => $provider,
            'ip' => $ip
        ]);
        
        return new WP_Error(
            'rate_limit_exceeded',
            'Too many requests. Please wait a moment and try again.',
            ['provider' => $provider, 'ip' => $ip]
        );
    }
    
    public function handle_api_key_error(string $provider): WP_Error {
        $this->logger->log('critical', "Invalid API key detected", ['provider' => $provider]);
        
        return new WP_Error(
            'invalid_api_key',
            'Configuration error. Please contact the administrator.',
            ['provider' => $provider]
        );
    }
}
```

### Validation Error Handler
```php
class IAT_Validation_Error_Handler {
    private $logger;
    
    public function __construct() {
        $this->logger = new IAT_Logger();
    }
    
    public function handle_validation_error(string $field, string $message, $value): WP_Error {
        $error_data = [
            'field' => $field,
            'value' => $value,
            'message' => $message
        ];
        
        $this->logger->log('warning', "Validation failed for field: {$field}", $error_data);
        
        return new WP_Error(
            'validation_error',
            $message,
            $error_data
        );
    }
    
    public function handle_missing_required_field(string $field): WP_Error {
        $this->logger->log('warning', "Missing required field", ['field' => $field]);
        
        return new WP_Error(
            'missing_field',
            "The field '{$field}' is required.",
            ['field' => $field]
        );
    }
}
```

### Security Error Handler
```php
class IAT_Security_Error_Handler {
    private $logger;
    
    public function __construct() {
        $this->logger = new IAT_Logger();
    }
    
    public function handle_security_violation(string $type, string $description, array $context = []): WP_Error {
        $error_data = array_merge($context, [
            'type' => $type,
            'description' => $description
        ]);
        
        $this->logger->log('critical', "Security violation detected: {$type}", $error_data);
        
        // Block suspicious IP
        $this->block_suspicious_ip($context['ip'] ?? '');
        
        return new WP_Error(
            'security_violation',
            'Security check failed. Your request has been blocked.',
            $error_data
        );
    }
    
    private function block_suspicious_ip(string $ip): void {
        if (!empty($ip)) {
            $blocked_ips = get_option('iat_blocked_ips', []);
            if (!in_array($ip, $blocked_ips)) {
                $blocked_ips[] = $ip;
                update_option('iat_blocked_ips', $blocked_ips);
            }
        }
    }
}
```

## 🔄 Error Recovery Strategies

### Database Connection Recovery
```php
class IAT_DB_Recovery {
    public function attempt_db_recovery(): bool {
        global $wpdb;
        
        // Test database connection
        $result = $wpdb->get_var("SELECT 1");
        
        if ($result !== '1') {
            // Attempt to reconnect
            $wpdb->db_connect();
            
            // Test again
            $result = $wpdb->get_var("SELECT 1");
            return $result === '1';
        }
        
        return true;
    }
    
    public function handle_db_connection_error(): WP_Error {
        $this->logger->log('critical', 'Database connection lost');
        
        // Attempt recovery
        if ($this->attempt_db_recovery()) {
            $this->logger->log('info', 'Database connection restored');
            return new WP_Error('db_recovered', 'Database connection restored');
        }
        
        return new WP_Error(
            'db_connection_failed',
            'Unable to connect to the database. Please contact support.',
            ['action' => 'database_recovery_failed']
        );
    }
}
```

### API Failover Strategy
```php
class IAT_API_Failover {
    private $providers;
    private $current_provider;
    
    public function __construct(array $providers) {
        $this->providers = $providers;
        $this->current_provider = 0;
    }
    
    public function get_next_provider(): ?array {
        if ($this->current_provider >= count($this->providers)) {
            return null;
        }
        
        return $this->providers[$this->current_provider++];
    }
    
    public function handle_provider_failure(string $provider, string $error): void {
        $this->logger->log('error', "API provider failed", [
            'provider' => $provider,
            'error' => $error
        ]);
        
        // Mark provider as failed
        $failed_providers = get_option('iat_failed_providers', []);
        $failed_providers[$provider] = time();
        update_option('iat_failed_providers', $failed_providers);
    }
}
```

## 📊 Error Monitoring and Reporting

### Error Statistics
```php
class IAT_Error_Statistics {
    public function get_error_stats(): array {
        $stats = [
            'total_errors' => 0,
            'errors_by_type' => [],
            'errors_by_severity' => [],
            'top_error_messages' => [],
            'recent_errors' => []
        ];
        
        $log_file = WP_CONTENT_DIR . '/logs/iat-plugin.log';
        
        if (file_exists($log_file)) {
            $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                $entry = json_decode($line, true);
                if ($entry) {
                    $stats['total_errors']++;
                    $stats['errors_by_type'][$entry['context']][] = $entry;
                    $stats['errors_by_severity'][$entry['severity']][] = $entry;
                    $stats['recent_errors'][] = $entry;
                }
            }
            
            // Limit recent errors to last 100
            $stats['recent_errors'] = array_slice($stats['recent_errors'], -100);
        }
        
        return $stats;
    }
    
    public function generate_error_report(): string {
        $stats = $this->get_error_stats();
        
        $report = "Error Report - " . current_time('mysql') . "\n";
        $report .= "==========================================\n\n";
        
        $report .= "Total Errors: {$stats['total_errors']}\n\n";
        
        $report .= "Errors by Severity:\n";
        foreach ($stats['errors_by_severity'] as $severity => $errors) {
            $report .= "- {$severity}: " . count($errors) . "\n";
        }
        
        $report .= "\nErrors by Type:\n";
        foreach ($stats['errors_by_type'] as $type => $errors) {
            $report .= "- {$type}: " . count($errors) . "\n";
        }
        
        return $report;
    }
}
```

### Error Dashboard Widget
```php
class IAT_Error_Dashboard {
    public function __construct() {
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
    }
    
    public function add_dashboard_widget(): void {
        wp_add_dashboard_widget(
            'iat_error_widget',
            'Istanbul Airport Transfer - Error Status',
            [$this, 'render_error_widget']
        );
    }
    
    public function render_error_widget(): void {
        $stats = (new IAT_Error_Statistics())->get_error_stats();
        
        echo '<div class="iat-error-widget">';
        echo '<p><strong>Total Errors:</strong> ' . $stats['total_errors'] . '</p>';
        
        if ($stats['recent_errors']) {
            echo '<h4>Recent Errors:</h4>';
            echo '<ul>';
            foreach (array_slice($stats['recent_errors'], -5) as $error) {
                echo '<li style="color: ' . $this->get_severity_color($error['severity']) . '">';
                echo esc_html($error['message']) . ' (' . esc_html($error['severity']) . ')';
                echo '</li>';
            }
            echo '</ul>';
        }
        
        echo '</div>';
    }
    
    private function get_severity_color(string $severity): string {
        $colors = [
            'debug' => '#888',
            'info' => '#0073aa',
            'warning' => '#f5a623',
            'error' => '#d54e21',
            'critical' => '#ff0000'
        ];
        
        return $colors[$severity] ?? '#000';
    }
}
```

## 🔧 Error Handling Integration

### Global Error Handler
```php
class IAT_Global_Error_Handler {
    private $db_handler;
    private $api_handler;
    private $validation_handler;
    private $security_handler;
    
    public function __construct() {
        $this->db_handler = new IAT_DB_Error_Handler();
        $this->api_handler = new IAT_API_Error_Handler();
        $this->validation_handler = new IAT_Validation_Error_Handler();
        $this->security_handler = new IAT_Security_Error_Handler();
        
        set_error_handler([$this, 'handle_error']);
        set_exception_handler([$this, 'handle_exception']);
    }
    
    public function handle_error(int $severity, string $message, string $file, int $line): bool {
        if (!(error_reporting() & $severity)) {
            return false;
        }
        
        $error_data = [
            'severity' => $severity,
            'message' => $message,
            'file' => $file,
            'line' => $line
        ];
        
        $this->log_error('system_error', $message, $error_data);
        
        if (!WP_DEBUG) {
            return true; // Don't display error
        }
        
        return false; // Display error
    }
    
    public function handle_exception(Throwable $exception): void {
        $error_data = [
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
        
        $this->log_error('system_error', 'Uncaught exception', $error_data);
        
        if (!WP_DEBUG) {
            wp_die('An unexpected error occurred. Please try again later.');
        }
    }
    
    private function log_error(string $type, string $message, array $data): void {
        $logger = new IAT_Logger();
        $logger->log('error', $message, $data);
    }
}
```

### AJAX Error Handling
```php
class IAT_AJAX_Error_Handler {
    public function __construct() {
        add_action('wp_ajax_iat_booking', [$this, 'handle_booking_ajax']);
        add_action('wp_ajax_nopriv_iat_booking', [$this, 'handle_booking_ajax']);
    }
    
    public function handle_booking_ajax(): void {
        try {
            // Validate nonce
            if (!wp_verify_nonce($_POST['nonce'], 'iat_booking_form')) {
                $this->send_error_response('Security check failed', 403);
            }
            
            // Process booking
            $result = $this->process_booking($_POST);
            
            if (is_wp_error($result)) {
                $this->send_error_response($result->get_error_message(), 400, $result->get_error_data());
            }
            
            wp_send_json_success($result);
            
        } catch (Exception $e) {
            $this->log_exception($e);
            $this->send_error_response('An unexpected error occurred', 500);
        }
    }
    
    private function send_error_response(string $message, int $status_code, array $data = []): void {
        wp_send_json_error([
            'message' => $message,
            'status' => $status_code,
            'data' => $data
        ], $status_code);
    }
    
    private function log_exception(Exception $e): void {
        $logger = new IAT_Logger();
        $logger->log('error', $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
```

## 📋 Error Handling Best Practices

### Development Phase
- [ ] Enable debug mode for detailed error messages
- [ ] Use structured logging for all error types
- [ ] Implement proper error recovery strategies
- [ ] Test error scenarios with unit tests
- [ ] Validate all user inputs with clear error messages

### Testing Phase
- [ ] Test database connection failures
- [ ] Test API provider failures and failover
- [ ] Test validation error scenarios
- [ ] Test security violation handling
- [ ] Verify error logging functionality

### Production Phase
- [ ] Disable debug mode for user-facing errors
- [ ] Monitor error logs regularly
- [ ] Set up error alerting for critical issues
- [ ] Implement log rotation to prevent disk space issues
- [ ] Review error statistics weekly

## 🔧 Configuration Options

### Error Handling Settings
```php
class IAT_Error_Settings {
    public function get_error_config(): array {
        return [
            'log_level' => get_option('iat_log_level', 'error'),
            'log_rotation_days' => get_option('iat_log_rotation_days', 30),
            'enable_error_reporting' => get_option('iat_enable_error_reporting', true),
            'alert_critical_errors' => get_option('iat_alert_critical_errors', true),
            'max_log_size' => get_option('iat_max_log_size', 10485760), // 10MB
            'enable_debug_mode' => defined('WP_DEBUG') && WP_DEBUG
        ];
    }
    
    public function cleanup_old_logs(): void {
        $config = $this->get_error_config();
        $log_file = WP_CONTENT_DIR . '/logs/iat-plugin.log';
        
        if (file_exists($log_file)) {
            // Check file size
            if (filesize($log_file) > $config['max_log_size']) {
                // Rotate log file
                $backup_file = $log_file . '.' . date('Y-m-d');
                copy($log_file, $backup_file);
                file_put_contents($log_file, '');
            }
            
            // Clean old backup files
            $log_dir = dirname($log_file);
            $files = glob($log_dir . '/iat-plugin.log.*');
            
            foreach ($files as $file) {
                if (filemtime($file) < strtotime("-{$config['log_rotation_days']} days")) {
                    unlink($file);
                }
            }
        }
    }
}
```

This comprehensive error handling and logging system ensures that the Istanbul Airport Transfer plugin can handle various error scenarios gracefully while providing detailed information for debugging and monitoring purposes.