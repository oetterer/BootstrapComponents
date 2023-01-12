<?php

namespace BootstrapComponents\Hooks;

use Bootstrap\BootstrapManager;
use MediaWiki\Hook\ParserAfterParseHook;
use MediaWiki\Hook\SetupAfterCacheHook;
use Parser;
use StripState;

/**
 * Class DefaultHooksHandler
 *
 * Implements HookHandler for hooks
 * - SetupAfterCache
 *      Called in Setup.php, after cache objects are set
 * - ParserBeforeInternalParse
 *      Replaces the normal processing of stripped wiki text with custom processing
 *
 * @see https://doc.wikimedia.org/mediawiki-core/master/php/md_docs_Hooks.html
 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SetupAfterCache
 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserBeforeInternalParse
 *
 * @since 5.0
 */
class DefaultHooksHandler implements SetupAfterCacheHook, ParserAfterParseHook
{
	public function __construct() {}

	/**
	 * Hook: ScribuntoExternalLibraries
	 *
	 * Allow extensions to add Scribunto libraries
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ScribuntoExternalLibraries
	 */
	public static function onScribuntoExternalLibraries( $engine, array &$extraLibraries ): bool
	{
		if ( $engine == 'lua' ) {
			$extraLibraries['mw.bootstrap'] = 'BootstrapComponents\\LuaLibrary';
		}

		return true;
	}

	/**
	 * Hook: SetupAfterCache
	 *
	 * Called in Setup.php, after cache objects are set
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SetupAfterCache
	 */
	public function onSetupAfterCache(): bool
	{
		BootstrapManager::getInstance()->addAllBootstrapModules();
		return true;
	}

	/**
	 * Hook: ParserAfterParse
	 *
	 * Called from Parser::parse() just after the call to Parser::internalParse() returns.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserAfterParse
	 *
	 * @param Parser $parser
	 * @param string $text
	 * @param StripState $stripState
	 * @return bool
	 */
	public function onParserAfterParse($parser, &$text, $stripState): bool
	{
		$parser->getOutput()->addModuleStyles( ['ext.bootstrap.styles'] );
		$parser->getOutput()->addModules( ['ext.bootstrap.scripts'] );
		return true;
	}
}
