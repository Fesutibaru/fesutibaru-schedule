<?php
/**
 * Cleanup on plugin uninstall.
 *
 * Removes all options and transient caches created by the plugin.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Remove plugin options
delete_option( 'fesutibaru_schedule_options' );

// Remove all transients
global $wpdb;
$wpdb->query(
    "DELETE FROM {$wpdb->options}
     WHERE option_name LIKE '_transient_fesutibaru_%'
        OR option_name LIKE '_transient_timeout_fesutibaru_%'"
);
