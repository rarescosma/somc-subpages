<?php
namespace RIC;

/**
 * Utility class: extend it for easy hooking using the hook() method.
 *
 * @since  0.7.0
 */

class Hook {
	/**
	 * Dynamic wrapper for add_filter. If only the hook name is set
	 * it will hook the class method with the same name.
	 *
	 * If the second argument is a string, it will override the
	 * method name. If it's an integer, it will be used as the
	 * priority value.
	 *
	 * For example, a fully customized call:
	 * $this->hook( 'hook_name', 'method_name', 10 );
	 *
	 * @access protected
	 * @since  0.7.0
	 *
	 * @param mixed $hook The hook name
	 * @return Hook The current object, for chaining
	 */
	protected function hook( $hook ) {

		/** Setup default priority. */
		$prio = 10;

		/** Initially set the method name to the hook name. */
		$method_name = self::sanitize_method( $hook );

		/** Process additional arguments. */
		$args = func_get_args();

		/** Unset the hook name. */
		unset( $args[0] );

		foreach ( (array)$args as $arg ){
			/** If the next argument is an integer => priority : else => method_name */
			if( is_int( $arg ) )
				$prio = $arg;
			else
				$method_name = $arg;
		}

		add_filter( $hook, array( $this, $method_name ), $prio, 999 );

		return $this;
	}

	/**
	 * Sanitizes method names by replacing dots '.' and dashes '-'
	 * with the special strings '_DOT_' and '_DASH_'
	 *
	 * @access private
	 * @since  0.7.0
	 *
	 * @param string $method_name The method name
	 * @return string The sanitized method name
	 */
	private static function sanitize_method( $method_name ) {
		return str_replace(
			array( '.', '-' ),
			array( '_DOT_', '_DASH_' ),
			$method_name
		);
	}
}
