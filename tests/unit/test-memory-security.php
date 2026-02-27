<?php
/**
 * IAT Memory Security Unit Tests
 * 
 * Unit tests for the IAT Memory Security system to ensure proper functionality
 * of encryption, access control, and security validation for sensitive memory operations.
 * 
 * @package Istanbul_Airport_Transfer
 * @subpackage Tests
 * @since 1.0.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Test class for IAT_Memory_Security
 */
class IAT_Memory_Security_Test extends TestCase {
    
    /**
     * Test encryption and decryption
     */
    public function test_encryption_decryption() {
        $test_data = [
            'api_key' => 'test_api_key_123456789',
            'secret' => 'very_secret_password',
            'config' => [
                'timeout' => 30,
                'debug' => true
            ]
        ];
        
        // Test encryption
        $encrypted = IAT_Memory_Security::encrypt_sensitive_data($test_data, 'test_key');
        $this->assertIsString($encrypted);
        $this->assertNotEmpty($encrypted);
        
        // Test decryption
        $decrypted = IAT_Memory_Security::decrypt_sensitive_data($encrypted, 'test_key');
        $this->assertEquals($test_data, $decrypted);
    }
    
    /**
     * Test encryption with different keys
     */
    public function test_encryption_with_different_keys() {
        $test_data = 'sensitive_data';
        
        $encrypted1 = IAT_Memory_Security::encrypt_sensitive_data($test_data, 'key1');
        $encrypted2 = IAT_Memory_Security::encrypt_sensitive_data($test_data, 'key2');
        
        // Different keys should produce different encrypted data
        $this->assertNotEquals($encrypted1, $encrypted2);
        
        // Each should decrypt correctly with its own key
        $this->assertEquals($test_data, IAT_Memory_Security::decrypt_sensitive_data($encrypted1, 'key1'));
        $this->assertEquals($test_data, IAT_Memory_Security::decrypt_sensitive_data($encrypted2, 'key2'));
    }
    
    /**
     * Test sensitive key detection
     */
    public function test_sensitive_key_detection() {
        $sensitive_keys = [
            'api_keys',
            'recaptcha_secret',
            'admin_email',
            'whatsapp_number',
            'phone_number',
            'payment_api_keys',
            'smtp_password'
        ];
        
        $non_sensitive_keys = [
            'cache_data',
            'config_general',
            'state_temp',
            'operational_stats'
        ];
        
        foreach ($sensitive_keys as $key) {
            $this->assertTrue(IAT_Memory_Security::is_sensitive_key($key));
        }
        
        foreach ($non_sensitive_keys as $key) {
            $this->assertFalse(IAT_Memory_Security::is_sensitive_key($key));
        }
    }
    
    /**
     * Test access validation
     */
    public function test_access_validation() {
        // Mock current user capabilities
        $this->mock_user_capabilities(['manage_options']);
        
        // Sensitive keys should require admin privileges
        $this->assertTrue(IAT_Memory_Security::validate_access('api_keys', 'read'));
        $this->assertTrue(IAT_Memory_Security::validate_access('api_keys', 'write'));
        
        // Operational keys should require edit_posts or higher
        $this->assertTrue(IAT_Memory_Security::validate_access('operational_data', 'read'));
        
        // State keys should require read access
        $this->assertTrue(IAT_Memory_Security::validate_access('state_data', 'read'));
        
        // Cache keys should be public
        $this->assertTrue(IAT_Memory_Security::validate_access('cache_data', 'read'));
    }
    
    /**
     * Test access validation with insufficient permissions
     */
    public function test_access_validation_insufficient_permissions() {
        // Mock user with limited capabilities
        $this->mock_user_capabilities(['read']);
        
        // Sensitive keys should be denied for non-admin users
        $this->assertFalse(IAT_Memory_Security::validate_access('api_keys', 'read'));
        $this->assertFalse(IAT_Memory_Security::validate_access('api_keys', 'write'));
        
        // Operational keys should be denied for users without edit_posts
        $this->assertFalse(IAT_Memory_Security::validate_access('operational_data', 'read'));
        
        // State keys should be allowed for users with read access
        $this->assertTrue(IAT_Memory_Security::validate_access('state_data', 'read'));
        
        // Cache keys should still be public
        $this->assertTrue(IAT_Memory_Security::validate_access('cache_data', 'read'));
    }
    
