<?php
/**
 * IAT Memory Bank Unit Tests
 * 
 * Unit tests for the IAT Memory Bank system to ensure proper functionality
 * of configuration, state, cache, and operational memory management.
 * 
 * @package Istanbul_Airport_Transfer
 * @subpackage Tests
 * @since 1.0.0
 */

use PHPUnit\Framework\TestCase;

/**
 * Test class for IAT_Memory_Bank
 */
class IAT_Memory_Bank_Test extends TestCase {
    
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
     * Test singleton pattern
     */
    public function test_singleton_pattern() {
        $instance1 = IAT_Memory_Bank::get_instance();
        $instance2 = IAT_Memory_Bank::get_instance();
        
        $this->assertSame($instance1, $instance2);
    }
    
    /**
     * Test configuration memory operations
     */
    public function test_configuration_memory() {
        // Test setting configuration
        $test_config = [
            'api_key' => 'test_key_123',
            'timeout' => 30,
            'debug' => true
        ];
        
        $this->memory_bank->set_config('test_config', $test_config);
        
        // Test getting configuration
        $retrieved_config = $this->memory_bank->get_config('test_config');
        $this->assertEquals($test_config, $retrieved_config);
        
        // Test default value
        $default_value = $this->memory_bank->get_config('nonexistent_key', 'default');
        $this->assertEquals('default', $default_value);
        
        // Test deletion
        $this->memory_bank->delete_config('test_config');
        $deleted_value = $this->memory_bank->get_config('test_config', 'not_found');
        $this->assertEquals('not_found', $deleted_value);
    }
    
    /**
     * Test state memory operations
     */
    public function test_state_memory() {
        $test_state = [
            'user_id' => 123,
            'session_data' => 'test_session'
        ];
        
        // Test setting state with default TTL
        $this->memory_bank->set_state('test_state', $test_state);
        
        // Test getting state
        $retrieved_state = $this->memory_bank->get_state('test_state');
        $this->assertEquals($test_state, $retrieved_state);
        
        // Test setting state with custom TTL
        $this->memory_bank->set_state('test_state_custom', $test_state, 7200);
        
        // Test deletion
        $this->memory_bank->delete_state('test_state');
        $deleted_state = $this->memory_bank->get_state('test_state', 'not_found');
        $this->assertEquals('not_found', $deleted_state);
    }
    
    /**
     * Test cache memory operations
     */
    public function test_cache_memory() {
        $test_cache = [
            'geocoding_result' => [
                'lat' => 41.0082,
                'lng' => 28.9784,
                'address' => 'Istanbul, Turkey'
            ]
        ];
        
        // Test setting cache with default TTL
        $this->memory_bank->set_cache('test_cache', $test_cache);
        
        // Test getting cache
        $retrieved_cache = $this->memory_bank->get_cache('test_cache');
        $this->assertEquals($test_cache, $retrieved_cache);
        
        // Test setting cache with specific TTL type
        $this->memory_bank->set_cache('test_cache_geocoding', $test_cache, 'geocoding');
        
        // Test deletion
        $this->memory_bank->delete_cache('test_cache');
        $deleted_cache = $this->memory_bank->get_cache('test_cache');
        $this->assertFalse($deleted_cache);
    }
    
    /**
     * Test operational memory operations
     */
    public function test_operational_memory() {
        // Test setting operational data
        $test_operational = [
            'daily_bookings' => 10,
            'monthly_bookings' => 150,
            'total_bookings' => 1250
        ];
        
        $this->memory_bank->set_operational('booking_stats', $test_operational);
        
        // Test getting operational data
        $retrieved_operational = $this->memory_bank->get_operational('booking_stats');
        $this->assertEquals($test_operational, $retrieved_operational);
        
        // Test counter increment
        $initial_count = $this->memory_bank->get_operational('test_counter', 0);
        $new_count = $this->memory_bank->increment_counter('test_counter', 5);
        $this->assertEquals($initial_count + 5, $new_count);
        
        // Test deletion
        $this->memory_bank->delete_operational('booking_stats');
        $deleted_operational = $this->memory_bank->get_operational('booking_stats', 'not_found');
        $this->assertEquals('not_found', $deleted_operational);
    }
    
    /**
     * Test bulk operations
     */
    public function test_bulk_operations() {
        // Set up test data
        $this->memory_bank->set_config('bulk_test_1', 'value1');
        $this->memory_bank->set_config('bulk_test_2', 'value2');
        $this->memory_bank->set_state('bulk_state_1', 'state1');
        $this->memory_bank->set_cache('bulk_cache_1', 'cache1');
        $this->memory_bank->set_operational('bulk_op_1', 'op1');
        
        // Test clear cache with pattern
        $this->memory_bank->clear_cache('bulk');
        $this->assertFalse($this->memory_bank->get_cache('bulk_cache_1'));
        
        // Test clear state
        $this->memory_bank->clear_state();
        $this->assertFalse($this->memory_bank->get_state('bulk_state_1'));
        
        // Test clear operational
        $this->memory_bank->clear_operational();
        $this->assertFalse($this->memory_bank->get_operational('bulk_op_1'));
        
        // Test clear all
        $this->memory_bank->clear_all();
        $this->assertFalse($this->memory_bank->get_config('bulk_test_1'));
        $this->assertFalse($this->memory_bank->get_config('bulk_test_2'));
    }
    
