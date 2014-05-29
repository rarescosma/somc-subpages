<?php
namespace RIC;

/**
 * The Fragments class can be used to register fragment groups for caching.
 *
 * Groups must be passed in as an array.
 *
 * The keys represent transient names / or transient prefixes.
 * The values represent action hooks that should trigger the purging of said
 * transients.
 *
 * For example:
 *
 * RIC\Fragments::init( array(
 * 	'my_transient' => array( 'clean_post_cache', 'wp_update_menu_items' ),
 * 	'another_tranient_group_' => array( 'ric_entity_change_post_page' )
 * ) );
 *
 * This configuration will purge the 'my_transient' fragment whenever the
 * 'clean_post_cache' or 'wp_update_menu_items' action happen.
 *
 * It will also purge all fragments starting with 'another_transient_group_'
 * whenever a page changes state.
 *
 * @see RIC\Events for more details on state changes.
 *
 * @since  0.8.5
 */

class Fragments {
	/**
	 * Default expiration time for fragments, in seconds.
	 *
	 * @var int
	 * @since  0.8.5
	 */
	const EXPIRATION = 3600;

	/**
	 * Holder for the passed in fragment groups
	 *
	 * @var array
	 * @since  0.8.5
	 */
	static $groups;

	/**
	 * Passes the fragment groups to W3TC, if active
	 * or handles transient purging grom wp_options manually, if not
	 *
	 * @access public
	 * @since  0.8.5
	 *
	 * @return void
	 */
	static function init( $groups = array() ) {
		self::$groups = $groups;

		if ( function_exists( 'w3tc_register_fragment_group' ) ) {
			// W3TC managed
			foreach( self::$groups as $group => $actions ) {
				w3tc_register_fragment_group( $group, $actions, self::EXPIRATION );
			}
		} else {
			self::hook( self::get_actions( self::$groups ) );
		}
	}

	/**
	 * This is where the actual hooking takes place. When one of the specified
	 * actions happen, we take all affected fragments and purge them through
	 * a single, efficient SQL query.
	 *
	 * @param  array  $actions Pairs of hook names => groups prefixes
	 *
	 * @access public
	 * @since  0.8.5
	 *
	 * @return void
	 */
	private static function hook( $actions = array() ) {
		if ( empty( $actions ) ) {
			return;
		}

		foreach( $actions as $action => $prefixes ) {
			add_action( $action, function() use ( $prefixes ) {
				global $wpdb;

				$clauses = array_map( function( $x ) use ( $wpdb ) {
					return $wpdb->prepare( 'option_name LIKE %s', "_transient_${x}%" );
				}, $prefixes );

				$wpdb->query( "DELETE from $wpdb->options WHERE (" . implode( ' OR ', $clauses ) . ')' );
			}, 999999 );
		}
	}

	/**
	 * "Flip" the fragment groups array so unique actions become keys and
	 * all affected fragments (or fragment groups) become arrays.
	 *
	 * @param  array  $groups Fragment groups
	 *
	 * @access public
	 * @since  0.8.5
	 *
	 * @return array
	 */
	private static function get_actions( $groups = array() ) {
		if ( empty( $groups ) ) {
			return array();
		}

		$actions = call_user_func_array( 'array_merge', array_values( $groups ) );

		$ret = array();
		foreach ( $actions as $action ) {
			$ret[$action] = array_filter( array_keys( $groups ), function( $key ) use ( $groups, $action ) {
				return in_array( $action, $groups[$key] );
			} );
		}

		return $ret;
	}
}
