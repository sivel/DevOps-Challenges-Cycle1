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

/**
 * Check to make sure that a service/region pair are available to the user
 */
function check_region_svc( $service, $region, $regions ) {
	if ( isset( $regions[$region][$service] ) || isset( $regions['all'][$service] ) ) {
		return true;
	} else {
		return false;
	}
}

if ( ! file_exists( $_SERVER['HOME'] . '/.rackspace_cloud_credentials' ) ) {
	printf("The required credentials file (%s) does not exist\n", $_SERVER['HOME'] . '/.rackspace_cloud_credentials');
	exit(1);
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

$regions = array();
$catalog = $client->getCatalog();

foreach ( $catalog->getItems() as $catalogItem ) {
	if ( $catalogItem->getName() == 'cloudServers' ) {
		continue;
	}
	$type = $catalogItem->getType();
	foreach ( $catalogItem->getEndpoints() as $endpoint ) {
		$region = empty( $endpoint->region ) ? 'all' : $endpoint->region;
		$regions[$region][$type] = array();
	}
}