    /**
     * Test memory status
     */
    public function test_memory_status() {
        // Set up test data
        $this->memory_bank->set_config('status_test', 'config_value');
        $this->memory_bank->set_state('status_test', 'state_value');
        $this->memory_bank->set_cache('status_test', 'cache_value');
        $this->memory_bank->set_operational('status_test', 'op_value');
        
        $status = $this->memory_bank->get_status();
        
        $this->assertArrayHasKey('config_size', $status);
        $this->assertArrayHasKey('state_size', $status);
        $this->assertArrayHasKey('cache_size', $status);
        $this->assertArrayHasKey('operational_size', $status);
        $this->assertArrayHasKey('memory_usage', $status);
        $this->assertArrayHasKey('memory_peak', $status);
        $this->assertArrayHasKey('last_cleanup', $status);
        
        $this->assertIsInt($status['config_size']);
        $this->assertIsInt($status['state_size']);
        $this->assertIsInt($status['cache_size']);
        $this->assertIsInt($status['operational_size']);
        $this->assertIsInt($status['memory_usage']);
        $this->assertIsInt($status['memory_peak']);
        $this->assertIsString($status['last_cleanup']);
    }
    
    /**
     * Test cache TTL configuration
     */
    public function test_cache_ttl_configuration() {
        $cache_config = [
            'geocoding' => 86400,
            'zone_detection' => 604800,
            'pricing_matrix' => 3600,
            'regions' => 1800,
            'api_usage' => 3600
        ];
        
        // Test that cache config is properly set
        $reflection = new ReflectionClass($this->memory_bank);
        $cache_config_property = $reflection->getProperty('cache_config');
        $cache_config_property->setAccessible(true);
        
        $actual_config = $cache_config_property->getValue($this->memory_bank);
        $this->assertEquals($cache_config, $actual_config);
    }
    
    /**
     * Test memory bank reset
     */
    public function test_memory_bank_reset() {
        // Set up test data
        $this->memory_bank->set_config('reset_test', 'test_value');
        $this->memory_bank->set_state('reset_state', 'state_value');
        $this->memory_bank->set_cache('reset_cache', 'cache_value');
        $this->memory_bank->set_operational('reset_op', 'op_value');
        
        // Reset memory bank
        $this->memory_bank->reset();
        
        // Verify all data is cleared
        $this->assertFalse($this->memory_bank->get_config('reset_test'));
        $this->assertFalse($this->memory_bank->get_state('reset_state'));
        $this->assertFalse($this->memory_bank->get_cache('reset_cache'));
        $this->assertFalse($this->memory_bank->get_operational('reset_op'));
    }
    
    /**
     * Test data validation
     */
    public function test_data_validation() {
        // Test large data rejection
        $large_data = str_repeat('x', 2 * 1024 * 1024); // 2MB string
        
        // This should not throw an exception but should be handled gracefully
        $this->memory_bank->set_config('large_data', $large_data);
        $retrieved_data = $this->memory_bank->get_config('large_data');
        
        // The data should be stored (WordPress handles large data)
        $this->assertEquals($large_data, $retrieved_data);
    }
    
    /**
     * Test concurrent access
     */
    public function test_concurrent_access() {
        // Test that multiple operations don't interfere
        $this->memory_bank->set_config('concurrent_1', 'value1');
        $this->memory_bank->set_config('concurrent_2', 'value2');
        $this->memory_bank->set_state('concurrent_1', 'state1');
        $this->memory_bank->set_state('concurrent_2', 'state2');
        
        $config1 = $this->memory_bank->get_config('concurrent_1');
        $config2 = $this->memory_bank->get_config('concurrent_2');
        $state1 = $this->memory_bank->get_state('concurrent_1');
        $state2 = $this->memory_bank->get_state('concurrent_2');
        
        $this->assertEquals('value1', $config1);
        $this->assertEquals('value2', $config2);
        $this->assertEquals('state1', $state1);
        $this->assertEquals('state2', $state2);
    }
    
    /**
     * Test cleanup operations
     */
    public function test_cleanup_operations() {
        // Set up test data
        $this->memory_bank->set_config('cleanup_test', 'test_value');
        $this->memory_bank->set_state('cleanup_state', 'state_value');
        $this->memory_bank->set_cache('cleanup_cache', 'cache_value');
        $this->memory_bank->set_operational('cleanup_op', 'op_value');
        
        // Test cleanup
        $this->memory_bank->cleanup_memory();
        
        // Check that cleanup timestamp is set
        $last_cleanup = $this->memory_bank->get_operational('last_cleanup');
        $this->assertNotEmpty($last_cleanup);
        $this->assertIsString($last_cleanup);
    }
}