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

// Extract event date
$event_date = '';
if ( ! empty( $event['startTime'] ) ) {
    $event_date = wp_date( 'j M', strtotime( $event['startTime'] ) );
}

// Extract time range
$start_time = '';
$end_time   = '';

if ( ! empty( $event['startTime'] ) ) {
    $start_time = wp_date( 'H:i', strtotime( $event['startTime'] ) );
}
if ( ! empty( $event['endTime'] ) ) {
    $end_time = wp_date( 'H:i', strtotime( $event['endTime'] ) );
}

$time_display = $start_time;
if ( $start_time && $end_time ) {
    $time_display = $start_time . ' – ' . $end_time;
}

// Extract speaker names from participants array
$speakers = array();
if ( $show_speakers && ! empty( $event['participants'] ) ) {
    foreach ( $event['participants'] as $participant ) {
        $name = trim( ( $participant['firstName'] ?? '' ) . ' ' . ( $participant['lastName'] ?? '' ) );
        if ( empty( $name ) ) {
            continue;
        }
        if ( ( $participant['role'] ?? '' ) === 'chair' ) {
            $name .= ' (chair)';
        }
        $speakers[] = $name;
    }
}

// Extract venue
$venue_name = '';
if ( $show_venues && ! empty( $event['venue']['name'] ) ) {
    $venue_name = $event['venue']['name'];
}

// Build ticket price display
$prices = array();
if ( ! empty( $event['ticketPriceFull'] ) ) {
    $prices[] = '£' . $event['ticketPriceFull'];
}
if ( ! empty( $event['ticketPriceConc'] ) ) {
    $prices[] = '£' . $event['ticketPriceConc'];
}
if ( ! empty( $event['ticketPriceKids'] ) ) {
    $prices[] = '£' . $event['ticketPriceKids'];
}
$price_display = implode( ' | ', $prices );
?>
<div class="fesutibaru-schedule__event">
    <div class="fesutibaru-schedule__details">
        <?php if ( $event_date ) : ?>
            <div class="fesutibaru-schedule__event-date">
                <?php echo esc_html( $event_date ); ?>
            </div>
        <?php endif; ?>

        <h4 class="fesutibaru-schedule__title">
            <?php echo esc_html( $event['title'] ?? '' ); ?>
        </h4>

        <?php if ( ! empty( $speakers ) ) : ?>
            <p class="fesutibaru-schedule__speakers">
                <?php echo esc_html( implode( ', ', $speakers ) ); ?>
            </p>
        <?php endif; ?>

        <?php if ( $time_display ) : ?>
            <div class="fesutibaru-schedule__time">
                <?php echo esc_html( $time_display ); ?>
            </div>
        <?php endif; ?>

        <?php if ( $venue_name ) : ?>
            <p class="fesutibaru-schedule__venue">
                <?php echo esc_html( $venue_name ); ?>
            </p>
        <?php endif; ?>

        <?php if ( $price_display ) : ?>
            <p class="fesutibaru-schedule__price">
                <?php echo esc_html( $price_display ); ?>
            </p>
        <?php endif; ?>

        <?php if ( ! empty( $event['eventType'] ) ) : ?>
            <span class="fesutibaru-schedule__type">
                <?php echo esc_html( $event['eventType'] ); ?>
            </span>
        <?php endif; ?>

        <?php if ( ! empty( $event['ticketUrl'] ) ) : ?>
            <a class="fesutibaru-schedule__ticket-link" href="<?php echo esc_url( $event['ticketUrl'] ); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html__( 'Tickets', 'fesutibaru-schedule' ); ?>
            </a>
        <?php endif; ?>

        <?php if ( ! empty( $event['description'] ) ) : ?>
            <p class="fesutibaru-schedule__description">
                <?php echo esc_html( $event['description'] ); ?>
            </p>
        <?php endif; ?>
    </div>
</div>
