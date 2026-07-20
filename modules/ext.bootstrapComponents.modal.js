/**
 * Contains javascript code executed when modals are used.
 */

( function () {
	'use strict';

	mw.libs.bootstrapComponents.whenReady( 'Modal', function ( Modal, root ) {
		root.querySelectorAll( '.modal' ).forEach( function ( element ) {
			Modal.getOrCreateInstance( element );
		} );
	} );
}() );
