<?php
/**
 * HTTP client for the Fesutibaru Public API v1.
 *
 * All requests are server-side via wp_remote_get(). The API key is never
 * exposed in front-end HTML or JavaScript.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Fesutibaru_Schedule_API_Client {

    /** @var string */
    private $base_url;

    /** @var string */
    private $api_key;

    /**
     * @param string $base_url  API base URL (e.g. https://planner.fesutibaru.com)
     * @param string $api_key   Bearer token
     */
    public function __construct( $base_url, $api_key ) {
        $this->base_url = rtrim( $base_url, '/' );
        $this->api_key  = $api_key;
    }

    /**
     * Fetch events from the API.
     *
     * @param array $params  Query parameters (search, limit, offset, venue, date, etc.)
     * @return array|WP_Error  Array of event objects or WP_Error on failure.
     */
    public function get_events( $params = array() ) {
        return $this->get( '/api/v1/events', $params );
    }

    /**
     * Fetch venues from the API.
     *
     * @param array $params  Query parameters.
     * @return array|WP_Error
     */
    public function get_venues( $params = array() ) {
        return $this->get( '/api/v1/venues', $params );
    }

    /**
     * Fetch people from the API.
     *
     * @param array $params  Query parameters.
     * @return array|WP_Error
     */
    public function get_people( $params = array() ) {
        return $this->get( '/api/v1/people', $params );
    }

    /**
     * Make a GET request to the API.
     *
     * @param string $endpoint  API path (e.g. /api/v1/events).
     * @param array  $params    Query parameters.
     * @return array|WP_Error
     */
    private function get( $endpoint, $params = array() ) {
        if ( empty( $this->base_url ) || empty( $this->api_key ) ) {
            return new WP_Error(
                'fesutibaru_not_configured',
                __( 'Fesutibaru Schedule: API URL and key must be configured in Settings.', 'fesutibaru-schedule' )
            );
        }

        $url = $this->base_url . $endpoint;

        if ( ! empty( $params ) ) {
            $url = add_query_arg( $params, $url );
        }

        $response = wp_remote_get( $url, array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $this->api_key,
                'Accept'        => 'application/json',
            ),
            'timeout' => 15,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = wp_remote_retrieve_body( $response );

        if ( 429 === $code ) {
            return new WP_Error(
                'fesutibaru_rate_limited',
                __( 'Fesutibaru Schedule: API rate limit reached. Data will refresh on the next page load.', 'fesutibaru-schedule' )
            );
        }

        if ( 401 === $code ) {
            return new WP_Error(
                'fesutibaru_unauthorized',
                __( 'Fesutibaru Schedule: Invalid API key. Please check your settings.', 'fesutibaru-schedule' )
            );
        }

        if ( $code < 200 || $code >= 300 ) {
            return new WP_Error(
                'fesutibaru_api_error',
                sprintf(
                    /* translators: %d: HTTP status code */
                    __( 'Fesutibaru Schedule: API returned status %d.', 'fesutibaru-schedule' ),
                    $code
                )
            );
        }

        $data = json_decode( $body, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error(
                'fesutibaru_invalid_json',
                __( 'Fesutibaru Schedule: Invalid JSON response from API.', 'fesutibaru-schedule' )
            );
        }

        return $data;
    }
}
