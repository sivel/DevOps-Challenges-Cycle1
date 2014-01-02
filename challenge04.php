<?php

// Set up args for this script
$args = array(
	'r:' => array(
		'desc' => 'Region',
	),
	'c:' => array(
		'desc' => 'Container'
	),
	'd:' => array(
		'desc' => 'Directory'
	),
);

// Import the arg parsing file
require( 'args.php' );

if ( ! is_dir( $options['d'] ) ) {
	printf( "The directory (%s) does not exist\n", $options['d'] );
}

// Import the setup/auth file
require( 'setup.php' );

$cloudfiles = $client->objectStoreService( 'cloudFiles', $options['r'] );

try {
	$cloudfiles->getContainer( $options['c'] );
	echo "This container already exists. Cannot continue\n";
	exit(1);
} catch ( Guzzle\Http\Exception\ClientErrorResponseException $e ) {
	// Make sure the error was because the container didn't exist
	if ( $e->getResponse()->getStatusCode() != 404 ) {
		echo "Cannot continue:\n";
		echo $e->getResponse()->getMessage();
		exit(1);
	}
	printf( "Creating container: %s\n", $options['c'] );
	$container = $cloudfiles->createContainer( $options['c'] );
}

printf( "Uploading directory (%s) to container (%s)\n", $options['d'], $options['c'] );
$container->uploadDirectory( $options['d'] );

printf( "CDN enabling container" );
$container->enableCdn();

$cdn = $container->getCdn();
echo "";
printf("CDN HTTP URL: %s\n", $cdn->getCdnUri());
printf("CDN HTTPS URL: %s\n", $cdn->getCdnSslUri());
printf("CDN Streaming URL: %s\n", $cdn->getCdnStreamingUri());
printf("CDN iOS Streaming URL: %s\n", $cdn->getIosStreamingUri());
