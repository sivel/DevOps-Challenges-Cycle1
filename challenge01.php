<?php

// Set up args for this script
$args = array(
	'r:' => array(
		'desc' => 'Region',
	),
	'n:' => array(
		'desc' => 'Name'
	),
	'i:' => array(
		'desc' => 'Image'
	),
);

// Import the arg parsing file
require( 'args.php' );

// Import the setup/auth file
require( 'setup.php' );

if ( ! check_region_svc( 'compute', $options['r'], $regions ) ) {
	printf( "You do not have access to compute in region %s\n", $options['r'] );
	exit(1);
}

$compute = $client->computeService( 'cloudServersOpenStack', $options['r'] );

echo "Starting build...\n";
$server = $compute->server();
$server->create( array(
	'name' => $options['n'],
	'image' => $compute->Image( $options['i'] ),
	'flavor' => $compute->Flavor(2),
	'OS-DCF:diskConfig' => 'MANUAL'
) );

$adminPassword = $server->adminPass;

// Wait for the server to become active, with a 300 second timeout
$server->waitFor( 'ACTIVE', 300, 'status' );

if ( $server->status() == 'ERROR' ) {
	echo "Server create failed with ERROR\n";
}

$accessIPv4 = $server->accessIPv4;

echo "\n";
echo "IP Address: $accessIPv4\n";
echo "Password: $adminPassword\n";
