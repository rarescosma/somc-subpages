<?php

define( 'SOMC_VERSION', '0.0.2' );
define( 'SOMC_PAGES_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'SOMC_PAGES_URL', plugin_dir_url( __FILE__ ) );
define( 'SOMC_PAGES_SHORTCODE', apply_filters( 'somc_subpages_shortcode_tag', 'somc_subpages' ) );

RIC::init( SOMC_PAGES_DIR . '/templates' );

/**
 * Purge the widget fragments whenever a page changes status
 * or the post_cache is purged
 */
RIC\Fragments::init( array(
	'somc_sp_widget_' => array( 'ric_entity_change_post_page', 'clean_post_cache' ),
	'somc_sp_shortcode_' => array( 'ric_entity_change_post_page', 'clean_post_cache' )
) );