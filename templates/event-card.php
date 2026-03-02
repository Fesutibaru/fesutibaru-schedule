<?php
/**
 * Event card template — renders a single event.
 *
 * Override this by copying to your-theme/fesutibaru-schedule/event-card.php
 *
 * Available variables:
 *   $event         — single event array from the API
 *   $show_speakers — bool
 *   $show_venues   — bool
 *   $atts          — shortcode attributes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Extract time range
$start_time = '';
$end_time   = '';

if ( ! empty( $event['start_time'] ) ) {
    $start_time = wp_date( 'H:i', strtotime( $event['start_time'] ) );
}
if ( ! empty( $event['end_time'] ) ) {
    $end_time = wp_date( 'H:i', strtotime( $event['end_time'] ) );
}

$time_display = $start_time;
if ( $start_time && $end_time ) {
    $time_display = $start_time . ' – ' . $end_time;
}

// Extract speaker names
$speakers = array();
if ( $show_speakers && ! empty( $event['authors'] ) ) {
    foreach ( $event['authors'] as $author ) {
        if ( ! empty( $author['name'] ) ) {
            $speakers[] = $author['name'];
        }
    }
}
if ( $show_speakers && ! empty( $event['chairs'] ) ) {
    foreach ( $event['chairs'] as $chair ) {
        if ( ! empty( $chair['name'] ) ) {
            $speakers[] = $chair['name'] . ' (chair)';
        }
    }
}

// Extract venue
$venue_name = '';
if ( $show_venues && ! empty( $event['venue']['name'] ) ) {
    $venue_name = $event['venue']['name'];
}
?>
<div class="fesutibaru-schedule__event">
    <?php if ( $time_display ) : ?>
        <div class="fesutibaru-schedule__time">
            <?php echo esc_html( $time_display ); ?>
        </div>
    <?php endif; ?>

    <div class="fesutibaru-schedule__details">
        <h4 class="fesutibaru-schedule__title">
            <?php echo esc_html( $event['title'] ?? '' ); ?>
        </h4>

        <?php if ( ! empty( $event['description'] ) ) : ?>
            <p class="fesutibaru-schedule__description">
                <?php echo esc_html( $event['description'] ); ?>
            </p>
        <?php endif; ?>

        <?php if ( ! empty( $speakers ) ) : ?>
            <p class="fesutibaru-schedule__speakers">
                <?php echo esc_html( implode( ', ', $speakers ) ); ?>
            </p>
        <?php endif; ?>

        <?php if ( $venue_name ) : ?>
            <p class="fesutibaru-schedule__venue">
                <?php echo esc_html( $venue_name ); ?>
            </p>
        <?php endif; ?>

        <?php if ( ! empty( $event['event_type'] ) ) : ?>
            <span class="fesutibaru-schedule__type">
                <?php echo esc_html( $event['event_type'] ); ?>
            </span>
        <?php endif; ?>
    </div>
</div>
