<ul>
<?php foreach ( $tree as $page ): ?>
	<li>
		<a href="<?php echo esc_attr( $page['permalink'] ); ?>" title="<?php echo esc_attr( $page['post_title'] ); ?>">
			<?php if ( !empty( $page['thumbnail'] ) ): ?>
			<img src="<?php echo esc_attr( $page['thumbnail']['src'] ); ?>" width="<?php echo esc_attr( $page['thumbnail']['width'] ); ?>" height="<?php echo esc_attr( $page['thumbnail']['height'] ); ?>" alt="<?php echo esc_attr( $page['thumbnail']['alt'] ); ?>" />
			<?php endif; ?>
			<?php echo esc_attr( $page['post_title'] ); ?>
		</a>
		<?php if ( !empty( $page['children'] ) ) {
			RIC::render( 'root', array( 'tree' => $page['children'] ) );
		} ?>
	</li>
<?php endforeach; ?>
</ul>
