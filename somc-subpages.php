<?php
/*
Plugin Name: SOMC Subpages
Description: Provides and widget and a shortcode for displaying children page hierarchies.
Version: 0.1
Plugin URI: https://github.com/rarescosma/somc-subpages
Author: Rares Cosma
Author URI: https://www.linkedin.com/in/rarescosma
*/

if ( !defined( 'ABSPATH' ) ) {
	die();
}

include 'debug.php';

// Load the RIC performance framework
include dirname( __FILE__ ) . '/src/ric/bootstrap.php';

// Set up constants, configure the framework + fragment caching
include dirname( __FILE__ ) . '/config.php';

// Load sources & transforms
include SOMC_PAGES_DIR . '/sources.php';
include SOMC_PAGES_DIR . '/transforms.php';

/**
 * Provides a method that returns data sources
 * and a method that forms fragment names
 */
class SOMC_Subpages {
	/**
	 * Returns a closure that calls the 'page_tree' source
	 *
	 * @see RIC
	 *
	 * @return callable
	 */
	static function subpages_factory( $args = array() ) {
		/**
		 * Wrap it in a closure for caching
		 *
		 * @see RIC::render()
		 */
		return function() use ( $args ) {
			// If we were given a parent ID, pass it on to the source
			$parent_id = empty( $args['parent_id'] ) ? false : intval( $args['parent_id'] );

			$args['tree'] = RIC::source( 'page_tree', $parent_id );

			// No subpages - assign a succint error message
			if ( empty( $args['tree'] ) ) {
				$args['error'] = __( 'Sorry, we couldn\'t find any subpages', 'somc_subpages' );
			}

			return $args;
		};
	}

	/**
	 * Hashes the args, mixing in the query hash too, if a post parent hasn't
	 * been explicitly set; and returns the fragment name, used for caching
	 *
	 * @param  string $prefix Prefix string.
	 * @param  array  $args   Arguments array
	 *
	 * @return string
	 */
	static function get_fragment_name( $prefix = 'somc_', $args = array() ) {
		$to_hash = json_encode( $args );

		if ( !isset( $args['parent_id'] ) ) {
			// Mix in the query vars hash so we don't serve the same fragment on different pages
			global $wp_the_query;
			$to_hash .= $wp_the_query->query_vars_hash;
		}

		// Use a fast hash
		return $prefix . dechex( crc32( $to_hash ) );
	}
}

// Include the widget & shortcode files
include SOMC_PAGES_DIR . '/widget.php';
add_action( 'widgets_init', function() {
	register_widget( 'SOMC_Subpages_Widget' );
} );

include SOMC_PAGES_DIR . '/shortcode.php';
