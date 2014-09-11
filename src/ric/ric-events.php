<?php
namespace RIC;

/**
 * Hooks into create/update/delete operations for the main WordPress
 * entities and funnels them into a single 'ric_entity_change' action.
 *
 * Additionally, a 'ric_entity_change_{$entity_type}_{$args}' is fired,
 * where $entity_type is one of self::$entities, and the $args string
 * is formed by arguments of the ->fire() method.
 *
 * These actions can be used to purge cache fragments or fragment groups.
 *
 * @see RIC\Fragments for a concrete use case.
 *
 * @since  0.8.5
 */

class Events extends Hook {
	/**
	 * Triggered action name.
	 *
	 * @var string
	 * @since  0.8.5
	 */
	const ACTION = 'ric_entity_change';

	/**
	 * Built-in WordPress entities that the class will proxy events for.
	 *
	 * @var array
	 * @since  0.8.5
	 */
	static $entities = array( 'post', 'term', 'user' );

	/**
	 * Constructor.
	 *
	 * @since  0.8.5
	 *
	 * @return RIC\Events
	 */
	function __construct() {
		foreach ( self::$entities as $entity ) {
			call_user_func_array( array( $this, 'proxy_' . $entity . 's' ), array() );
		}
	}

	/**
	 * Fires the 'ric_entity_change' and 'ric_entity_change_{$entity_type}_{$args}'
	 * actions.
	 *
	 * @access public
	 * @since  0.8.5
	 *
	 * @param  string $entity_type The entity type (post, term, etc.)
	 * @param  string $id          The etitiy ID
	 * @return void
	 */
	protected function fire( $entity_type, $id ) {
		static $fired;

		if ( !isset( $fired ) ) {
			$fired = array_fill_keys( self::$entities, array() );
		}

		$args = array_slice( func_get_args() , 2 );

		// Make sure we're firing a CRUD event only once
		if ( !isset( $fired[$entity_type][$id] ) ) {
			$fired[$entity_type][$id] = true;
			do_action_ref_array( self::ACTION, array_merge( array( $entity_type, $id ), $args ) );
			do_action( self::ACTION . "_${entity_type}_" . implode( '_', $args ) );
		}
	}

	/**
	 * Hooks into post-relevant actions: save_post, transition_post_status,
	 * update_post_meta and transition_comment_status.
	 *
	 * @access protected
	 * @since 0.8.5
	 *
	 * @return void
	 */
	protected function proxy_posts() {
		// Proxy create / update
		$this->hook( 'save_post', 99 );

		// Proxy publish / unpublish (equivalent to create / delete)
		$this->hook( 'transition_post_status', 99 );

		// Proxy meta changes
		$this->hook( 'update_post_meta', 99 );

		// Proxy comment changes
		$this->hook( 'transition_comment_status', 99 );
	}

	/**
	 * Published posts affect the state, so we need to fire our action
	 * when 'save_post' is triggered, if the status is 'publish'
	 *
	 * @access public
	 * @since 0.8.5
	 *
	 * @param  string  $id     The post ID
	 * @param  WP_Post $post   The post object
	 * @return void
	 */
	public function save_post( $id, $post ) {
		if ( isset( $post->post_status ) && 'publish' === $post->post_status ) {
			$this->fire( 'post', $id, $post->post_type );
		}
	}

	/**
	 * If a post lost or gained the 'publish' status, it would have affected
	 * the state.
	 *
	 * @access public
	 * @since 0.8.5
	 *
	 * @param  string  $new_status The new status
	 * @param  string  $old_status The old status
	 * @param  WP_Post $post       The post object
	 * @return void
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {
		if ( 'publish' === $new_status || 'publish' === $old_status ) {
			$this->fire( 'post', $post->ID, $post->post_type );
		}
	}

	/**
	 * Meta changes affec the state, but we're not counting meta keys that
	 * start with '_' - they are builtin
	 *
	 * @access public
	 * @since 0.8.5
	 *
	 * @param  int    $meta_id   The meta ID
	 * @param  int    $object_id The post ID
	 * @param  string $meta_key  The meta key that changed
	 * @return void
	 */
	public function update_post_meta( $meta_id, $object_id, $meta_key ) {
		// Skip builtin stuff
		if ( 0 === strpos( $meta_key, '_' ) ) {
			return;
		}

		$this->fire( 'post', $object_id, get_post_type( $object_id ) );
	}

	/**
	 * Comments that either lose or gain the 'approve' status affect the state.
	 *
	 * @access public
	 * @since 0.8.5
	 *
	 * @param  string $new_status The new status
	 * @param  string $old_status The old status
	 * @param  object $comment    Comment data
	 * @return void
	 */
	public function transition_comment_status( $new_status, $old_status, $comment ) {
		if ( 'approve' === $new_status || 'approve' === $old_status ) {
			$this->fire( 'post', $comment->comment_post_ID, get_post_type( $comment->comment_post_ID ) );
		}
	}

	/**
	 * Creating, editing or deleting a term affects the state.
	 *
	 * @access protected
	 * @since 0.8.5
	 *
	 * @return void
	 */
	protected function proxy_terms() {
		$t = $this;
		$closure = function( $id, $tt_id, $taxonomy ) use ( &$t ) {
			if ( !empty( $id ) && is_numeric( $id ) ) {
				$t->fire( 'term', $id, $taxonomy );
			}
		};

		// CRUD
		add_action( 'create_term', $closure, 99, 3 );
		add_action( 'edit_term', $closure, 99, 3 );
		add_action( 'delete_term', $closure, 99, 3 );
	}

	/**
	 * Creating, editing or deleting an user affets the state.
	 *
	 * @access protected
	 * @since 0.8.5
	 *
	 * @return void
	 */
	protected function proxy_users() {
		$t = $this;

		$closure = function( $id ) use ( &$t ) {
			if ( !empty( $id ) && is_numeric( $id ) ) {
				$t->fire( 'user', $id );
			}
		};

		// CRUD
		add_action( 'user_register', $closure, 99, 1 );
		add_action( 'profile_update', $closure, 99, 1 );
		add_action( 'delete_user', $closure, 99, 1 );
	}
}
