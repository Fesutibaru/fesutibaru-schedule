<?php
/**
 * Shortcode renderer for [fesutibaru_schedule].
 *
 * Fetches events from the API (with caching), groups them by day,
 * and renders them using overridable templates.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Fesutibaru_Schedule_Shortcode_Renderer {

    /**
     * Render the shortcode.
     *
     * @param array|string $atts  Shortcode attributes.
     * @return string  HTML output.
     */
    public function render( $atts = array() ) {
        $atts = shortcode_atts( array(
            'view'          => Fesutibaru_Schedule_Settings::get( 'default_view', 'list' ),
            'days'          => '',
            'venue'         => '',
            'search'        => '',
            'limit'         => 500,
            'show_speakers' => 'yes',
            'show_venues'   => 'yes',
            'date'          => '',
            'class'         => '',
        ), $atts, 'fesutibaru_schedule' );

        // Check configuration
        $base_url = Fesutibaru_Schedule_Settings::get( 'api_base_url' );
        $api_key  = Fesutibaru_Schedule_Settings::get( 'api_key' );

        if ( empty( $base_url ) || empty( $api_key ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                return '<p class="fesutibaru-schedule__notice">'
                    . esc_html__( 'Fesutibaru Schedule: Please configure your API settings.', 'fesutibaru-schedule' )
                    . ' <a href="' . esc_url( admin_url( 'options-general.php?page=fesutibaru-schedule' ) ) . '">'
                    . esc_html__( 'Settings', 'fesutibaru-schedule' ) . '</a></p>';
            }
            return '';
        }

        // Build API query parameters
        $query_params = array();

        if ( ! empty( $atts['search'] ) ) {
            $query_params['search'] = $atts['search'];
        }

        if ( ! empty( $atts['venue'] ) ) {
            $query_params['venue'] = $atts['venue'];
        }

        if ( ! empty( $atts['date'] ) ) {
            $query_params['date'] = $atts['date'];
        }

        $max_events = (int) $atts['limit'];

        // Check cache first
        $cache_duration = (int) Fesutibaru_Schedule_Settings::get( 'cache_duration', 5 );
        $cache          = new Fesutibaru_Schedule_Cache( $cache_duration );
        $cache_key      = $cache->make_key( array_merge( $query_params, array( 'limit' => $max_events ) ) );

        $events = $cache->get( $cache_key );

        if ( false === $events ) {
            // Cache miss — fetch all pages from API (max 200 per page)
            $client = new Fesutibaru_Schedule_API_Client( $base_url, $api_key );
            $events = array();
            $page   = 1;

            while ( count( $events ) < $max_events ) {
                $per_page     = min( 200, $max_events - count( $events ) );
                $page_params  = array_merge( $query_params, array(
                    'per_page' => $per_page,
                    'page'     => $page,
                ) );

                $result = $client->get_events( $page_params );

                if ( is_wp_error( $result ) ) {
                    // Try stale cache if we got nothing yet
                    if ( empty( $events ) ) {
                        $events = $cache->get_stale( $cache_key );

                        if ( false === $events ) {
                            if ( current_user_can( 'manage_options' ) ) {
                                return '<p class="fesutibaru-schedule__notice">'
                                    . esc_html( $result->get_error_message() ) . '</p>';
                            }
                            return '';
                        }
                    }
                    break;
                }

                $page_data = isset( $result['data'] ) ? $result['data'] : $result;
                $events    = array_merge( $events, $page_data );

                // Stop if we got fewer than requested (last page)
                if ( count( $page_data ) < $per_page ) {
                    break;
                }

                $page++;
            }

            if ( ! empty( $events ) ) {
                $cache->set( $cache_key, $events );
            }
        }

        if ( empty( $events ) ) {
            return $this->load_template( 'no-events', array( 'atts' => $atts ) );
        }

        // Filter by specific date if specified
        if ( ! empty( $atts['date'] ) ) {
            $filter_date = $atts['date'];
            $events      = array_filter( $events, function ( $event ) use ( $filter_date ) {
                $event_date = substr( $event['startTime'] ?? '', 0, 10 );
                return $event_date === $filter_date;
            } );
        }

        // Filter by number of days if specified
        if ( ! empty( $atts['days'] ) ) {
            $max_date = wp_date( 'Y-m-d', strtotime( '+' . (int) $atts['days'] . ' days' ) );
            $events   = array_filter( $events, function ( $event ) use ( $max_date ) {
                $event_date = substr( $event['startTime'] ?? '', 0, 10 );
                return $event_date <= $max_date;
            } );
        }

        // Group events by day
        $grouped = array();
        foreach ( $events as $event ) {
            $date_key = substr( $event['startTime'] ?? 'unknown', 0, 10 );
            $grouped[ $date_key ][] = $event;
        }

        // Sort days chronologically
        ksort( $grouped );

        return $this->load_template( 'schedule', array(
            'grouped'       => $grouped,
            'atts'          => $atts,
            'show_speakers' => $atts['show_speakers'] === 'yes',
            'show_venues'   => $atts['show_venues'] === 'yes',
        ) );
    }

    /**
     * Load a template file.
     *
     * Checks the active theme first for overrides, then falls back
     * to the plugin's bundled templates.
     *
     * @param string $template  Template name (without .php).
     * @param array  $vars      Variables to extract into the template scope.
     * @return string  Rendered HTML.
     */
    private function load_template( $template, $vars = array() ) {
        // Allow theme overrides
        $theme_path = get_stylesheet_directory() . '/fesutibaru-schedule/' . $template . '.php';
        $plugin_path = FESUTIBARU_SCHEDULE_PLUGIN_DIR . 'templates/' . $template . '.php';

        $file = file_exists( $theme_path ) ? $theme_path : $plugin_path;

        if ( ! file_exists( $file ) ) {
            return '';
        }

        extract( $vars, EXTR_SKIP );
        ob_start();
        include $file;
        return ob_get_clean();
    }
}
