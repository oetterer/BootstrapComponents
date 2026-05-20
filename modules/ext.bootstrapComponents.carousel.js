/**
 * Contains javascript code executed when carousels are used.
 *
 * Bootstrap 5 migration: Converted from jQuery to vanilla JavaScript.
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
 * @file
 * @ingroup       BootstrapComponents
 * @author        Tobias Oetterer
 */

( function () {
	'use strict';

	// Wait for DOM to be ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initCarousels );
	} else {
		initCarousels();
	}

	function initCarousels() {
		// Bootstrap 5 automatically initializes carousels with data-bs-ride="carousel"
		// Manual initialization for additional control
		var carouselElements = document.querySelectorAll( '.carousel' );
		carouselElements.forEach( function ( element ) {
			if ( typeof bootstrap !== 'undefined' && bootstrap.Carousel ) {
				new bootstrap.Carousel( element );
			}
		} );
	}
}() );
