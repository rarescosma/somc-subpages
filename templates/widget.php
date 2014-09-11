<?php

/**
 * Since we have not implemented conditional logic for this widget,
 * it is not cool to trigger a warning if we got a WP_Error, which
 * means we're not on a 'page'.
 *
 * We'll just silently bail out.
 */
if ( is_wp_error( $tree ) ) { return; }

echo $before_widget;
echo $before_title . $widget_title . $after_title;
?>
<div class="semc-wrap">
<?php if ( isset( $error ) ): ?>
<div class="error">
	<p><?php echo $error; ?></p>
</div>
<?php endif; ?>

<?php if ( !empty( $tree ) ) { include 'root.php'; } ?>

<?php echo $after_widget; ?>
</div>