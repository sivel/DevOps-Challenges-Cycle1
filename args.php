<?php
/* This file helps with processing different command line args from individual scripts */

// $args should come from the including script, but in case none are needed
// initialize an empty array
if ( ! isset( $args ) ) {
	$args = array();
}

// Add -h for help. All scripts get this
$args = array_merge( $args, array(
	'h' => array(),
) );

$short = array();
$help = array();
$required = array();

// Process php getopt short args and set up help for each
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

// Parse the options
$options = getopt( implode( $short ) );

// If there are required args and any were not provided, add the -h flag
// so that we output the help message instead of running
foreach ( $required as $arg ) {
	if ( ! array_key_exists( $arg, $options ) ) {
		$options['h'] = array();
	}
}

// If -h is set, display the help info
if ( array_key_exists( 'h', $options ) ) {
	echo "\n" . $argv[0] . ' ' . implode( ' ', $help ) . "\n";
	exit();
}
