<?php
/**
 * IAT Deactivator
 *
 * @package     Istanbul_Airport_Transfer
 * @subpackage  Core
 * @since       1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * IAT Deactivator Class
 *
 * Handles plugin deactivation tasks.
 */
class IAT_Deactivator {

    /**
     * Deactivate the plugin
     */
    public static function deactivate() {
        // Clear scheduled cron jobs
        self::clear_scheduled_events();

        // Clear transients
        self::clear_plugin_transients();

        // Optionally preserve data (don't delete tables - that's for uninstall)
        // flush_rewrite_rules(); // Uncomment if needed
    }

    /**
     * Clear scheduled events
     */
    private static function clear_scheduled_events() {
        // Clear all scheduled cron jobs for the plugin
        wp_clear_scheduled_hook('iat_auto_confirm_pending_bookings');
        wp_clear_scheduled_hook('iat_cleanup_old_data');
        wp_clear_scheduled_hook('iat_cleanup_geocache');
        wp_clear_scheduled_hook('iat_cleanup_expired_sessions');
    }

    /**
     * Clear plugin transients
     */
    private static function clear_plugin_transients() {
        global $wpdb;

        // Clear all plugin-related transients
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_iat_') . '%'
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('_transient_timeout_iat_') . '%'
            )
        );
    }
}