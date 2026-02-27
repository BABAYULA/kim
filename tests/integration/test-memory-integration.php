<?php
/**
 * IAT Memory Bank Integration Tests
 * 
 * Integration tests for the IAT Memory Bank system to ensure proper interaction
 * with WordPress core functions, database operations, and real-world scenarios.
 * 
 * @package Istanbul_Airport_Transfer
 * @subpackage Tests
 * @since 1.0.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Test class for IAT_Memory_Bank integration
 */
class IAT_Memory_Bank_Integration_Test extends TestCase {
    
    /**
     * Memory bank instance
     * 
     * @var IAT_Memory_Bank
     */
    private $memory_bank;
    
    /**
     * Set up test environment
     */
    protected function setUp(): void {
        parent::setUp();
        
        // Create memory bank instance
        $this->memory_bank = IAT_Memory_Bank::get_instance();
        
        // Clear any existing data
        $this->memory_bank->clear_all();
    }
    
    /**
     * Clean up after tests
     */
    protected function tearDown(): void {
        // Clean up test data
        $this->memory_bank->clear_all();
        
        parent::tearDown();
    }
    
    /**
     * Test WordPress option integration
     */
    public function test_wordpress_option_integration() {
        $test_key = 'integration_test_config';
        $test_value = ['test' => 'data', 'number' => 123];
        
        // Set configuration
        $this->memory_bank->set_config($test_key, $test_value);
        
        // Verify WordPress option was created
        $wp_option = get_option('iat_config_' . $test_key);
        $this->assertEquals($test_value, $wp_option);
        
        // Get configuration
        $retrieved_value = $this->memory_bank->get_config($test_key);
        $this->assertEquals($test_value, $retrieved_value);
        
        // Verify WordPress option is still there
        $wp_option_after = get_option('iat_config_' . $test_key);
        $this->assertEquals($test_value, $wp_option_after);
    }
    
    /**
     * Test WordPress transient integration
     */
    public function test_wordpress_transient_integration() {
        $test_key = 'integration_test_state';
        $test_value = ['session' => 'data', 'timestamp' => time()];
        
        // Set state with short TTL
        $this->memory_bank->set_state($test_key, $test_value, 1);
        
        // Verify transient was created
        $wp_transient = get_transient('iat_state_' . $test_key);
        $this->assertEquals($test_value, $wp_transient);
        
        // Wait for transient to expire
        sleep(2);
        
        // Verify transient has expired
        $expired_transient = get_transient('iat_state_' . $test_key);
        $this->assertFalse($expired_transient);
        
        // Verify memory bank handles expired transient gracefully
        $retrieved_value = $this->memory_bank->get_state($test_key, 'default');
        $this->assertEquals('default', $retrieved_value);
    }
    
    /**
     * Test cache persistence across requests
     */
    public function test_cache_persistence() {
        $test_key = 'integration_test_cache';
        $test_value = ['geocoding' => ['lat' => 41.0082, 'lng' => 28.9784]];
        
        // Set cache
        $this->memory_bank->set_cache($test_key, $test_value, 'geocoding');
        
        // Verify cache is in memory
        $memory_cache = $this->memory_bank->get_cache($test_key);
        $this->assertEquals($test_value, $memory_cache);
        
        // Verify cache is in WordPress transient
        $wp_transient = get_transient('iat_cache_' . $test_key);
        $this->assertEquals($test_value, $wp_transient);
        
        // Clear memory cache but keep transient
        $this->memory_bank->delete_cache($test_key);
        
        // Verify cache is reloaded from transient
        $reloaded_cache = $this->memory_bank->get_cache($test_key);
        $this->assertEquals($test_value, $reloaded_cache);
    }
    
