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

# get the autoloader
#
# note, that the autoloader also
# * registers all test classes
# * fills (string) $autoloadType
$autoloadType = 'unknown';
$autoloader = require __DIR__ . '/autoloader.php';

# Not, add some runtime information
require __DIR__ . '/TestInfoScreen.php';
require __DIR__ . '/PhpUnitEnvironment.php';

$phpUnitEnvironment = new \BootstrapComponents\Tests\PHPUnitEnvironment();

if ( $phpUnitEnvironment->hasDebugRequest( $GLOBALS['argv'] ) === false ) {
	$phpUnitEnvironment->emptyDebugVars();
}

$testInfoScreen = new \BootstrapComponents\Tests\TestInfoScreen( 25 );

$testInfoScreen->addInfoToBlock( "MediaWiki:", $phpUnitEnvironment->getSoftwareInfo( 'mw' ) );
$testInfoScreen->addInfoToBlock( "Bootstrap:", $phpUnitEnvironment->getSoftwareInfo( 'Bootstrap' ) );
$testInfoScreen->addInfoToBlock( "BootstrapComponents:", $phpUnitEnvironment->getSoftwareInfo( 'BootstrapComponents' ) );
$testInfoScreen->newBlock();

$testInfoScreen->addInfoToBlock( "Autoloader:", $autoloadType );
$testInfoScreen->addInfoToBlock( "Database:", $phpUnitEnvironment->getDbType() );
$testInfoScreen->addInfoToBlock( "Site language:", $phpUnitEnvironment->getSiteLanguageCode() );
$testInfoScreen->newBlock();

$testInfoScreen->addInfoToBlock( "SemanticMediaWiki:", $phpUnitEnvironment->getSoftwareInfo( 'smw' ) );
$testInfoScreen->addInfoToBlock( "Scribunto:", $phpUnitEnvironment->getSoftwareInfo( 'Scribunto' ) );
$testInfoScreen->newBlock();

$testInfoScreen->addInfoToBlock( "PHPUnit:", $phpUnitEnvironment->getPhpUnitVersion() );
$testInfoScreen->addInfoToBlock( "Debug logs:", ( $phpUnitEnvironment->enabledDebugLogs() ? 'Enabled' : 'Disabled' ) );
$testInfoScreen->addInfoToBlock( "Xdebug:", ( ( $version = $phpUnitEnvironment->getXdebugInfo() ) ? $version : 'Disabled (or not installed)' ) );
$testInfoScreen->addInfoToBlock( "Intl/ICU:", ( ( $intl = $phpUnitEnvironment->getIntlInfo() ) ? $intl : 'Disabled (or not installed)' ) );
$testInfoScreen->addInfoToBlock( "PCRE:", ( ( $pcre = $phpUnitEnvironment->getPcreInfo() ) ? $pcre : 'Disabled (or not installed)' ) );
$testInfoScreen->newBlock();

$testInfoScreen->addInfoToBlock( "Execution time:", $phpUnitEnvironment->executionTime() );

$testInfoScreen->printScreen();

unset( $phpUnitEnvironment );
unset( $testInfoScreen );
