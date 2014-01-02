<?php
/* This file helps with parsing creds file and authenticating.  It also provides some helper functions */

require( 'vendor/autoload.php' );

function status ( $item ) {
	printf( "%s: %s / %s%%\n", $item->name, $item->status, $item->progress );
}

function filter_servers( $server, $state = 'ACTIVE' ) {
	$states = array( 'ERROR', $state );
	return ( ! in_array( $server->status(), $states ) );
}

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

$credentials = parse_ini_file( $_SERVER['HOME'] . '/.rackspace_cloud_credentials', true );

use OpenCloud\Rackspace;

$client = new Rackspace( Rackspace::US_IDENTITY_ENDPOINT, array(
    'username' => $credentials['rackspace_cloud']['username'],
    'apiKey'   => $credentials['rackspace_cloud']['api_key']
) );

$client->authenticate();
