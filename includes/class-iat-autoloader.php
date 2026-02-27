<?php
/**
 * IAT Autoloader
 *
 * @package     Istanbul_Airport_Transfer
 * @subpackage  Core
 * @since       1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * IAT Autoloader Class
 *
 * Handles autoloading of plugin classes following PSR-4 standard.
 */
class IAT_Autoloader {

    /**
     * Initialize autoloader
     */
    public static function init() {
        spl_autoload_register([self::class, 'autoload']);
    }

    /**
     * Autoload plugin classes
     *
     * @param string $class_name Class name to load
     */
    public static function autoload($class_name) {
        // Only handle our plugin classes
        if (strpos($class_name, 'IAT_') !== 0) {
            return;
        }

        // Convert class name to file path
        $prefix = 'IAT_';
        $base_dir = IAT_PLUGIN_DIR . 'includes/';
        
        // Remove prefix and convert to file path
        $relative_class = substr($class_name, strlen($prefix));
        $relative_class = str_replace('_', '-', $relative_class);
        
        // Convert namespace separators to directory separators
        $file = $base_dir . 'class-' . strtolower($relative_class) . '.php';
        
        // If file exists, require it
        if (file_exists($file)) {
            require_once $file;
        }
    }
}

// Initialize autoloader
IAT_Autoloader::init();