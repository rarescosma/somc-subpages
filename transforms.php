<?php

/**
 * Adds the 'thumbnail' key to a post array, containing:
 * 'src' => The thumbnail URL
 * 'width' => The thumbnail width
 * 'height' => The thumbnail height
 * 'resized' => Whether the image is a resized version (false for original uploads)
 * 'alt' => Alt text, as defined in the media settings
 */
RIC::register_transform( 'media:thumbnail', function( $post ) {
	$post['thumbnail'] = false;

	if ( !has_post_thumbnail( $post['ID'] ) ) {
		return $post;
	}

	// Let people override the size
	$size = apply_filters( 'somc_pages_thumbnail_size', 'thumbnail' );

	$thumb_id = get_post_thumbnail_id( $post['ID'] );
	if ( false !== $thumb = wp_get_attachment_image_src( $thumb_id, $size ) ) {
		$thumb = array_combine(
			array( 'src', 'width', 'height', 'resized' ),
			$thumb
		);

		// Add alt text
		$thumb['alt'] = get_post_meta( $thumb_id, '_wp_attachment_image_alt', true );

		$post['thumbnail'] = $thumb;
	}

	return $post;
} );


/**
 * Adds the post/page permalink
 */
RIC::register_transform( 'posts:permalink', function( $post ) {
	if ( isset( $post['ID'] ) ) {
		$post['permalink'] = get_permalink( $post['ID'] );
	}

	return $post;
} );


/**
 * Truncate title transform
 */
RIC::register_transform( 'posts:truncate_title', function( $post ) {
	static $num_chars = 20;
	if ( strlen( $post['post_title'] ) <= $num_chars ) {
		return $post;
	}

	// Try not to break in the middle of a word
	$truncated = substr( $post['post_title'], 0 , 20 );
	$last_space = strrpos( $truncated, ' ' );
	if ( false === $last_space ) {
		// Title begins with a word longer than 20 characters, weird
		$post['post_title'] = $truncated;
	} else {
		$post['post_title'] = substr( $truncated, 0, $last_space ) . '&hellip;';
	}

	return $post;
} );


/**
 * Takes a flat array of posts and arranges them in a nested array
 * following their child/parent relationships.
 */
RIC::register_transform( 'structure:flat_to_tree', function( $items ) {
	// First, make sure we have IDs as keys of the items array
	$ids = wp_list_pluck( $items, 'ID' );
	$items = array_combine( $ids, $items );

	$tree = array();
	foreach ( $items as $id => $item ) {
		if ( in_array( $item['post_parent'], $ids ) ) {
			$items[$item['post_parent']]['children'][] = &$items[$id];
		} else {
			$tree[] = &$items[$id];
		}
	}

	return $tree;
} );
