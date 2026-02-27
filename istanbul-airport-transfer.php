<?php
/**
 * Istanbul Airport Transfer
 *
 * @package           Istanbul_Airport_Transfer
 * @author            Istanbul Airport Transfer Team
 * @copyright         2023 Istanbul Airport Transfer
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Istanbul Airport Transfer
 * Plugin URI:        https://istanbulairporttransfer.com
 * Description:       A comprehensive WordPress plugin for managing airport transfer reservations between Istanbul Airport (IST) and Sabiha Gökçen Airport (SAW) with 13 service zones across Istanbul.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Istanbul Airport Transfer Team
 * Author URI:        https://istanbulairporttransfer.com
 * Text Domain:       istanbul-airport-transfer
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Define plugin constants
 */
define('IAT_PLUGIN_FILE', __FILE__);
define('IAT_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('IAT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('IAT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('IAT_VERSION', '1.0.0');

/**
 * Load plugin classes
 */
require_once IAT_PLUGIN_DIR . 'includes/class-iat-autoloader.php';
require_once IAT_PLUGIN_DIR . 'includes/class-iat-main.php';
require_once IAT_PLUGIN_DIR . 'includes/class-iat-activator.php';
require_once IAT_PLUGIN_DIR . 'includes/class-iat-deactivator.php';

/**
 * Plugin activation hook
 */
register_activation_hook(__FILE__, ['IAT_Activator', 'activate']);

/**
 * Plugin deactivation hook
 */
register_deactivation_hook(__FILE__, ['IAT_Deactivator', 'deactivate']);

/**
 * Initialize the plugin
 */
function iat_main() {
    return IAT_Main::get_instance();
}

/**
 * Load the plugin after WordPress has loaded
 */
add_action('plugins_loaded', 'iat_main');