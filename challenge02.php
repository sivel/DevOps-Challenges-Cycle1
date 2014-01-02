<?php

// Set up the args for this script
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
	'c:' => array(
		'desc' => 'Count'
	),
	'k:' => array(
		'desc' => 'sshkey'
	),
);

// Import the arg parsing file
require( 'args.php' );

// Make sure the SSH key exists
if ( ! file_exists( $options['k'] ) ) {
	printf("SSH Key (%s) does not exist\n", $options['k']);
	exit(1)
}
// Read in the SSH key
$sshkey = file_get_contents( $options['k'] );

// Import the setup/auth file
require( 'setup.php' );

$compute = $client->computeService( 'cloudServersOpenStack', $options['r'] );

echo "Starting build...\n";

$servers = array();
foreach ( range( 1, (int) $options['c'] ) as $i ) {
	// Set the server name using printf
	$name = sprintf($options['n'], $i);
	// If the name didn't have printf formatting add the number to the end
	if ( $name == $options['n'] ) {
		$name = $options['n'] . $i;
	}
	$servers[] = $compute->server();
	end( $servers )->addFile( '/root/.ssh/authorized_keys', $sshkey );
	end( $servers )->create( array(
		'name' => $name,
		'image' => $compute->Image( $options['i'] ),
		'flavor' => $compute->Flavor(2),
		'OS-DCF:diskConfig' => 'MANUAL'
	) );
}

// This is a custom helper that can be found in setup.php
waitForMany( $servers, 'ACTIVE', 300, 'status' );

foreach ( $servers as $server ) {
	if ( $server->status() == 'ERROR' ) {
		printf("Server (%s) create failed with ERROR\n", $server->id);
	}
}

echo "\n============================================================================\n";
foreach ( $servers as $server ) {
	echo "Name: {$server->name}\n";
	echo "ID: {$server->id}\n";
	echo "IP Address: {$server->accessIPv4}\n";
	echo "Password: {$server->adminPass}\n";
	echo "============================================================================\n";
}
