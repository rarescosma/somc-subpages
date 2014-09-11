<?php

class SEMC_Subpages_Widget extends WP_Widget {
	/**
	 * Constructor.
	 *
	 * @since 0.1
	 *
	 * @return SEMC_Subpages_Widget
	 */
	public function __construct() {
		parent::__construct( 'widget_semc_subpages', __( 'SEMC Subpages', 'semc_subpages' ), array(
			'classname'   => 'widget_semc_subpages',
			'description' => __( 'Use this widget to display a sortable tree of subpages of the current page.', 'semc_subpages' )
		) );
	}

	/**
	 * Output the widget markup.
	 *
	 * @access public
	 * @since 0.1
	 *
	 * @param array $args     An array of standard parameters for widgets in this theme.
	 * @param array $instance An array of settings for this widget instance.
	 */
	public function widget( $args, $instance ) {
		// Enqueue the assets
		SEMC_Subpages::enqueue_assets();

		// Chip the title in
		$args['widget_title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Subpages', 'semc_subpages' ) : $instance['title'], $instance, $this->id_base );

		// Form the fragment name
		$fragment = SEMC_Subpages::get_fragment_name( 'semc_sp_widget_', $args );

		// Render with a callable as the second argument and the fragment name
		// as the third => fragment caching!
		RIC::render( 'widget', SEMC_Subpages::subpages_factory( $args ), $fragment );
	}

	private function get_fragment_name( $args ) {
		global $wp_the_query;

		// Fast hash the widget arguments
		$widget_hash = dechex( crc32( json_encode( $args ) . $wp_the_query->query_vars_hash ) );

		// Mix in the query vars hash so we don't serve the same fragment on different pages
		return 'semc_widget_markup_' . $widget_hash;
	}

	/**
	 * Validate and save the widget settings.
	 *
	 * @since 0.1
	 *
	 * @param array $new_instance New widget instance.
	 * @param array $instance     Original widget instance.
	 * @return array Updated widget instance.
	 */
	function update( $new_instance, $instance ) {
		$instance['title']  = strip_tags( $new_instance['title'] );
		return $instance;
	}

	/**
	 * Display a *very* basic form for this widget on the Widgets page.
	 *
	 * @since 0.1
	 *
	 * @param array $instance
	 */
	function form( $instance ) {
		$title  = empty( $instance['title'] ) ? '' : esc_attr( $instance['title'] );
		?>
			<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title:', 'twentyfourteen' ); ?></label>
			<input id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" class="widefat" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>"></p>
		<?php
	}
}
