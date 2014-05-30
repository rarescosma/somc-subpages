<nav class="auxiliary">
	<p>Sort by title: <a href="#" data-trigger="order" data-order="asc">asc</a> <a href="#" data-trigger="order" data-order="desc">desc</a></p>
</nav>
<ul>
<?php foreach ( $tree as $page ): ?>
	<li>
		<a class="block" href="<?php echo esc_attr( $page['permalink'] ); ?>" title="<?php echo esc_attr( $page['post_title'] ); ?>">
			<?php if ( !empty( $page['thumbnail'] ) ): ?>
			<div class="media__img">
				<img src="<?php echo esc_attr( $page['thumbnail']['src'] ); ?>" width="<?php echo esc_attr( $page['thumbnail']['width'] ); ?>" height="<?php echo esc_attr( $page['thumbnail']['height'] ); ?>" alt="<?php echo esc_attr( $page['thumbnail']['alt'] ); ?>" />
			</div>
			<?php endif; ?>
			<div class="media__body"><?php echo esc_attr( $page['post_title'] ); ?></div>
		</a>
		<?php if ( !empty( $page['children'] ) ): ?>
		<p><a href="#" data-trigger="expand" data-action="expand" data-switch-action="collapse">Expand</a></p>
		<?php RIC::render( 'root', array( 'tree' => $page['children'] ) ); ?>
		<?php endif; ?>
	</li>
<?php endforeach; ?>
</ul>
