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

module.exports = {
	getComponentClass: getComponentClass
};
