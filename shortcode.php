<?php

add_shortcode( SOMC_PAGES_SHORTCODE, function( $attributes ) {
	$args = shortcode_atts( array(
		'title' => __( 'Subpages', 'somc_subpages' ),
		'parent_id' => false
	), $attributes );

	RIC::render(
		'shortcode',
		SOMC_Subpages::subpages_factory( $args ),
		SOMC_Subpages::get_fragment_name( 'somc_sp_shortcode_', $args )
	);
} );
