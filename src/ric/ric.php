<?php

/**
 * Provides tools for developing using the source/transform/render flow:
 * * register_(source|transform)
 * * source
 * * transform
 * * render
 */
class RIC {
	protected static $sources = array();

	protected static $transforms = array();

	protected static $cache;

	protected static $views_dir;

	/**
	 * Initialize
	 *
	 * @param  string $views_dir  Where to search for views/templates
	 *
	 * @return void
	 */
	static function init( $views_dir = '' ) {
		self::$views_dir = empty( $views_dir ) ? trailingslashit( dirname( __FILE__ ) ) . 'views' : $views_dir;

		// TODO - dependency injection
		self::$cache = new RIC\Simple_Cache();
	}

	/**
	 * Source registry
	 *
	 * @param  string   $key      Source handle
	 * @param  callable $callback Callback
	 *
	 * @return void
	 */
	static function register_source( $key = '', $callback ) {
		if ( !empty( $key ) && is_callable( $callback ) ) {
			self::$sources[$key] = $callback;
		}
	}

	/**
	 * Wrapper for source callbacks. Performs memoization.
	 * TODO: error handling
	 *
	 * @param  string $name Source handle ('posts')
	 * @param  mixed  $args Source arguments
	 *
	 * @return array|WP_Error
	 */
	static function source( $name = '', $args ) {
		static $memo;

		if ( empty( $name ) || !array_key_exists( $name, self::$sources ) ) {
			return array();
		}

		$key = crc32( $name . '#' . json_encode( $args ) );
		if ( !isset( $memo[$key] ) ) {
			$memo[$key] = $items = call_user_func( self::$sources[$name], $args );
		} else {
			$items = $memo[$key];
		}

		return $items;
	}

	/**
	 * Transform registry
	 *
	 * @param  string   $key      Transform handle
	 * @param  callable $callback Callback
	 *
	 * @return void
	 */
	static function register_transform( $key = '', $callback ) {
		if ( !empty( $key ) && is_callable( $callback ) ) {
			self::$transforms[$key] = $callback;
		}
	}

	/**
	 * Wrapper for transform callbacks. Accepts an arbitrary number of
	 * transform handles.
	 *
	 * Transforms will be applied in the supplied order.
	 *
	 * @param  array  $items The items to trasform
	 * ...
	 *
	 * @return array|WP_Error
	 */
	static function transform( $items = array() ) {
		// Bubble up errors
		if ( is_wp_error( $items ) ) {
			return $items;
		}

		$fargs = func_get_args();
		array_shift( $fargs ); // First argument is grabbed explicitly

		// If the last argument is TRUE, apply the transform to the whole
		// set at once, instead of one element at a time
		if ( is_bool( end( $fargs ) ) ) {
			$single = array_pop( $fargs );
		} else {
			$single = false;
		}
		reset( $fargs );

		$registered = self::$transforms;

		$valid_transforms = array_filter(
			$fargs,
			function( $k ) use ( $registered ) {
				return array_key_exists( $k, $registered );
			}
		);

		foreach ( $valid_transforms as $cb ) {
			if ( $single ) {
				$items = $registered[$cb]( $items );
			} else {
				$items = array_map( $registered[$cb], $items );
			}
		}

		return $items;
	}

	/**
	 * Renders a template
	 *
	 * @param  string          $view      The desired view, relative to IC_VIEWS_DIR
	 * @param  array|callable  $_context  The context. Use this parameter to pass data
	 * into the view. You can also pass a callable to defer code execution, in case
	 * of a cache hit.
	 * @param  string          $cache_key Optional key for fragment caching.
	 * If the cache engine finds a valid fragment and $context is a callable, it
	 * won't be called.
	 *
	 * @return void
	 */
	static function render( $view = '', $_context, $cache_key = false ) {
		if (
			!empty( $cache_key )
			&& is_string( $cache_key )
			&& false !== $cached = self::$cache[$cache_key]
		) {
			echo $cached;
			return;
		}

		// Trigger context, if we were passed a callable
		if ( is_callable( $_context ) ) {
			$_context = call_user_func( $_context );
		}

		if ( !empty( $cache_key ) ) {
			ob_start();
		}

		$view_file = self::$views_dir . '/' . $view . '.php';
		if ( file_exists( $view_file ) && false !== $_context ) {
			// Prevent spoiling the global scope
			// The whole, unextracted context is available in the view
			// through the $_context variable
			$closure = function( $_view_file ) use ( $_context ) {
				extract( $_context, EXTR_SKIP );
				include( $_view_file );
			};

			$closure( $view_file );
		}

		if ( !empty( $cache_key ) ) {
			self::$cache[$cache_key] = $markup = ob_get_clean();
			echo $markup;
			return;
		}
	}
}
