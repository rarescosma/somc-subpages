<?php

function dd( $what ) {
	var_dump( $what ); die();
}

function d( $what ) {
	var_dump( $what );
}

add_filter( 'ric_debug', '__return_true' );