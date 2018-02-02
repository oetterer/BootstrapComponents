<?php

if ( PHP_SAPI !== 'cli' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );
ini_set( 'display_errors', 1 );

if ( !ExtensionRegistry::getInstance()->isLoaded( 'BootstrapComponents' ) ) {
	die( "\nBootstrapComponents is not available or loaded, please check your Composer or LocalSettings.\n" );
}

if ( !is_readable( $autoloaderClassPath = __DIR__ . '/../../SemanticMediaWiki/tests/autoloader.php' ) ) {
	die( "\nThe Semantic MediaWiki test autoloader is not available. Needed for integration tests!" );
}

$version = print_r( ExtensionRegistry::getInstance()->getAllThings()['BootstrapComponents']['version'], true );

$dateTimeUtc = new \DateTime( 'now', new \DateTimeZone( 'UTC' ) );
print sprintf( "\n%-22s%s\n", "MediaWiki: ", $GLOBALS['wgVersion'] );
print sprintf( "%-22s%s\n", "Bootstrap: ", BS_VERSION );
print sprintf( "%-22s%s\n", "BootstrapComponents: ", $version );
print sprintf( "\n%-22s%s\n", "Execution time:", $dateTimeUtc->format( 'Y-m-d H:i' ) );
print sprintf( "%-22s%s\n", "Debug logs:", $GLOBALS['wgDebugLogGroups'] !== [] || $GLOBALS['wgDebugLogFile'] !== '' ? 'Enabled' : 'Disabled' );

$autoLoader = require $autoloaderClassPath;
$autoLoader->addPsr4( 'BootstrapComponents\\Tests\\Unit\\', __DIR__ . '/phpunit/Unit' );
$autoLoader->addPsr4( 'BootstrapComponents\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );
unset( $autoLoader );
