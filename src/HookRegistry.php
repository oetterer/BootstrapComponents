<?php
/**
 * Contains the class registering the needed/wanted hooks.
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

namespace BootstrapComponents;

use BootstrapComponents\Hooks\OutputPageParserOutput;
use BootstrapComponents\Hooks\ParserFirstCallInit;
use Bootstrap\BootstrapManager;
use Hooks;
use MagicWord;
use MediaWiki\MediaWikiServices;
use Parser;

/**
 * Class HookRegistry
 *
 * Registers all hooks and components for Extension BootstrapComponents.
 *
 * Information on how to add an additional hook
 *  1. add it to {@see HookRegistry::AVAILABLE_HOOKS}.
 *  2. add an appropriate entry in the array inside {@see HookRegistry::getCompleteHookDefinitionList}
 *     with the hook as array key and the callback as value.
 *  3. have {@see HookRegistry::compileRequestedHooksListFor} add the hook to its result array. Based on
 *     a certain condition, if necessary.
 *  4. add appropriate tests to {@see \BootstrapComponents\Tests\Unit\HookRegistryTest}.
 *
 * @since 1.0
 */
class HookRegistry {

	/**
	 * @var array
	 */
	const AVAILABLE_HOOKS = [
		'GalleryGetModes', 'ImageBeforeProduceHTML', 'InternalParseBeforeLinks', 'OutputPageParserOutput',
		'ParserAfterParse', 'ParserFirstCallInit', 'ScribuntoExternalLibraries', 'SetupAfterCache',
	];
	// dev note: for modals, please see \BootstrapComponents\ModalBuilder for a list of tested hooks

	/**
	 * @var ComponentLibrary $componentLibrary
	 */
	private $componentLibrary;

	/**
	 * @var \Config $myConfig
	 */
	private $myConfig;

	/**
	 * @var NestingController $nestingController
	 */
	private $nestingController;

	/**
	 * HookRegistry constructor.
	 *
	 * @throws \ConfigException cascading {@see \BootstrapComponents\HookRegistry::getHooksToRegister}
	 * @throws \MWException cascading {@see \BootstrapComponents\HookRegistry::getHooksToRegister}
	 *
	 */
	public function __construct() {

		$this->myConfig = $this->registerMyConfiguration();

		list ( $this->componentLibrary, $this->nestingController ) = $this->initializeApplications( $this->myConfig );
	}

	/**
	 * @param array $hooksToRegister
	 *
	 * @return array
	 */
	public function buildHookCallbackListFor( $hooksToRegister ) {
		$hookCallbackList = [];
		$completeHookDefinitionList = $this->getCompleteHookDefinitionList(
			$this->myConfig, $this->componentLibrary, $this->nestingController
		);
		foreach ( $hooksToRegister as $requestedHook ) {
			if ( isset( $completeHookDefinitionList[$requestedHook] ) ) {
				$hookCallbackList[$requestedHook] = $completeHookDefinitionList[$requestedHook];
			}
		}
		return $hookCallbackList;
	}

	/**
	 * @throws \MWException cascading {@see \Hooks::clear}
	 */
	public function clear() {
		foreach ( self::AVAILABLE_HOOKS as $name ) {
			Hooks::clear( $name );
		}
	}

	/**
	 * @param \Config $myConfig
	 *
	 * @throws \ConfigException cascading {@see \Config::get}
	 *
	 * @return string[]
	 */
	public function compileRequestedHooksListFor( $myConfig ) {
		$requestedHookList = [
			'OutputPageParserOutput', 'ParserAfterParse', 'ParserFirstCallInit',
			'SetupAfterCache', 'ScribuntoExternalLibraries',
		];
		if ( $myConfig->has( 'BootstrapComponentsEnableCarouselGalleryMode' )
			&& $myConfig->get( 'BootstrapComponentsEnableCarouselGalleryMode' )
		) {
			$requestedHookList[] = 'GalleryGetModes';
		}
		if ( $myConfig->has( 'BootstrapComponentsModalReplaceImageTag' )
			&& $myConfig->get( 'BootstrapComponentsModalReplaceImageTag' )
		) {
			$requestedHookList[] = 'ImageBeforeProduceHTML';
			$requestedHookList[] = 'InternalParseBeforeLinks';
		}
		return $requestedHookList;
	}

