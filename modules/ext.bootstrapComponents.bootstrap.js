'use strict';

// Resolve a Bootstrap component class by name: from the window.bootstrap global that
// Extension:Bootstrap and most Bootstrap-bundling skins expose, or from the jQuery plugin
// bridge that some skins (Tweeki) register instead, where a plugin's Constructor is the
// native component class.
function getComponentClass( name ) {
	if ( window.bootstrap && window.bootstrap[ name ] ) {
		return window.bootstrap[ name ];
	}
	const plugin = window.jQuery && window.jQuery.fn[ name.toLowerCase() ];
	return ( plugin && plugin.Constructor ) || null;
}

// Defers component initialization until the module carrying the active skin's Bootstrap has
// loaded. A static ResourceLoader dependency cannot express this, since the module depends on
// the skin. The modules are provided in a config variable instead. The callback runs for the
// initial page content and again for every re-render (VisualEditor, live preview).
function whenReady( callback ) {
	const bootstrapModules = mw.config.get( 'wgBootstrapComponentsBootstrapModules' );
	if ( !bootstrapModules ) {
		mw.log.warn( 'BootstrapComponents: no Bootstrap modules were configured for this skin.' );
		return;
	}
	if ( bootstrapModules.styles ) {
		mw.loader.load( bootstrapModules.styles );
	}
	mw.loader.using( bootstrapModules.scripts ).then( function () {
		mw.hook( 'wikipage.content' ).add( function ( $content ) {
			callback( ( $content && $content[ 0 ] ) || document );
		} );
	}, function () {
		mw.log.warn( 'BootstrapComponents: Bootstrap module "' + bootstrapModules.scripts + '" failed to load.' );
	} );
}

module.exports = {
	getComponentClass: getComponentClass,
	whenReady: whenReady
};
