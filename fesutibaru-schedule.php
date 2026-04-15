<?php
/**
 * Plugin Name: Fesutibaru Schedule
 * Plugin URI:  https://github.com/Fesutibaru/fesutibaru-schedule
 * Description: Display your festival schedule from the Fesutibaru platform using a simple shortcode.
 * Version:     0.1.11
 * Author:      Fesutibaru
 * Author URI:  https://fesutibaru.com
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: fesutibaru-schedule
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'FESUTIBARU_SCHEDULE_VERSION', '0.1.11' );
define( 'FESUTIBARU_SCHEDULE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'FESUTIBARU_SCHEDULE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load classes
require_once FESUTIBARU_SCHEDULE_PLUGIN_DIR . 'includes/class-api-client.php';
require_once FESUTIBARU_SCHEDULE_PLUGIN_DIR . 'includes/class-cache.php';
require_once FESUTIBARU_SCHEDULE_PLUGIN_DIR . 'includes/class-settings.php';
require_once FESUTIBARU_SCHEDULE_PLUGIN_DIR . 'includes/class-shortcode-renderer.php';

/**
 * Initialise the plugin.
 */
function fesutibaru_schedule_init() {
    // Register settings page
    $settings = new Fesutibaru_Schedule_Settings();
    $settings->init();

    // Register shortcode
    $renderer = new Fesutibaru_Schedule_Shortcode_Renderer();
    add_shortcode( 'fesutibaru_schedule', array( $renderer, 'render' ) );
}
add_action( 'init', 'fesutibaru_schedule_init' );

/**
 * Enqueue front-end assets when the shortcode is present.
 */
function fesutibaru_schedule_enqueue_assets() {
    global $post;

    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'fesutibaru_schedule' ) ) {
        wp_enqueue_style(
            'fesutibaru-schedule',
            FESUTIBARU_SCHEDULE_PLUGIN_URL . 'assets/css/schedule.css',
            array(),
            FESUTIBARU_SCHEDULE_VERSION
        );

        wp_enqueue_script(
            'fesutibaru-schedule',
            FESUTIBARU_SCHEDULE_PLUGIN_URL . 'assets/js/schedule.js',
            array(),
            FESUTIBARU_SCHEDULE_VERSION,
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'fesutibaru_schedule_enqueue_assets' );

/**
 * Add settings link on the Plugins page.
 */
function fesutibaru_schedule_settings_link( $links ) {
    $settings_link = '<a href="' . admin_url( 'options-general.php?page=fesutibaru-schedule' ) . '">'
        . __( 'Settings', 'fesutibaru-schedule' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'fesutibaru_schedule_settings_link' );
