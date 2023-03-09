<?php
/**
 * An extension building on the Bootstrap Extension that provides certain components
 * inside mediawiki markup as parser functions or parser tags.
 *
 * @see      https://www.mediawiki.org/wiki/Extension:BootstrapComponents
 * @see      https://www.mediawiki.org/wiki/Extension:Bootstrap
 *
 * @author   Tobias Oetterer
 *
 * @defgroup BootstrapComponents BootstrapComponents
 */

/**
 * The main file of the BootstrapComponents extension, responsible for initializing
 * the whole shabang.
 *
 * @copyright (C) 2018, Tobias Oetterer, Paderborn University
 * @license       https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 (or later)
 *
 * This file is part of the MediaWiki extension BootstrapComponents.
 * The BootstrapComponents extension is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The BootstrapComponents extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * My thanks go to Stephan Gambke for creating the Bootstrap extension and to
 * mwjames and JeroenDeDauw who both where kind enough to help me get better
 * in coding for mediawiki. And finally a shout-out to Karsten Hoffmeyer,
 * always an inspiration and quick in lending a hand.
 *
 * @file
 * @ingroup       BootstrapComponents
 */

namespace BootstrapComponents;

use ConfigException;
use Exception;
use ExtensionRegistryHelper\ExtensionRegistryHelper;
use MWException;

/**
 * Provides methods to register, when installed by composer
 *
 * @since 1.0
 *
 * @codeCoverageIgnore
 */
class BootstrapComponents {

	/**
	 * @var bool $hooksRegistered
	 */
	private static $hooksRegistered = false;

	/**
	 * @var string $version
	 */
	private static $version;

	/**
	 * Add this to extension.json's 'callable' entry.
	 *
	 * @param array $info
	 *
	 * @throws Exception when extension Bootstrap cannot be loaded recursively
	 *
	 * @return void
	 */
	public static function init( array $info ) {

		// loads local composer libraries, if present
		if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
			include_once __DIR__ . '/vendor/autoload.php';
		}

		self::$version = $info['version'] ?? 'UNKNOWN';

		// should be loaded manually in LocalSettings.php. If not, we give it a try!
		ExtensionRegistryHelper::singleton()->loadExtensionRecursive( 'Bootstrap' );
	}

	/**
	 * Since 1.37 extension.json's "callback" is too early for HookRegistry, since all the MW-Services and the
	 * ConfigFactory is not ready, yet.
	 *
	 * @throws ConfigException cascading {@see HookRegistry::__construct} and {@see HookRegistry::run}
	 * @throws MWException cascading {@see HookRegistry::__construct}
	 *
	 * @return void
	 */
	public static function onExtensionFunction() {
		if ( self::doCheckRequirements() ) {
			$hookRegistry = new HookRegistry();
			$hookRegistry->run();
			self::$hooksRegistered = true;
		}
	}

	/**
	 * @return bool
	 * @throws MWException
	 *
	 */
	public static function doCheckRequirements(): bool
	{
		if ( !defined( 'MEDIAWIKI' ) ) {
			echo 'This file is part of the Mediawiki extension BootstrapComponents, it is not a valid entry point.' . PHP_EOL;
			throw new MWException( 'This file is part of a Mediawiki Extension, it is not a valid entry point.' );
		}

		if ( version_compare( $GLOBALS[ 'wgVersion' ], '1.35', 'lt' ) ) {
			echo '<b>Error:</b> <a href="https://github.com/oetterer/BootstrapComponents/">Bootstrap Components</a> '
				. 'is only compatible with MediaWiki 1.35 or above. You need to upgrade MediaWiki first.' . PHP_EOL;
			throw new MWException( 'BootstrapComponents detected an incompatible MediaWiki version. Exiting.' );
		}

		if ( self::hooksRegistrationDone() ) {
			// Do not initialize more than once.
			return false;
		}
		return true;
	}

	/**
	 * Returns version number of Extension BootstrapComponents
	 *
	 * @return string
	 */
	public static function getVersion(): string
	{
		return self::$version ?: 'UNDEFINED';
	}

	/**
	 * @return bool
	 */
	public static function hooksRegistrationDone(): bool
	{
		return self::$hooksRegistered;
	}
}