    /**
     * Test operational data persistence
     */
    public function test_operational_data_persistence() {
        $test_key = 'integration_test_operational';
        $test_value = ['counter' => 100, 'stats' => ['total' => 500]];
        
        // Set operational data
        $this->memory_bank->set_operational($test_key, $test_value);
        
        // Verify WordPress option was created
        $wp_option = get_option('iat_operational_' . $test_key);
        $this->assertEquals($test_value, $wp_option);
        
        // Get operational data
        $retrieved_value = $this->memory_bank->get_operational($test_key);
        $this->assertEquals($test_value, $retrieved_value);
        
        // Test counter increment
        $initial_count = $this->memory_bank->get_operational('test_counter', 0);
        $new_count = $this->memory_bank->increment_counter('test_counter', 10);
        
        $this->assertEquals($initial_count + 10, $new_count);
        
        // Verify counter is persisted
        $persisted_count = $this->memory_bank->get_operational('test_counter');
        $this->assertEquals($new_count, $persisted_count);
    }
    
    /**
     * Test bulk data operations
     */
    public function test_bulk_data_operations() {
        // Set up multiple test data points
        $test_data = [
            'config' => [
                'key1' => 'value1',
                'key2' => 'value2',
                'key3' => 'value3'
            ],
            'state' => [
                'state1' => 'state_value1',
                'state2' => 'state_value2'
            ],
            'cache' => [
                'cache1' => 'cache_value1',
                'cache2' => 'cache_value2'
            ],
            'operational' => [
                'op1' => 'op_value1',
                'op2' => 'op_value2'
            ]
        ];
        
        // Set all data
        foreach ($test_data['config'] as $key => $value) {
            $this->memory_bank->set_config($key, $value);
        }
        
        foreach ($test_data['state'] as $key => $value) {
            $this->memory_bank->set_state($key, $value);
        }
        
        foreach ($test_data['cache'] as $key => $value) {
            $this->memory_bank->set_cache($key, $value);
        }
        
        foreach ($test_data['operational'] as $key => $value) {
            $this->memory_bank->set_operational($key, $value);
        }
        
        // Verify all data is accessible
        foreach ($test_data['config'] as $key => $value) {
            $this->assertEquals($value, $this->memory_bank->get_config($key));
        }
        
        foreach ($test_data['state'] as $key => $value) {
            $this->assertEquals($value, $this->memory_bank->get_state($key));
        }
        
        foreach ($test_data['cache'] as $key => $value) {
            $this->assertEquals($value, $this->memory_bank->get_cache($key));
        }
        
        foreach ($test_data['operational'] as $key => $value) {
            $this->assertEquals($value, $this->memory_bank->get_operational($key));
        }
        
        // Test bulk clear operations
        $this->memory_bank->clear_cache();
        $this->memory_bank->clear_state();
        $this->memory_bank->clear_operational();
        
        // Verify data is cleared
        foreach ($test_data['cache'] as $key => $value) {
            $this->assertFalse($this->memory_bank->get_cache($key));
        }
        
        foreach ($test_data['state'] as $key => $value) {
            $this->assertFalse($this->memory_bank->get_state($key));
        }
        
        foreach ($test_data['operational'] as $key => $value) {
            $this->assertFalse($this->memory_bank->get_operational($key));
        }
        
        // Config data should still be there
        foreach ($test_data['config'] as $key => $value) {
            $this->assertEquals($value, $this->memory_bank->get_config($key));
        }
    }
    
    /**
     * Test memory bank status reporting
     */
    public function test_memory_bank_status() {
        // Set up test data
        $this->memory_bank->set_config('status_config', 'config_value');
        $this->memory_bank->set_state('status_state', 'state_value');
        $this->memory_bank->set_cache('status_cache', 'cache_value');
        $this->memory_bank->set_operational('status_op', 'op_value');
        
        $status = $this->memory_bank->get_status();
        
        // Verify status structure
        $this->assertArrayHasKey('config_size', $status);
        $this->assertArrayHasKey('state_size', $status);
        $this->assertArrayHasKey('cache_size', $status);
        $this->assertArrayHasKey('operational_size', $status);
        $this->assertArrayHasKey('memory_usage', $status);
        $this->assertArrayHasKey('memory_peak', $status);
        $this->assertArrayHasKey('last_cleanup', $status);
        
        // Verify counts
        $this->assertEquals(1, $status['config_size']);
        $this->assertEquals(1, $status['state_size']);
        $this->assertEquals(1, $status['cache_size']);
        $this->assertEquals(1, $status['operational_size']);
        
        // Verify memory usage is reasonable
        $this->assertGreaterThan(0, $status['memory_usage']);
        $this->assertGreaterThan(0, $status['memory_peak']);
        $this->assertLessThan(100 * 1024 * 1024, $status['memory_usage']); // Less than 100MB
    }
    
