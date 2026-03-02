<?php
/**
 * Schedule template — renders grouped events by day.
 *
 * Override this by copying to your-theme/fesutibaru-schedule/schedule.php
 *
 * Available variables:
 *   $grouped       — array of events grouped by date key (YYYY-MM-DD => events[])
 *   $atts          — shortcode attributes
 *   $show_speakers — bool
 *   $show_venues   — bool
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$view_class  = 'fesutibaru-schedule--' . esc_attr( $atts['view'] );
$extra_class = ! empty( $atts['class'] ) ? ' ' . esc_attr( $atts['class'] ) : '';
?>
<div class="fesutibaru-schedule <?php echo $view_class . $extra_class; ?>">
    <?php foreach ( $grouped as $date_key => $day_events ) : ?>
        <div class="fesutibaru-schedule__day" data-date="<?php echo esc_attr( $date_key ); ?>">
            <h3 class="fesutibaru-schedule__date">
                <?php
                if ( $date_key !== 'unknown' ) {
                    // Format: "Saturday 14 June 2025"
                    $timestamp = strtotime( $date_key );
                    echo esc_html( wp_date( 'l j F Y', $timestamp ) );
                } else {
                    echo esc_html__( 'Date TBC', 'fesutibaru-schedule' );
                }
                ?>
            </h3>

            <div class="fesutibaru-schedule__events">
                <?php foreach ( $day_events as $event ) :
                    // Load the event card template for each event
                    $theme_path  = get_stylesheet_directory() . '/fesutibaru-schedule/event-card.php';
                    $plugin_path = FESUTIBARU_SCHEDULE_PLUGIN_DIR . 'templates/event-card.php';
                    $card_file   = file_exists( $theme_path ) ? $theme_path : $plugin_path;

                    if ( file_exists( $card_file ) ) {
                        include $card_file;
                    }
                endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
