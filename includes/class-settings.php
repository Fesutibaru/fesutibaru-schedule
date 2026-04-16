<?php
/**
 * Admin settings page for Fesutibaru Schedule.
 *
 * WP Admin > Settings > Fesutibaru Schedule
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Fesutibaru_Schedule_Settings {

    const OPTION_GROUP = 'fesutibaru_schedule_settings';
    const OPTION_NAME  = 'fesutibaru_schedule_options';

    /**
     * Register hooks.
     */
    public function init() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_init', array( $this, 'handle_clear_cache' ) );
    }

    /**
     * Add the settings page under Settings menu.
     */
    public function add_settings_page() {
        add_options_page(
            __( 'Fesutibaru Schedule', 'fesutibaru-schedule' ),
            __( 'Fesutibaru Schedule', 'fesutibaru-schedule' ),
            'manage_options',
            'fesutibaru-schedule',
            array( $this, 'render_settings_page' )
        );
    }

    /**
     * Register settings fields.
     */
    public function register_settings() {
        register_setting(
            self::OPTION_GROUP,
            self::OPTION_NAME,
            array( $this, 'sanitize_options' )
        );

        add_settings_section(
            'fesutibaru_api_section',
            __( 'API Connection', 'fesutibaru-schedule' ),
            function () {
                echo '<p>' . esc_html__( 'Enter your Fesutibaru API credentials. You can find these in the Planner dashboard under Settings > API Keys.', 'fesutibaru-schedule' ) . '</p>';
            },
            'fesutibaru-schedule'
        );

        add_settings_field(
            'api_base_url',
            __( 'API Base URL', 'fesutibaru-schedule' ),
            array( $this, 'render_text_field' ),
            'fesutibaru-schedule',
            'fesutibaru_api_section',
            array(
                'field'       => 'api_base_url',
                'placeholder' => 'https://yourfestival.fesutibaru.com',
                'description' => __( 'The URL of your Fesutibaru Planner instance.', 'fesutibaru-schedule' ),
            )
        );

        add_settings_field(
            'api_key',
            __( 'API Key', 'fesutibaru-schedule' ),
            array( $this, 'render_password_field' ),
            'fesutibaru-schedule',
            'fesutibaru_api_section',
            array(
                'field'       => 'api_key',
                'description' => __( 'Your API key (Bearer token). This is stored securely and never exposed in page source.', 'fesutibaru-schedule' ),
            )
        );

        add_settings_section(
            'fesutibaru_display_section',
            __( 'Display Settings', 'fesutibaru-schedule' ),
            null,
            'fesutibaru-schedule'
        );

        add_settings_field(
            'cache_duration',
            __( 'Cache Duration (minutes)', 'fesutibaru-schedule' ),
            array( $this, 'render_number_field' ),
            'fesutibaru-schedule',
            'fesutibaru_display_section',
            array(
                'field'       => 'cache_duration',
                'default'     => 5,
                'min'         => 1,
                'max'         => 1440,
                'description' => __( 'How long to cache API responses. Default: 5 minutes.', 'fesutibaru-schedule' ),
            )
        );

        add_settings_field(
            'tracking_parameter',
            __( 'Link Tracking Parameter', 'fesutibaru-schedule' ),
            array( $this, 'render_text_field' ),
            'fesutibaru-schedule',
            'fesutibaru_display_section',
            array(
                'field'       => 'tracking_parameter',
                'placeholder' => 'utm_source=main_website',
                'description' => __( 'Query string to append to all outgoing links (e.g. utm_source=main_website&utm_medium=schedule).', 'fesutibaru-schedule' ),
            )
        );

        add_settings_field(
            'default_view',
            __( 'Default View', 'fesutibaru-schedule' ),
            array( $this, 'render_select_field' ),
            'fesutibaru-schedule',
            'fesutibaru_display_section',
            array(
                'field'   => 'default_view',
                'options' => array(
                    'list' => __( 'List', 'fesutibaru-schedule' ),
                    'grid' => __( 'Grid', 'fesutibaru-schedule' ),
                ),
                'description' => __( 'Default layout when no view is specified in the shortcode.', 'fesutibaru-schedule' ),
            )
        );
    }

    /**
     * Sanitize options before saving.
     *
     * @param array $input  Raw input.
     * @return array  Sanitized options.
     */
    public function sanitize_options( $input ) {
        $sanitized = array();

        $sanitized['api_base_url']    = esc_url_raw( rtrim( $input['api_base_url'] ?? '', '/' ) );
        $sanitized['api_key']         = sanitize_text_field( $input['api_key'] ?? '' );
        $sanitized['cache_duration']  = max( 1, min( 1440, (int) ( $input['cache_duration'] ?? 5 ) ) );
        $sanitized['tracking_parameter'] = sanitize_text_field( $input['tracking_parameter'] ?? '' );
        $sanitized['default_view']    = in_array( $input['default_view'] ?? '', array( 'list', 'grid' ), true )
            ? $input['default_view']
            : 'list';

        return $sanitized;
    }

    /**
     * Handle the "Clear Cache" button.
     */
    public function handle_clear_cache() {
        if (
            ! isset( $_POST['fesutibaru_clear_cache'] ) ||
            ! check_admin_referer( 'fesutibaru_clear_cache_action' )
        ) {
            return;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        Fesutibaru_Schedule_Cache::clear_all();

        add_settings_error(
            self::OPTION_GROUP,
            'cache_cleared',
            __( 'Cache cleared successfully.', 'fesutibaru-schedule' ),
            'updated'
        );
    }

    /**
     * Render the settings page.
     */
    public function render_settings_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields( self::OPTION_GROUP );
                do_settings_sections( 'fesutibaru-schedule' );
                submit_button();
                ?>
            </form>

            <hr>

            <h2><?php esc_html_e( 'Cache', 'fesutibaru-schedule' ); ?></h2>
            <p><?php esc_html_e( 'Clear all cached API responses. The next page load will fetch fresh data.', 'fesutibaru-schedule' ); ?></p>
            <form method="post">
                <?php wp_nonce_field( 'fesutibaru_clear_cache_action' ); ?>
                <input type="hidden" name="fesutibaru_clear_cache" value="1">
                <?php submit_button( __( 'Clear Cache', 'fesutibaru-schedule' ), 'secondary' ); ?>
            </form>

            <hr>

            <h2><?php esc_html_e( 'Usage', 'fesutibaru-schedule' ); ?></h2>
            <p><?php esc_html_e( 'Add this shortcode to any page or post:', 'fesutibaru-schedule' ); ?></p>
            <code>[fesutibaru_schedule]</code>

            <p style="margin-top: 1em;">
                <?php esc_html_e( 'With parameters:', 'fesutibaru-schedule' ); ?>
            </p>
            <code>[fesutibaru_schedule days="3" view="grid" venue="Main Theatre" show_speakers="yes"]</code>

            <p style="margin-top: 1em;">
                <a href="https://github.com/Fesutibaru/fesutibaru-schedule#shortcode-usage" target="_blank" rel="noopener">
                    <?php esc_html_e( 'View full documentation', 'fesutibaru-schedule' ); ?> &rarr;
                </a>
            </p>
        </div>
        <?php
    }

    // -------------------------------------------------------------------------
    // Field renderers
    // -------------------------------------------------------------------------

    public function render_text_field( $args ) {
        $options = get_option( self::OPTION_NAME, array() );
        $value   = $options[ $args['field'] ] ?? '';
        printf(
            '<input type="text" name="%s[%s]" value="%s" class="regular-text" placeholder="%s">',
            esc_attr( self::OPTION_NAME ),
            esc_attr( $args['field'] ),
            esc_attr( $value ),
            esc_attr( $args['placeholder'] ?? '' )
        );
        if ( ! empty( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
        }
    }

    public function render_password_field( $args ) {
        $options = get_option( self::OPTION_NAME, array() );
        $value   = $options[ $args['field'] ] ?? '';
        printf(
            '<input type="password" name="%s[%s]" value="%s" class="regular-text" autocomplete="off">',
            esc_attr( self::OPTION_NAME ),
            esc_attr( $args['field'] ),
            esc_attr( $value )
        );
        if ( ! empty( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
        }
    }

    public function render_number_field( $args ) {
        $options = get_option( self::OPTION_NAME, array() );
        $value   = $options[ $args['field'] ] ?? ( $args['default'] ?? '' );
        printf(
            '<input type="number" name="%s[%s]" value="%s" class="small-text" min="%s" max="%s">',
            esc_attr( self::OPTION_NAME ),
            esc_attr( $args['field'] ),
            esc_attr( $value ),
            esc_attr( $args['min'] ?? 0 ),
            esc_attr( $args['max'] ?? '' )
        );
        if ( ! empty( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
        }
    }

    public function render_select_field( $args ) {
        $options       = get_option( self::OPTION_NAME, array() );
        $current_value = $options[ $args['field'] ] ?? '';
        printf(
            '<select name="%s[%s]">',
            esc_attr( self::OPTION_NAME ),
            esc_attr( $args['field'] )
        );
        foreach ( $args['options'] as $value => $label ) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr( $value ),
                selected( $current_value, $value, false ),
                esc_html( $label )
            );
        }
        echo '</select>';
        if ( ! empty( $args['description'] ) ) {
            printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
        }
    }

    // -------------------------------------------------------------------------
    // Helper: get a single option value
    // -------------------------------------------------------------------------

    /**
     * Get a plugin option.
     *
     * @param string $key      Option key.
     * @param mixed  $default  Default value.
     * @return mixed
     */
    public static function get( $key, $default = '' ) {
        $options = get_option( self::OPTION_NAME, array() );
        return $options[ $key ] ?? $default;
    }
}