	/**
	 * @param \Config           $myConfig
	 * @param ComponentLibrary  $componentLibrary
	 * @param NestingController $nestingController
	 *
	 * @return \Closure[]
	 */
	public function getCompleteHookDefinitionList( $myConfig, $componentLibrary, $nestingController ) {
		return [
			/**
			 * Hook: GalleryGetModes
			 *
			 * Allows extensions to add classes that can render different modes of a gallery.
			 *
			 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GalleryGetModes
			 */
			'GalleryGetModes'            => function( &$modeArray ) {
				$modeArray['carousel'] = 'BootstrapComponents\\CarouselGallery';
				return true;
			},

			/**
			 * Hook: ImageBeforeProduceHTML
			 *
			 * Called before producing the HTML created by a wiki image insertion
			 *
			 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ImageBeforeProduceHTML
			 */
			'ImageBeforeProduceHTML'     => $this->createImageBeforeProduceHTMLCallback( $nestingController, $myConfig ),

			/**
			 * Hook: InternalParseBeforeLinks
			 *
			 * Used to process the expanded wiki code after <nowiki>, HTML-comments, and templates have been treated.
			 *
			 * @see https://www.mediawiki.org/wiki/Manual:Hooks/InternalParseBeforeLinks
			 */
			'InternalParseBeforeLinks'   => $this->createInternalParseBeforeLinksCallback(),

			/**
			 * Hook: OutputPageParserOutput
			 *
			 * Called after parse, before the HTML is added to the output.
			 *
			 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
			 */
			'OutputPageParserOutput'     => function( \OutputPage &$outputPage, \ParserOutput $parserOutput, ParserOutputHelper &$parserOutputHelper = null ) {
				// @todo check, if we need to omit execution on actions edit, submit, or history
				// $action = $outputPage->parserOptions()->getUser()->getRequest()->getVal( "action" );
				$hook = new OutputPageParserOutput( $outputPage, $parserOutput, $parserOutputHelper );
				return $hook->process();
			},

			/**
			 * Hook: ParserAfterParse
			 *
			 * Called from Parser::parse() just after the call to Parser::internalParse() returns.
			 *
			 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserAfterParse
			 */
			'ParserAfterParse'           => function( Parser &$parser, &$text, \StripState &$stripState ) {
				#@todo make this conditional, i.e. only include styles and scripts for used components?
				$parser->getOutput()->addModules( 'ext.bootstrap.styles' );
				$parser->getOutput()->addModules( 'ext.bootstrap.scripts' );
				return true;
			},

			/**
			 * Hook: ParserFirstCallInit
			 *
			 * Called when the parser initializes for the first time.
			 *
			 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
			 */
			'ParserFirstCallInit'        => function( Parser $parser ) use ( $componentLibrary, $nestingController ) {
				$hook = new ParserFirstCallInit( $parser, $componentLibrary, $nestingController );
				return $hook->process();
			},

			/**
			 * Hook: ScribuntoExternalLibraries
			 *
			 * Allow extensions to add Scribunto libraries
			 *
			 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ScribuntoExternalLibraries
			 */
			'ScribuntoExternalLibraries' => function( $engine, &$extraLibraries ) {
				if ( $engine == 'lua' ) {
					$extraLibraries['mw.bootstrap'] = 'BootstrapComponents\\LuaLibrary';
				}
				return true;
			},

			/**
			 * Hook: SetupAfterCache
			 *
			 * Called in Setup.php, after cache objects are set
			 *
			 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SetupAfterCache
			 */
			'SetupAfterCache'            => function() {
				// @todo change 'adding all bootstrap modules' to 'only add used modules' during parse.
				BootstrapManager::getInstance()->addAllBootstrapModules();
				return true;
			},
		];
	}

