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
 * The main file of the BootstrapComponents extension, when loaded via Composer.
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
 * mwjames and JeroenDeDauw who both where kind enough to help me getting better
 * in coding for mediawiki.
 *
 * @file
 * @ingroup       BootstrapComponents
 */

namespace BootstrapComponents;

use \MWException;

/**
 * Provides methods to register, when installed by composer
 *
 * @since 1.0
 *
 * @codeCoverageIgnore
 */
class BootstrapComponents {

	/**
	 * @throws \MWException
	 */
	public static function load() {

		if ( self::doCheckRequirements() ) {
			wfLoadExtension( 'BootstrapComponents' );
		}
	}

	/**
	 * @throws \MWException
	 *
	 * @return bool
	 */
	public static function doCheckRequirements() {

		if ( !defined( 'MEDIAWIKI' ) ) {
			echo 'This file is part of the Mediawiki extension BootstrapComponents, it is not a valid entry point.' . PHP_EOL;
			throw new MWException( 'This file is part of a Mediawiki Extension, it is not a valid entry point.' );
		}

		if ( version_compare( $GLOBALS[ 'wgVersion' ], '1.27', 'lt' ) ) {
			echo '<b>Error:</b> <a href="https://github.com/oetterer/BootstrapComponents/">Bootstrap Components</a> '
				. 'is only compatible with MediaWiki 1.27 or above. You need to upgrade MediaWiki first.' . PHP_EOL;
			throw new MWException( 'BootstrapComponents detected an incompatible MediaWiki version. Exiting.' );
		}

		if ( defined( 'BOOTSTRAP_COMPONENTS_VERSION' ) ) {
			// Do not initialize more than once.
			return false;
		}
		return true;
	}

	/**
	 * Returns version number of Extension BootstrapComponents
	 * @return float
	 */
	public static function getVersion() {
		return BOOTSTRAP_COMPONENTS_VERSION;
	}
}


/**
 * This file is loaded, when using composer. Defers initialization to wfLoadExtension as if called by LocalSettings.
 *
 * @see https://github.com/oetterer/BootstrapComponents/
 */
BootstrapComponents::load();