<?php
// Import the arg parsing file
require( 'args.php' );

// Import the setup/auth file
require( 'setup.php' );

if ( ! check_region_svc( 'rax:dns', 'all', $regions ) ) {
	printf( "You do not have access to Cloud DNS\n" );
	exit(1);
}

$dns = $client->dnsService();
$domains = $dns->domainList();

// Print out a list of domains prefixed with a number for selection
foreach ( $domains as $i => $domain ) {
	printf( "%d) %s\n", (int) $i + 1, $domain->name() );
}

// Wait for the user to give make an acceptable selection
do {
	$selection = readline( 'Select a domain to create a record for: ' );
} while ( ! isset( $domains[(int) $selection - 1] ) );

// Grab the appropriate domain from the user selection
$domain = $domains->getElement((int) $selection - 1);

printf( "\nAdding an 'A' record for %s:\n", $domain->name );
$ipaddress = readline( 'IP Address: ' );
$ttl = readline( 'TTL: ' );
$name = readline( 'DNS Record Name: ' );

// Some processing to add the domain onto the string if it's not there
if ( ! strstr( $name, strtolower( $domain->name ) ) ) {
	$name = rtrim( strtolower( $name ), '.' );
	$name = sprintf( '%s.%s', $name, strtolower( $domain->name ) );
}

$record = $domain->record();
$asyncResponse = $record->create(
	array(
		'type' => 'A',
		'ttl'  => (int) $ttl,
		'name' => $name,
		'data' => $ipaddress
	)
);
// Wait for the record to be created
$asyncResponse->waitFor( 'COMPLETED', 300, False, 1);

if ($asyncResponse->status() == 'ERROR') {
	echo "\nFailed to create DNS record\n";
	exit(1);
}

echo "\nRecord created\n";
