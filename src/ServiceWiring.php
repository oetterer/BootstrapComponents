<?php

/**
 * Adds some service wiring to MediaWikiServices
 *
 * @since 5.2
 */

use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
use MediaWiki\Extension\BootstrapComponents\ComponentLibrary;
use MediaWiki\Extension\BootstrapComponents\NestingController;
use MediaWiki\MediaWikiServices;

return [
	'BootstrapComponentsService' =>
		static function ( MediaWikiServices $services ): BootstrapComponentsService {
			return new BootstrapComponentsService(
				$services->getMainConfig()
			);
		},
	'BootstrapComponents.ComponentLibrary' =>
		static function ( MediaWikiServices $services ): ComponentLibrary {
			$myConfig = $services->getConfigFactory()->makeConfig('BootstrapComponents');
			$whileList = $myConfig->has( 'BootstrapComponentsWhitelist' )
				?$myConfig->get( 'BootstrapComponentsWhitelist' ) : true;
			return new ComponentLibrary( $whileList );
		},
	'BootstrapComponents.NestingController' =>
		static function ( MediaWikiServices $services ): NestingController {
			return new NestingController();
		},
];