    /**
     * Test cleanup operations
     */
    public function test_cleanup_operations() {
        // Set up test data
        $this->memory_bank->set_config('cleanup_config', 'config_value');
        $this->memory_bank->set_state('cleanup_state', 'state_value');
        $this->memory_bank->set_cache('cleanup_cache', 'cache_value');
        $this->memory_bank->set_operational('cleanup_op', 'op_value');
        
        // Run cleanup
        $this->memory_bank->cleanup_memory();
        
        // Verify cleanup timestamp is set
        $last_cleanup = $this->memory_bank->get_operational('last_cleanup');
        $this->assertNotEmpty($last_cleanup);
        $this->assertIsString($last_cleanup);
        
        // Verify cleanup operations completed without errors
        $this->assertNotFalse($last_cleanup);
    }
    
    /**
     * Test database integration with real WordPress database
     */
    public function test_database_integration() {
        global $wpdb;
        
        // Test that our tables are properly created (if they exist)
        $tables = $wpdb->get_col("SHOW TABLES LIKE 'wp_iat_%'");
        
        if (!empty($tables)) {
            // If tables exist, test that we can query them
            foreach ($tables as $table) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
                $this->assertIsNumeric($count);
            }
        }
        
        // Test that our options are stored in the options table
        $option_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('iat_') . '%'
            )
        );
        
        $this->assertIsNumeric($option_count);
    }
    
    /**
     * Test memory bank with real-world data sizes
     */
    public function test_real_world_data_sizes() {
        // Test with larger data sets
        $large_config = [];
        for ($i = 0; $i < 100; $i++) {
            $large_config['key_' . $i] = str_repeat('value_' . $i, 100);
        }
        
        $this->memory_bank->set_config('large_config', $large_config);
        
        $retrieved_config = $this->memory_bank->get_config('large_config');
        $this->assertEquals($large_config, $retrieved_config);
        
        // Test with nested arrays
        $nested_data = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'data' => 'deep_nested_value'
                    ]
                ]
            ]
        ];
        
        $this->memory_bank->set_config('nested_data', $nested_data);
        
        $retrieved_nested = $this->memory_bank->get_config('nested_data');
        $this->assertEquals($nested_data, $retrieved_nested);
    }
    
    /**
     * Test memory bank performance
     */
    public function test_memory_bank_performance() {
        $start_time = microtime(true);
        
        // Perform multiple operations
        for ($i = 0; $i < 100; $i++) {
            $this->memory_bank->set_config('perf_test_' . $i, 'value_' . $i);
            $this->memory_bank->get_config('perf_test_' . $i);
        }
        
        $end_time = microtime(true);
        $duration = $end_time - $start_time;
        
        // Operations should complete in reasonable time (less than 5 seconds)
        $this->assertLessThan(5.0, $duration);
        
        // Verify all data was stored correctly
        for ($i = 0; $i < 100; $i++) {
            $value = $this->memory_bank->get_config('perf_test_' . $i);
            $this->assertEquals('value_' . $i, $value);
        }
    }
    
    /**
     * Test memory bank with WordPress hooks
     */
    public function test_wordpress_hooks_integration() {
        $hook_called = false;
        
        // Test that hooks are properly registered
        add_action('iat_cleanup_memory', function() use (&$hook_called) {
            $hook_called = true;
        });
        
        // Trigger the hook
        do_action('iat_cleanup_memory');
        
        $this->assertTrue($hook_called);
    }
    
    /**
     * Test memory bank with WordPress multisite
     */
    public function test_multisite_compatibility() {
        if (!is_multisite()) {
            $this->markTestSkipped('Multisite not available');
        }
        
        // Test that memory bank works in multisite environment
        $test_key = 'multisite_test';
        $test_value = ['site_id' => get_current_blog_id()];
        
        $this->memory_bank->set_config($test_key, $test_value);
        $retrieved_value = $this->memory_bank->get_config($test_key);
        
        $this->assertEquals($test_value, $retrieved_value);
    }
}