/**
 * Contains javascript code executed when modals are used.
 *
 * Bootstrap 5 migration: BS5 dropped the jQuery `.modal()` plugin, and
 * modal triggers (elements with `data-bs-toggle="modal"`) are no longer
 * auto-initialised the way BS4 did. Instantiate a `bootstrap.Modal` for each
 * modal container so the data-attribute triggers will actually open it.
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
		document.addEventListener( 'DOMContentLoaded', initModals );
	} else {
		initModals();
	}

	function initModals() {
		// Instantiate every .modal element so trigger clicks (or programmatic
		// bootstrap.Modal.getOrCreateInstance(el).show()) work as expected.
		var modalList = document.querySelectorAll( '.modal' );
		modalList.forEach( function ( modalEl ) {
			if ( typeof bootstrap !== 'undefined' && bootstrap.Modal ) {
				bootstrap.Modal.getOrCreateInstance( modalEl );
			}
		} );
	}
}() );
