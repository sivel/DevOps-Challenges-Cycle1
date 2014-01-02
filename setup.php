<?php
/* This file helps with parsing creds file and authenticating.  It also provides some helper functions */

require( 'vendor/autoload.php' );

/**
 * A status callback function for waitFor function calls
 *
 * Outputs server name, status and progress
 */
function status ( $item ) {
	printf( "%s: %s / %s%%\n", $item->name, $item->status, $item->progress );
}

/**
 * Filter out servers in specified states so we know when an array of servers
 * has been built
 */
function filter_servers( $server, $state = 'ACTIVE' ) {
	$states = array( 'ERROR', $state );
	return ( ! in_array( $server->status(), $states ) );
}

/**
 * Function similar to waitFor provided by php-opencloud, but designed to monitor many servers
 */
function waitForMany( $servers,  $state = 'ACTIVE', $timeout = 3600, $callback = null, $interval = 10 ) {
	// save stats
	$startTime = time();

	$states = array( 'ERROR', $state );

	while ( true ) {
		foreach ( $servers as $i => $server ) {
			$servers[$i]->refresh( $server->id );

			if ( $callback ) {
				call_user_func( $callback, $servers[$i] );
			}

			if ( ! array_filter( $servers, 'filter_servers' ) || ( time() - $startTime ) > $timeout ) {
				return;
			}

		}

		sleep( $interval );
	}
}

// Parse the ini file
$credentials = parse_ini_file( $_SERVER['HOME'] . '/.rackspace_cloud_credentials', true );

use OpenCloud\Rackspace;

// Set up the identity client
$client = new Rackspace( Rackspace::US_IDENTITY_ENDPOINT, array(
    'username' => $credentials['rackspace_cloud']['username'],
    'apiKey'   => $credentials['rackspace_cloud']['api_key']
) );

// Authenticate
$client->authenticate();