    /**
     * Test key sanitization
     */
    public function test_key_sanitization() {
        $test_keys = [
            'normal_key' => 'normal_key',
            'key-with-dashes' => 'key-with-dashes',
            'key_with_underscores' => 'key_with_underscores',
            'key123' => 'key123',
            'key with spaces' => 'keywithspaces',
            'key@#$%special' => 'keyspecial',
            'very_long_key_that_exceeds_limit_and_should_be_truncated' => 'very_long_key_that_exceeds_limit_and_should_be_trun'
        ];
        
        foreach ($test_keys as $input => $expected) {
            $sanitized = IAT_Memory_Security::sanitize_key($input);
            $this->assertEquals($expected, $sanitized);
        }
    }
    
    /**
     * Test data validation
     */
    public function test_data_validation() {
        // Test valid data
        $valid_data = [
            'api_keys' => [
                'yandex' => ['key1', 'key2'],
                'google' => ['key3', 'key4']
            ],
            'admin_email' => 'admin@example.com',
            'phone_number' => '+1234567890'
        ];
        
        $this->assertTrue(IAT_Memory_Security::validate_data('api_keys', $valid_data['api_keys']));
        $this->assertTrue(IAT_Memory_Security::validate_data('admin_email', $valid_data['admin_email']));
        $this->assertTrue(IAT_Memory_Security::validate_data('phone_number', $valid_data['phone_number']));
        
        // Test invalid data
        $invalid_data = [
            'api_keys' => 'invalid_string',
            'admin_email' => 'invalid-email',
            'phone_number' => 'invalid-phone'
        ];
        
        $this->assertFalse(IAT_Memory_Security::validate_data('api_keys', $invalid_data['api_keys']));
        $this->assertFalse(IAT_Memory_Security::validate_data('admin_email', $invalid_data['admin_email']));
        $this->assertFalse(IAT_Memory_Security::validate_data('phone_number', $invalid_data['phone_number']));
    }
    
    /**
     * Test sensitive data validation
     */
    public function test_sensitive_data_validation() {
        // Test valid API keys
        $valid_api_keys = [
            'yandex' => ['valid_key_123456789', 'another_valid_key_123456789'],
            'google' => ['google_key_123456789']
        ];
        
        $this->assertTrue(IAT_Memory_Security::validate_sensitive_data('api_keys', $valid_api_keys));
        
        // Test invalid API keys (too short)
        $invalid_api_keys = [
            'yandex' => ['short'],
            'google' => ['also_short']
        ];
        
        $this->assertFalse(IAT_Memory_Security::validate_sensitive_data('api_keys', $invalid_api_keys));
        
        // Test valid email
        $this->assertTrue(IAT_Memory_Security::validate_sensitive_data('admin_email', 'test@example.com'));
        
        // Test invalid email
        $this->assertFalse(IAT_Memory_Security::validate_sensitive_data('admin_email', 'invalid-email'));
        
        // Test valid phone number
        $this->assertTrue(IAT_Memory_Security::validate_sensitive_data('phone_number', '+1234567890'));
        $this->assertTrue(IAT_Memory_Security::validate_sensitive_data('phone_number', '(123) 456-7890'));
        $this->assertTrue(IAT_Memory_Security::validate_sensitive_data('phone_number', '123-456-7890'));
        
        // Test invalid phone number
        $this->assertFalse(IAT_Memory_Security::validate_sensitive_data('phone_number', 'invalid-phone'));
    }
    
    /**
     * Test encryption key generation
     */
    public function test_encryption_key_generation() {
        $key1 = IAT_Memory_Security::generate_encryption_key('test');
        $key2 = IAT_Memory_Security::generate_encryption_key('test');
        $key3 = IAT_Memory_Security::generate_encryption_key('different');
        
        // Same input should produce same key
        $this->assertEquals($key1, $key2);
        
        // Different input should produce different key
        $this->assertNotEquals($key1, $key3);
        
        // Key should be 32 characters (AES-256)
        $this->assertEquals(32, strlen($key1));
    }
    
    /**
     * Test IV generation
     */
    public function test_iv_generation() {
        $iv1 = IAT_Memory_Security::generate_iv('test');
        $iv2 = IAT_Memory_Security::generate_iv('test');
        $iv3 = IAT_Memory_Security::generate_iv('different');
        
        // Same input should produce same IV
        $this->assertEquals($iv1, $iv2);
        
        // Different input should produce different IV
        $this->assertNotEquals($iv1, $iv3);
        
        // IV should be 16 characters (AES-256-CBC)
        $this->assertEquals(16, strlen($iv1));
    }
    
