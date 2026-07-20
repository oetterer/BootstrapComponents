( function () {
	'use strict';

	// Resolve a Bootstrap component class by name: from the global that Extension:Bootstrap and most
	// Bootstrap skins expose, or from the jQuery plugin bridge that some skins register instead (a
	// plugin's Constructor is the native component class). Bootstrap registers each plugin under the
	// component's lowercased name.
	function resolve( name ) {
		if ( window.bootstrap && window.bootstrap[ name ] ) {
			return window.bootstrap[ name ];
		}

		const $ = window.jQuery;
		const plugin = name.toLowerCase();
		if ( $ && $.fn[ plugin ] && $.fn[ plugin ].Constructor ) {
			return $.fn[ plugin ].Constructor;
		}

		return null;
	}

	// Run initialise( Component, root ) once the named Bootstrap component is available, on the
	// initial content and again whenever content is re-rendered (VisualEditor, live preview). On a
	// skin that provides its own Bootstrap nothing orders it before this code, so retry briefly.
	function whenReady( name, initialise ) {
		mw.hook( 'wikipage.content' ).add( function ( $content ) {
			let attempts = 0;

			( function attempt() {
				const Component = resolve( name );
				if ( Component ) {
					initialise( Component, ( $content && $content[ 0 ] ) || document );
					return;
				}

				if ( attempts++ < 20 ) {
					setTimeout( attempt, 50 );
					return;
				}

				mw.log.warn( 'BootstrapComponents: bootstrap.' + name + ' is not available.' );
			}() );
		} );
	}

	mw.libs = mw.libs || {};
	mw.libs.bootstrapComponents = { whenReady: whenReady };
}() );
