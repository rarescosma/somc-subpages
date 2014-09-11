<?php

define( 'SEMC_VERSION', '0.0.2' );
define( 'SEMC_PAGES_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'SEMC_PAGES_URL', plugin_dir_url( __FILE__ ) );
define( 'SEMC_PAGES_SHORTCODE', apply_filters( 'semc_subpages_shortcode_tag', 'semc_subpages' ) );

RIC::init( SEMC_PAGES_DIR . '/templates' );

/**
 * Purge the widget fragments whenever a page changes status
 * or the post_cache is purged
 */
RIC\Fragments::init( array(
	'semc_sp_widget_' => array( 'ric_entity_change_post_page', 'clean_post_cache' ),
	'semc_sp_shortcode_' => array( 'ric_entity_change_post_page', 'clean_post_cache' )
) );