    /**
     * Test data hashing
     */
    public function test_data_hashing() {
        $test_data = [
            'api_key' => 'test_key',
            'secret' => 'test_secret'
        ];
        
        $hash1 = IAT_Memory_Security::hash_sensitive_data($test_data);
        $hash2 = IAT_Memory_Security::hash_sensitive_data($test_data);
        
        // Same data should produce same hash
        $this->assertEquals($hash1, $hash2);
        
        // Hash should be 64 characters (SHA-256)
        $this->assertEquals(64, strlen($hash1));
        
        // Different data should produce different hash
        $different_data = ['api_key' => 'different_key'];
        $different_hash = IAT_Memory_Security::hash_sensitive_data($different_data);
        $this->assertNotEquals($hash1, $different_hash);
    }
    
    /**
     * Test suspicious activity detection
     */
    public function test_suspicious_activity_detection() {
        // Mock IP address
        $_SERVER['HTTP_CLIENT_IP'] = '127.0.0.1';
        
        // Test normal activity
        for ($i = 0; $i < 5; $i++) {
            $this->assertTrue(IAT_Memory_Security::check_suspicious_activity('read', 'api_keys'));
        }
        
        // Test suspicious activity (too many attempts)
        for ($i = 0; $i < 10; $i++) {
            IAT_Memory_Security::check_suspicious_activity('read', 'api_keys');
        }
        
        // Should be blocked after 10 attempts
        $this->assertFalse(IAT_Memory_Security::check_suspicious_activity('read', 'api_keys'));
    }
    
    /**
     * Test secure key generation
     */
    public function test_secure_key_generation() {
        $sensitive_key = 'api_keys';
        $non_sensitive_key = 'cache_data';
        
        $secure_sensitive = IAT_Memory_Security::secure_key($sensitive_key);
        $secure_non_sensitive = IAT_Memory_Security::secure_key($non_sensitive_key);
        
        // Sensitive key should be prefixed and hashed
        $this->assertStringStartsWith('iat_memory_', $secure_sensitive);
        $this->assertEquals(32, strlen(str_replace('iat_memory_', '', $secure_sensitive))); // MD5 hash length
        
        // Non-sensitive key should remain unchanged
        $this->assertEquals($non_sensitive_key, $secure_non_sensitive);
    }
    
    /**
     * Test cleanup sensitive data
     */
    public function test_cleanup_sensitive_data() {
        // Set up test data
        update_option('iat_config_api_keys', 'test_api_keys');
        set_transient('iat_state_api_keys', 'test_state_data', 3600);
        set_transient('iat_cache_api_keys', 'test_cache_data', 3600);
        update_option('iat_operational_api_keys', 'test_operational_data');
        
        // Clean up sensitive data
        IAT_Memory_Security::cleanup_sensitive_data('api_keys');
        
        // Verify data is cleaned up
        $this->assertFalse(get_option('iat_config_api_keys'));
        $this->assertFalse(get_transient('iat_state_api_keys'));
        $this->assertFalse(get_transient('iat_cache_api_keys'));
        $this->assertFalse(get_option('iat_operational_api_keys'));
    }
    
    /**
     * Test empty data handling
     */
    public function test_empty_data_handling() {
        // Test encryption with empty data
        $encrypted_empty = IAT_Memory_Security::encrypt_sensitive_data('', 'test');
        $this->assertEquals('', $encrypted_empty);
        
        // Test decryption with empty data
        $decrypted_empty = IAT_Memory_Security::decrypt_sensitive_data('', 'test');
        $this->assertEquals('', $decrypted_empty);
        
        // Test decryption with invalid data
        $decrypted_invalid = IAT_Memory_Security::decrypt_sensitive_data('invalid_base64_data', 'test');
        $this->assertEquals('', $decrypted_invalid);
    }
    
    /**
     * Mock user capabilities for testing
     * 
     * @param array $capabilities User capabilities
     */
    private function mock_user_capabilities($capabilities) {
        // This is a simplified mock - in a real test environment,
        // you would use WordPress testing utilities or a more sophisticated mocking system
        global $wp_roles;
        
        if (!isset($wp_roles)) {
            $wp_roles = new WP_Roles();
        }
        
        // Add capabilities to the administrator role for testing
        foreach ($capabilities as $capability) {
            $wp_roles->add_cap('administrator', $capability);
        }
    }
}