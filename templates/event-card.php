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
    <?php if ( ! empty( $event['imageUrl'] ) ) : ?>
        <div class="fesutibaru-schedule__image">
            <img decoding="async" src="<?php echo esc_url( $event['imageUrl'] ); ?>" alt="<?php echo esc_attr( $event['title'] ?? '' ); ?>" loading="lazy" />
        </div>
    <?php endif; ?>

    <div class="fesutibaru-schedule__details">
        <div class="fesutibaru-schedule__left">
            <?php if ( $event_date ) : ?>
                <div class="fesutibaru-schedule__event-date">
                    <?php echo esc_html( $event_date ); ?>
                </div>
            <?php endif; ?>

            <div class="fesutibaru-schedule__content">
                <h4 class="fesutibaru-schedule__title">
                    <?php echo esc_html( $event['title'] ?? '' ); ?>
                </h4>

                <?php if ( ! empty( $speakers ) ) : ?>
                    <p class="fesutibaru-schedule__speakers">
                        <?php echo esc_html( implode( ', ', $speakers ) ); ?>
                    </p>
                <?php endif; ?>

                <?php
                // Build accessibility indicators
                $a11y_labels = array();
                if ( ! empty( $event['bslInterpreted'] ) ) {
                    $a11y_labels[] = array( 'key' => 'bsl', 'label' => __( 'BSL Interpreted', 'fesutibaru-schedule' ) );
                }
                if ( ! empty( $event['captioned'] ) ) {
                    $a11y_labels[] = array( 'key' => 'captioned', 'label' => __( 'Captioned', 'fesutibaru-schedule' ) );
                }
                if ( ! empty( $event['audioDescribed'] ) ) {
                    $a11y_labels[] = array( 'key' => 'audio-described', 'label' => __( 'Audio Described', 'fesutibaru-schedule' ) );
                }
                if ( ! empty( $event['relaxedPerformance'] ) ) {
                    $a11y_labels[] = array( 'key' => 'relaxed', 'label' => __( 'Relaxed Performance', 'fesutibaru-schedule' ) );
                }
                $has_live_stream = ! empty( $event['liveStreamUrl'] );
                ?>
                <?php if ( $time_display || $venue_name || ! empty( $a11y_labels ) || $has_live_stream ) : ?>
                    <div class="fesutibaru-schedule__meta">
                        <?php if ( $time_display ) : ?>
                            <span class="fesutibaru-schedule__time">
                                <?php echo esc_html( $time_display ); ?>
                            </span>
                        <?php endif; ?>
                        <?php if ( $venue_name ) : ?>
                            <span class="fesutibaru-schedule__venue">
                                <?php echo esc_html( $venue_name ); ?>
                            </span>
                        <?php endif; ?>
                        <?php foreach ( $a11y_labels as $a11y ) : ?>
                            <span class="fesutibaru-schedule__accessibility-tag fesutibaru-schedule__accessibility-tag--<?php echo esc_attr( $a11y['key'] ); ?>">
                                <?php echo esc_html( $a11y['label'] ); ?>
                            </span>
                        <?php endforeach; ?>
                        <?php if ( $has_live_stream ) : ?>
                            <span class="fesutibaru-schedule__live-stream">
                                <?php echo esc_html__( 'Live Stream', 'fesutibaru-schedule' ); ?>
                            </span>
                        <?php endif; ?>
                    </div>
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

                <?php if ( ! empty( $event['description'] ) ) : ?>
                    <p class="fesutibaru-schedule__description">
                        <?php echo esc_html( $event['description'] ); ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ( ! empty( $event['ticketUrl'] ) ) : ?>
            <a class="fesutibaru-schedule__ticket-link" href="<?php echo esc_url( $event['ticketUrl'] ); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html__( 'Tickets', 'fesutibaru-schedule' ); ?>
            </a>
        <?php endif; ?>
    </div>
</div>
