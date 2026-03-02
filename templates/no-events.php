<?php
/**
 * Empty state template — shown when no events are returned.
 *
 * Override this by copying to your-theme/fesutibaru-schedule/no-events.php
 *
 * Available variables:
 *   $atts — shortcode attributes
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="fesutibaru-schedule fesutibaru-schedule--empty">
    <p class="fesutibaru-schedule__no-events">
        <?php esc_html_e( 'No events scheduled at this time. Check back soon!', 'fesutibaru-schedule' ); ?>
    </p>
</div>
