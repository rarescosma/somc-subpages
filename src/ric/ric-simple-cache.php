<?php
namespace RIC;
use \ArrayAccess;

/**
 * Acts as an abstraction layer over the WordPress transient API
 * - prevents the same transient from being saved or fetched twice
 * during a request.
 * - objects of this class can be used as arrays to get/set/unset transients:
 * $cache = new RIC\Simple_Cache();
 *
 * $cache['foo'] = 'bar';  // Will set the transient 'foo' to 'bar'
 *
 * $x = $cache['foo'];     // Will return the value of the 'foo' transient
 *
 * unset( $cache['foo'] ); // Will delete the 'foo' transient
 *
 * @since  0.8.5
 */

class Simple_Cache implements ArrayAccess {
	/**
	 * Transient expiration time.
	 *
	 * @var int
	 * @since  0.8.5
	 */
	private $expiration = 0;

	/**
	 * Acts as a cache for transient values.
	 *
	 * @var array
	 * @since  0.8.5
	 */
	private $cache = array();

	/**
	 * Holds transient names that were saved during a request.
	 *
	 * @var array
	 * @since  0.8.5
	 */
	private $saved_keys = array();

	/**
	 * Holds transient names that were fetched during a request.
	 *
	 * @var array
	 * @since  0.8.5
	 */
	private $fetched_keys = array();

	/**
	 * If TRUE, skip the always return a falsy value.
	 *
	 * @var bool
	 * @since  0.8.5
	 */
	private $debug;

	/**
	 * Pass me a single integer argument to override the default expiration time,
	 * which is 0. (== expiration disabled)
	 *
	 * @since  0.8.5
	 *
	 * @param int $expiration Expiration time
	 * @return RIC\Simple_Cache
	 */
	public function __construct( $expiration = null ) {
		if ( is_int( $expiration ) ) {
			$this->expiration = $expiration;
		}

		// Use this hook to turn on debugging, which completely disables the cache
		$this->debug = apply_filters( 'ric_debug', false );
	}

	/**
	 * \ArrayAccess implementation for checking if a given key exists
	 *
	 * @access public
	 * @since  0.8.5
	 *
	 * @param  mixed  $key The key
	 * @return bool
	 */
	public function offsetExists( $key ) {
		return array_key_exists( $key, $this->cache );
	}

	/**
	 * \ArrayAccess implementation for setting a given key in the "array" to
	 * a given value.
	 *
	 * @access public
	 * @since  0.8.5
	 *
	 * @param  mixed  $key   The key
	 * @param  mixed  $value The value
	 * @return void
	 */
	public function offsetSet( $key, $value ) {
		// Don't save the same transient twice
		if ( !in_array( $key, $this->saved_keys ) ) {
			set_transient( $key, $value, $this->expiration );
			$this->saved_keys[] = $key;
		}

		$this->cache[$key] = $value;
	}

	/**
	 * \ArrayAccess implementation for getting the value assigned to a key.
	 *
	 * @access public
	 * @since  0.8.5
	 *
	 * @param  mixed  $key The key
	 * @return bool|mixed
	 */
	public function offsetGet( $key ) {
		if ( $this->debug ) {
			return false;
		}

		if ( $this->offsetExists( $key ) ) {
			return $this->cache[$key];
		}

		// Don't fetch the same transient twice
		if ( !in_array( $key, $this->fetched_keys ) ) {
			$this->cache[$key] = get_transient( $key );
			$this->fetched_keys[] = $key;
		}

		// We should have a value by now
		return $this->cache[$key];
	}

	/**
	 * \ArrayAccess implementation to unset a key
	 *
	 * @access public
	 * @since  0.8.5
	 *
	 * @param  mixed  $key The key
	 * @return void
	 */
	public function offsetUnset( $key ) {
		delete_transient( $key );
		unset( $this->cache[$key] );

		$closure = function( $_key ) use ( $key ) {
			return $_key !== $key;
		};

		$this->fetched_keys = array_filter( $this->fetched_keys, $closure );
		$this->saved_keys = array_filter( $this->saved_keys, $closure );
	}
}
