<?php
/* This file helps with processing different command line args from individual scripts */

if ( ! isset( $args ) ) {
	$args = array();
}

$args = array_merge( $args, array(
	'h' => array(),
) );

$short = array();
$help = array();
$required = array();

foreach ( $args as $arg => $config ) {
	$stripped = preg_replace( '/[^\w]+/', '', $arg );
	$short[] = $arg;
	if ( strlen( $arg ) == 3 ) {
		$help[] = '[-' . $stripped . ' ' . strtoupper( $config['desc'] ) . ']';
	} else if ( strlen( $arg ) == 2 ) {
		$help[] = '-' . $stripped . ' ' . strtoupper( $config['desc'] );
		$required[] = $stripped;
	} else {
		$help[] = '[-' . $stripped . ']';
	}
}

$options = getopt( implode( $short ) );

foreach ( $required as $arg ) {
	if ( ! array_key_exists( $arg, $options ) ) {
		$options['h'] = array();
	}
}

if ( array_key_exists( 'h', $options ) ) {
	echo "\n" . $argv[0] . ' ' . implode( ' ', $help ) . "\n";
	exit();
}
