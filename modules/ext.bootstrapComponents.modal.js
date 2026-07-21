/**
 * Contains javascript code executed when modals are used.
 */

( function () {
	'use strict';

	const { getComponentClass } = require( 'ext.bootstrapComponents.bootstrap' );

	// Wait for DOM to be ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initModals );
	} else {
		initModals();
	}

	function initModals() {
		const Modal = getComponentClass( 'Modal' );
		if ( !Modal ) {
			// eslint-disable-next-line no-console
			console.warn( 'BootstrapComponents: bootstrap.Modal is not available; modal triggers will not work.' );
			return;
		}
		// Instantiate every .modal element so trigger clicks (or programmatic
		// bootstrap.Modal.getOrCreateInstance(el).show()) work as expected.
		const modalList = document.querySelectorAll( '.modal' );
		modalList.forEach( function ( modalEl ) {
			Modal.getOrCreateInstance( modalEl );
		} );
	}
}() );
