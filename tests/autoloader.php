<?php

/**
 * Convenience autoloader to pre-register test classes
 */
if ( PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg' ) {
	die( 'Not an entry point' );
}

if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'MediaWiki is not available.' );
}

if ( !class_exists( 'BootstrapComponents\BootstrapComponents' ) ) {
	die( "\nBootstrapComponents is not available, please check your LocalSettings or Composer settings.\n" );
}

$basePath = getenv( 'MW_INSTALL_PATH' ) !== false
		? getenv( 'MW_INSTALL_PATH' )
		: __DIR__ . '/../../..';

if ( is_readable( $path = $basePath . '/vendor/autoload.php' ) ) {
	$autoloadType = "MediaWiki vendor autoloader";
} elseif ( is_readable( $path = __DIR__ . '/../vendor/autoload.php' ) ) {
	$autoloadType = "Extension vendor autoloader";
} else {
	die( 'To run the test suite it is required that packages are installed using Composer.' );
}

// Extensions are able to define this in case the output requires an extended
// width due to a long extension name.
if ( !defined( 'PHPUNIT_FIRST_COLUMN_WIDTH' ) ) {
	define( 'PHPUNIT_FIRST_COLUMN_WIDTH', 20 );
}

/**
 * getting the autoloader and registering test classes
 */
/** @var \Composer\Autoload\ClassLoader $autoloader */
$autoloader = require $path;

#$autoloader->addPsr4( 'BootstrapComponents\\Tests\\Unit\\', __DIR__ . '/phpunit/Unit' );
#$autoloader->addPsr4( 'BootstrapComponents\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );
$autoloader->addPsr4( 'BootstrapComponents\\Tests\\', __DIR__ . '/phpunit' );
@include_once __DIR__ . '/phpunit/Unit/ComponentsTestBase.php';

return $autoloader;