	/**
	 * @param \Config $myConfig
	 *
	 * @throws \MWException cascading {@see \BootstrapComponents\ApplicationFactory} calls
	 * @throws \ConfigException cascading {@see \Config::get}
	 *
	 * @return array
	 */
	public function initializeApplications( $myConfig ) {
		$applicationFactory = ApplicationFactory::getInstance();
		$componentLibrary = $applicationFactory->getComponentLibrary(
			$myConfig->get( 'BootstrapComponentsWhitelist' )
		);
		$nestingController = $applicationFactory->getNestingController();
		return [ $componentLibrary, $nestingController ];
	}

	/**
	 * @param string $hook
	 *
	 * @return boolean
	 */
	public function isRegistered( $hook ) {
		return Hooks::isRegistered( $hook );
	}

	/**
	 * Registers all supplied hooks.
	 *
	 * @param array $hookList $hook => $callback
	 *
	 * @return int  number of registered hooks
	 */
	public function register( $hookList ) {
		foreach ( $hookList as $hook => $callback ) {
			Hooks::register( $hook, $callback );
		}
		return count( $hookList );
	}

	/**
	 * Executes the setup process.
	 *
	 * @throws \ConfigException
	 *
	 * @return int
	 */
	public function run() {
		$requestedHooks = $this->compileRequestedHooksListFor(
			$this->myConfig
		);
		$hookCallbackList = $this->buildHookCallbackListFor(
			$requestedHooks
		);

		return $this->register( $hookCallbackList );
	}

	/**
	 * Callback for Hook: ImageBeforeProduceHTML
	 *
	 * Called before producing the HTML created by a wiki image insertion
	 *
	 * @param NestingController $nestingController
	 * @param \Config           $myConfig
	 *
	 * @return \Closure
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ImageBeforeProduceHTML
	 *
	 */
	private function createImageBeforeProduceHTMLCallback( $nestingController, $myConfig ) {

		return function( &$dummy, &$title, &$file, &$frameParams, &$handlerParams, &$time, &$res
		) use ( $nestingController, $myConfig ) {

			$imageModal = new ImageModal( $dummy, $title, $file, $nestingController );

			if ( $myConfig->has( 'BootstrapComponentsDisableSourceLinkOnImageModal' )
				&& $myConfig->get( 'BootstrapComponentsDisableSourceLinkOnImageModal' )
			) {
				$imageModal->disableSourceLink();
			}

			return $imageModal->parse( $frameParams, $handlerParams, $time, $res );
		};
	}

	/**
	 * Callback for Hook: InternalParseBeforeLinks
	 *
	 * Used to process the expanded wiki code after <nowiki>, HTML-comments, and templates have been treated.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/InternalParseBeforeLinks
	 *
	 * @return \Closure
	 */
	private function createInternalParseBeforeLinksCallback() {
		return function( Parser &$parser, &$text ) {
			if ( class_exists( '\MediaWiki\MediaWikiServices' )
				&& method_exists( '\MediaWiki\MediaWikiServices', 'getMagicWordFactory' )
			) {
				$mw = MediaWikiServices::getInstance()->getMagicWordFactory()->get( 'BSC_NO_IMAGE_MODAL' );;
			} else {
				$mw = MagicWord::get( 'BSC_NO_IMAGE_MODAL' );
			}
			// we do not use our ParserOutputHelper class here, for we would need to reset it in integration tests.
			// resetting our factory build classes is unfortunately a little skittish
			$parser->getOutput()->setExtensionData(
				'bsc_no_image_modal',
				$mw->matchAndRemove( $text )
			);
			return true;
		};
	}

	/**
	 * Registers and returns my own configuration, so that it is present during pre-init onExtensionLoad(). See phabricator issue T184837
	 *
	 * @see https://phabricator.wikimedia.org/T184837
	 *
	 * @return \Config
	 */
	private function registerMyConfiguration() {
		$configFactory = MediaWikiServices::getInstance()->getConfigFactory();
		$configFactory->register( 'BootstrapComponents', 'GlobalVarConfig::newInstance' );
		return $configFactory->makeConfig( 'BootstrapComponents' );
	}
}
