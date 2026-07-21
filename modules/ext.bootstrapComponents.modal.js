/**
 * Contains javascript code executed when modals are used.
 */

( function () {
	'use strict';

	const { getComponentClass, whenReady } = require( 'ext.bootstrapComponents.bootstrap' );

	whenReady( function ( content ) {
		const Modal = getComponentClass( 'Modal' );
		if ( !Modal ) {
			// eslint-disable-next-line no-console
			console.warn( 'BootstrapComponents: bootstrap.Modal is not available; modal triggers will not work.' );
			return;
		}
		// Instantiate every .modal element so trigger clicks (or programmatic
		// bootstrap.Modal.getOrCreateInstance(el).show()) work as expected.
		content.querySelectorAll( '.modal' ).forEach( function ( modalEl ) {
			Modal.getOrCreateInstance( modalEl );
		} );
	} );
}() );
