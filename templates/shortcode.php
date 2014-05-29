<section class="somc-subpages">
	<?php if ( !empty( $title ) ): ?>
	<h2><?php echo $title; ?></h2>
	<?php endif; ?>

	<?php if ( isset( $error ) ): ?>
	<div class="error">
		<p><?php echo $error; ?></p>
	</div>
	<?php endif; ?>

	<?php if ( !empty( $tree ) ) { include 'root.php'; } ?>
</section>