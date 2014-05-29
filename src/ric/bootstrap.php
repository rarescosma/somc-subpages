<?php
/**
 * This file acts as a simple bootstrap for the framework classes
 *
 * @since  0.8.5
 */
$prefix = trailingslashit( dirname( __FILE__ ) );
array_map(
	function( $file ) use ( $prefix ) {
		require $prefix . $file . '.php';
	},
	array(
		'ric-hook',
		'ric',
		'ric-events',
		'ric-fragments',
		'ric-simple-cache'
	)
);
unset( $prefix );