<?php
/**
 * Caching layer using WordPress Transients API.
 *
 * Each unique set of shortcode parameters produces a separate cache entry.
 * Stale cache is served when the API is unreachable or rate-limited.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Fesutibaru_Schedule_Cache {

    /** @var int Cache TTL in seconds. */
    private $ttl;

    /**
     * @param int $ttl_minutes  Cache duration in minutes.
     */
    public function __construct( $ttl_minutes = 5 ) {
        $this->ttl = max( 1, (int) $ttl_minutes ) * MINUTE_IN_SECONDS;
    }

    /**
     * Build a cache key from shortcode parameters.
     *
     * @param array $params  Shortcode attributes.
     * @return string
     */
    public function make_key( $params ) {
        // Sort to ensure consistent keys regardless of parameter order
        ksort( $params );
        return 'fesutibaru_' . md5( wp_json_encode( $params ) );
    }

    /**
     * Get cached data.
     *
     * @param string $key  Cache key.
     * @return mixed|false  Cached data or false if not found / expired.
     */
    public function get( $key ) {
        return get_transient( $key );
    }

    /**
     * Get stale (backup) cache — used when the API fails.
     *
     * We store a second copy with a much longer TTL so we can serve
     * something even when the API is down.
     *
     * @param string $key  Cache key.
     * @return mixed|false
     */
    public function get_stale( $key ) {
        return get_transient( $key . '_stale' );
    }

    /**
     * Store data in cache.
     *
     * @param string $key   Cache key.
     * @param mixed  $data  Data to cache.
     */
    public function set( $key, $data ) {
        set_transient( $key, $data, $this->ttl );
        // Stale backup: 24 hours — served when the API is unreachable
        set_transient( $key . '_stale', $data, DAY_IN_SECONDS );
    }

    /**
     * Clear all Fesutibaru transients.
     *
     * Used by the "Clear Cache" button on the settings page.
     */
    public static function clear_all() {
        global $wpdb;

        // Delete both fresh and stale transients
        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '_transient_fesutibaru_%'
                OR option_name LIKE '_transient_timeout_fesutibaru_%'"
        );
    }
}
