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
use Closure;
use Config;
use ConfigException;
use Hooks;
use MagicWord;
use MediaWiki\HookContainer\HookContainer;
use MediaWiki\MediaWikiServices;
use MWException;
use OutputPage;
use Parser;
use ParserOutput;
use StripState;

/**
 * Class HookRegistry
 *
 * Registers all hooks and components for Extension BootstrapComponents.
 *
 * Information on how to add a new hook
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
		'GalleryGetModes',
		'ImageBeforeProduceHTML',
		'InternalParseBeforeLinks',
		'OutputPageParserOutput',
		'ParserFirstCallInit',
	];
	// dev note: for modals, please see \BootstrapComponents\ModalBuilder for a list of tested hooks

	/**
	 * @var ComponentLibrary $componentLibrary
	 */
	private $componentLibrary;

	/**
	 * @var Config $myConfig
	 */
	private $myConfig;

	/**
	 * @var NestingController $nestingController
	 */
	private $nestingController;

	/**
	 * HookRegistry constructor.
	 *
	 * @throws ConfigException cascading {@see HookRegistry::getHooksToRegister}
	 * @throws MWException cascading {@see HookRegistry::getHooksToRegister}
	 *
	 */
	public function __construct() {

		$this->myConfig = $this->registerMyConfiguration();

		list ( $this->componentLibrary, $this->nestingController ) =
			$this->initializeApplications( $this->myConfig );
	}

	/**
	 * @param array $hooksToRegister
	 *
	 * @return array
	 */
	public function buildHookCallbackListFor( array $hooksToRegister ): array
	{
		$hookCallbackList = [];
		$completeHookDefinitionList =
			$this->getCompleteHookDefinitionList( $this->myConfig, $this->componentLibrary,
				$this->nestingController );
		foreach ( $hooksToRegister as $requestedHook ) {
			if ( isset( $completeHookDefinitionList[$requestedHook] ) ) {
				$hookCallbackList[$requestedHook] = $completeHookDefinitionList[$requestedHook];
			}
		}

		return $hookCallbackList;
	}

	/**
	 * Used to clear registered hooks for integration tests
	 *
	 * @deprecated the use of Hooks::clear() is deprecated, see there
	 * @throws MWException cascading {@see Hooks::clear}
	 */
	public function clear() {
		foreach ( self::AVAILABLE_HOOKS as $name ) {
			Hooks::clear( $name );
		}
	}

	/**
	 * @param Config $myConfig
	 *
	 * @return string[]
	 * @throws ConfigException cascading {@see Config::get}
	 *
	 */
	public function compileRequestedHooksListFor( Config $myConfig ): array
	{
		$requestedHookList = [
			'OutputPageParserOutput',
			'ParserFirstCallInit',
		];
		if ( $myConfig->has( 'BootstrapComponentsEnableCarouselGalleryMode' ) &&
			$myConfig->get( 'BootstrapComponentsEnableCarouselGalleryMode' ) ) {
			$requestedHookList[] = 'GalleryGetModes';
		}
		if ( $myConfig->has( 'BootstrapComponentsModalReplaceImageTag' ) &&
			$myConfig->get( 'BootstrapComponentsModalReplaceImageTag' ) ) {
			$requestedHookList[] = 'ImageBeforeProduceHTML';
			$requestedHookList[] = 'InternalParseBeforeLinks';
		}

		return $requestedHookList;
	}

	/**
	 * @param Config $myConfig
	 * @param ComponentLibrary $componentLibrary
	 * @param NestingController $nestingController
	 *
	 * @return Closure[]
	 */
	public function getCompleteHookDefinitionList(
		Config $myConfig, ComponentLibrary $componentLibrary, NestingController $nestingController
	): array
	{
		return [
			/**
			 * Hook: GalleryGetModes
			 *
			 * Allows extensions to add classes that can render different modes of a gallery.
			 *
			 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GalleryGetModes
			 */
			'GalleryGetModes' => function ( &$modeArray ) {
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
			'ImageBeforeProduceHTML' => $this->createImageBeforeProduceHTMLCallback( $nestingController,
				$myConfig ),

			/**
			 * Hook: InternalParseBeforeLinks
			 *
			 * Used to process the expanded wiki code after <nowiki>, HTML-comments, and templates have been treated.
			 *
			 * @see https://www.mediawiki.org/wiki/Manual:Hooks/InternalParseBeforeLinks
			 */
			'InternalParseBeforeLinks' => $this->createInternalParseBeforeLinksCallback(),

			/**
			 * Hook: OutputPageParserOutput
			 *
			 * Called after parse, before the HTML is added to the output.
			 *
			 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
			 */
			'OutputPageParserOutput' => function (
				OutputPage &$outputPage, ParserOutput $parserOutput,
				ParserOutputHelper &$parserOutputHelper = null
			) {
				// @todo check, if we need to omit execution on actions edit, submit, or history
				// $action = $outputPage->parserOptions()->getUser()->getRequest()->getVal( "action" );
				$hook =
					new OutputPageParserOutput( $outputPage, $parserOutput, $parserOutputHelper );

				return $hook->process();
			},

			/**
			 * Hook: ParserFirstCallInit
			 *
			 * Called when the parser initializes for the first time.
			 *
			 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
			 */
			'ParserFirstCallInit' => function ( Parser $parser ) use (
				$componentLibrary, $nestingController
			) {
				$hook = new ParserFirstCallInit( $parser, $componentLibrary, $nestingController );

				return $hook->process();
			},
		];
	}

	/**
	 * @param Config $myConfig
	 *
	 * @return array
	 * @throws ConfigException cascading {@see Config::get}
	 *
	 * @throws MWException cascading {@see ApplicationFactory} calls
	 */
	public function initializeApplications(Config $myConfig ): array
	{
		$applicationFactory = ApplicationFactory::getInstance();
		$componentLibrary =
			$applicationFactory->getComponentLibrary( $myConfig->get( 'BootstrapComponentsWhitelist' ) );
		$nestingController = $applicationFactory->getNestingController();

		return [ $componentLibrary, $nestingController ];
	}

	/**
	 * @param string $hook
	 *
	 * @return boolean
	 */
	public function isRegistered(string $hook ): bool
	{
		return MediaWikiServices::getInstance()->getHookContainer()->isRegistered( $hook );
	}

	/**
	 * Registers all supplied hooks.
	 *
	 * @param array $hookList $hook => $callback
	 *
	 * @return int  number of registered hooks
	 */
	public function register(array $hookList ): int
	{
		foreach ( $hookList as $hook => $callback ) {
			MediaWikiServices::getInstance()->getHookContainer()->register( $hook, $callback );
		}

		return count( $hookList );
	}

	/**
	 * Executes the setup process.
	 *
	 * @return int
	 * @throws ConfigException
	 *
	 */
	public function run(): int
	{
		$requestedHooks = $this->compileRequestedHooksListFor( $this->myConfig );
		$hookCallbackList = $this->buildHookCallbackListFor( $requestedHooks );

		return $this->register( $hookCallbackList );
	}

	/**
	 * Callback for Hook: ImageBeforeProduceHTML
	 *
	 * Called before producing the HTML created by a wiki image insertion
	 *
	 * @param NestingController $nestingController
	 * @param Config $myConfig
	 *
	 * @return Closure
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ImageBeforeProduceHTML
	 *
	 */
	private function createImageBeforeProduceHTMLCallback(
		NestingController $nestingController, Config $myConfig
	): Closure
	{
		return function (
			&$dummy, &$title, &$file, &$frameParams, &$handlerParams, &$time, &$res
		) use ( $nestingController, $myConfig ) {

			$imageModal = new ImageModal( $dummy, $title, $file, $nestingController );

			if ( $myConfig->has( 'BootstrapComponentsDisableSourceLinkOnImageModal' ) &&
				$myConfig->get( 'BootstrapComponentsDisableSourceLinkOnImageModal' ) ) {
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
	 * @return Closure
	 */
	private function createInternalParseBeforeLinksCallback(): Closure
	{
		return function ( Parser &$parser, &$text ) {
			$mw = MediaWikiServices::getInstance()->getMagicWordFactory()->get( 'BSC_NO_IMAGE_MODAL' );
			// we do not use our ParserOutputHelper class here, for we would need to reset it in integration tests.
			// resetting our factory build classes is unfortunately a little skittish
			$parser->getOutput()
				->setExtensionData( 'bsc_no_image_modal', $mw->matchAndRemove( $text ) );

			return true;
		};
	}

	/**
	 * Registers and returns my own configuration, so that it is present during pre-init onExtensionLoad(). See phabricator issue T184837
	 *
	 * @see https://phabricator.wikimedia.org/T184837
	 *
	 * @return Config
	 */
	private function registerMyConfiguration(): Config
	{
		$configFactory = MediaWikiServices::getInstance()->getConfigFactory();
		$configFactory->register( 'BootstrapComponents', 'GlobalVarConfig::newInstance' );

		return $configFactory->makeConfig( 'BootstrapComponents' );
	}
}
