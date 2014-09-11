<?php

add_shortcode( SEMC_PAGES_SHORTCODE, function( $attributes ) {
	SEMC_Subpages::enqueue_assets();

	$args = shortcode_atts( array(
		'title' => __( 'Subpages', 'semc_subpages' ),
		'parent_id' => false
	), $attributes );

	RIC::render(
		'shortcode',
		SEMC_Subpages::subpages_factory( $args ),
		SEMC_Subpages::get_fragment_name( 'semc_sp_shortcode_', $args )
	);
} );
