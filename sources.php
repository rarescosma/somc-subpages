<?php

/**
 * Source for the children of any hierarchical post specified by
 * a numeric ID argument.
 *
 * If no argument is passed, the queried object is used instead.
 */
RIC::register_source( 'page_children', function( $parent_id = false ) {
	if ( !empty ( $parent_id ) ) {
		$parent = get_post( $parent_id );
	} else {
		// Use the original query, just in case someone messed up $wp_query
		global $wp_the_query;
		$parent = $wp_the_query->get_queried_object();
	}

	if ( empty( $parent ) || !isset( $parent->post_type ) || !is_post_type_hierarchical( $parent->post_type ) ) {
		return new WP_Error( 'I require a hierarchical post type.' );
	}

	$pages = get_pages( array(
		'hierarchical' => 1,
		'child_of' => $parent->ID,
		'post_type' => $parent->post_type,
		'post_status' => 'publish'
	) );

	/**
	 * Return associative arrays instead of post objects:
	 * we need this step to turn the flat list into a tree later
	 */
	return array_map( function( $p ) { return (array)$p; }, $pages );
} );


/**
 * Custom source used by the widget and shortcode
 * Obtains a flat list of subpages from 'page_children', then it
 * adds thumbnails and permalinks, and finally it passes the list
 * through the 'flat_to_tree' transform to make it a tree.
 */
RIC::register_source( 'page_tree', function( $parent_id = false ) {
	$flat = RIC::transform(
		RIC::source( 'page_children', $parent_id ),
		'media:thumbnail',
		'posts:permalink'
	);

	return RIC::transform( $flat, 'structure:flat_to_tree', true );
} );