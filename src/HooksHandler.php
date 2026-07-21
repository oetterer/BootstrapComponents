<?php

namespace MediaWiki\Extension\BootstrapComponents;

use Bootstrap\BootstrapManager;
use MediaWiki\Config\Config;
use MediaWiki\Extension\BootstrapComponents\Hooks\OutputPageParserOutput;
use MediaWiki\Extension\BootstrapComponents\Hooks\ParserFirstCallInit;
use MediaWiki\Hook\BeforePageDisplayHook;
use MediaWiki\Hook\GalleryGetModesHook;
use MediaWiki\Hook\ImageBeforeProduceHTMLHook;
use MediaWiki\Hook\InternalParseBeforeLinksHook;
use MediaWiki\Hook\OutputPageParserOutputHook;
use MediaWiki\Hook\ParserAfterParseHook;
use MediaWiki\Hook\ParserFirstCallInitHook;
use MediaWiki\Hook\SetupAfterCacheHook;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\Parser;
use SMW\Utils\File;
use StripState;

/**
 * Class HooksHandler
 *
 * Implements HookHandler for hooks
 * - SetupAfterCache
 *      Called in Setup.php, after cache objects are set
 * - ParserBeforeInternalParse
 *      Replaces the normal processing of stripped wiki text with custom processing
 *
 * @see https://doc.wikimedia.org/mediawiki-core/master/php/md_docs_Hooks.html
 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ScribuntoExternalLibraries
 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GalleryGetModes
 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserAfterParse
 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserBeforeInternalParse
 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SetupAfterCache
 *
 * @since 5.0
 */
class HooksHandler implements
	BeforePageDisplayHook,
	GalleryGetModesHook,
	ImageBeforeProduceHTMLHook,
	InternalParseBeforeLinksHook,
	OutputPageParserOutputHook,
	ParserAfterParseHook,
	ParserFirstCallInitHook,
	SetupAfterCacheHook
{
	/**
	 * The styles fix module added on every parsed page. onBeforePageDisplay reads it as the
	 * marker that the page rendered parsed content.
	 */
	private const BOOTSTRAP_FIX_MODULE = 'ext.bootstrapComponents.bootstrap.fix';

	private Config $config;

	public function __construct(
		private readonly BootstrapComponentsService $bootstrapComponentsService,
		private readonly ComponentLibrary $componentLibrary,
		private readonly NestingController $nestingController,
	) {
	}

	/**
	 * Hook: ScribuntoExternalLibraries
	 *
	 * Allow extensions to add Scribunto libraries
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ScribuntoExternalLibraries
	 */
	public static function onScribuntoExternalLibraries( $engine, array &$extraLibraries ): bool {
		if ( $engine == 'lua' ) {
			$extraLibraries['mw.bootstrap'] = LuaLibrary::class;
		}

		return true;
	}

	/**
	 * Hook: BeforePageDisplay
	 *
	 * Loads Bootstrap for the active skin: Extension:Bootstrap's stylesheet and script are each
	 * added only when the skin does not put its own on the page, so a single copy of each is on
	 * the page, and only where the page rendered parsed content, the same scope in which
	 * onParserAfterParse adds the styles fix. Component initialization waits on whichever module
	 * carries this skin's Bootstrap, the modules are named in a config variable set on every page:
	 * content can also arrive after page display (live preview on an edit page), and the init
	 * scripts loaded with it read the variable and fetch the named module on demand.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/BeforePageDisplay
	 */
	public function onBeforePageDisplay( $out, $skin ): void {
		$skinName = $skin->getSkinName();
		$service = $this->getBootstrapComponentsService();

		$out->addJsConfigVars( 'wgBootstrapComponentsBootstrapModules', [
			'scripts' => $service->getBootstrapScriptsModule( $skinName ),
			'styles' => $service->getBootstrapStylesModule( $skinName ),
		] );

		if ( !in_array( self::BOOTSTRAP_FIX_MODULE, $out->getModuleStyles(), true ) ) {
			return;
		}

		if ( !$service->skinProvidesBootstrapStyles( $skinName ) ) {
			$out->addModuleStyles( [ 'ext.bootstrap.styles' ] );
		}
		if ( !$service->skinProvidesBootstrapScripts( $skinName ) ) {
			$out->addModules( [ 'ext.bootstrap.scripts' ] );
		}
	}

	/**
	 * Hook: GalleryGetModes
	 *
	 * Allows extensions to add classes that can render different modes of a gallery.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GalleryGetModes
	 *
	 * @codeCoverageIgnore trivial
	 *
	 * @param array &$modeArray
	 * @return bool
	 */

	public function onGalleryGetModes( &$modeArray ): bool {
		if (
			$this->getConfig()->has( 'BootstrapComponentsEnableCarouselGalleryMode' )
				&& $this->getConfig()->get( 'BootstrapComponentsEnableCarouselGalleryMode' )
		) {
			$modeArray['carousel'] = CarouselGallery::class;
		}
		return true;
	}

	/**
	 * Hook: ImageBeforeProduceHTML
	 *
	 * Called before producing the HTML created by a wiki image insertion
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ImageBeforeProduceHTML
	 *
	 * @codeCoverageIgnore trivial
	 *
	 * @param $linker always null (was \DummyLinker $linker)
	 * @param \Title &$title
	 * @param File|\LocalFile &$file
	 * @param array &$frameParams
	 * @param array &$handlerParams
	 * @param bool|string &$time
	 * @param null|string &$res
	 * @param Parser $parser
	 * @param string &$query
	 * @param null|int &$widthOption
	 * @throws RuntimeException
	 */
	public function onImageBeforeProduceHTML(
		$linker, &$title, &$file, &$frameParams, &$handlerParams, &$time, &$res, $parser, &$query, &$widthOption
	): bool {
		if ( $this->getConfig()->has( 'BootstrapComponentsModalReplaceImageTag' ) &&
			$this->getConfig()->get( 'BootstrapComponentsModalReplaceImageTag' ) ) {
			if ( !$file ) {
				return true;
			}
			$imageModal = new ImageModal(
				$title, $file,
				$this->getNestingController(), $this->getBootstrapComponentsService()
			);

			if ( $this->getConfig()->has( 'BootstrapComponentsDisableSourceLinkOnImageModal' ) &&
				$this->getConfig()->get( 'BootstrapComponentsDisableSourceLinkOnImageModal' ) ) {
				$imageModal->disableSourceLink();
			}

			return $imageModal->parse( $frameParams, $handlerParams, $time, $res );
		}
		return true;
	}

	/**
	 * Hook: InternalParseBeforeLinks
	 *
	 * Used to process the expanded wiki code after <nowiki>, HTML-comments, and templates have been treated.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/InternalParseBeforeLinks
	 *
	 * @codeCoverageIgnore trivial
	 *
	 * @param Parser $parser
	 * @param string &$text
	 * @param StripState $stripState
	 * @return bool
	 */
	public function onInternalParseBeforeLinks( $parser, &$text, $stripState ): bool {
		$this->getBootstrapComponentsService()->setModalsSuppressedByMagicWord(
			MediaWikiServices::getInstance()
				->getMagicWordFactory()->get( 'BSC_NO_IMAGE_MODAL' )->matchAndRemove( $text )
		);
		return true;
	}

	/**
	 * Hook: OutputPageParserOutput
	 *
	 * Called after parse, before the HTML is added to the output.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
	 *
	 * @codeCoverageIgnore trivial
	 *
	 * @param \OutputPage $outputPage
	 * @param \ParserOutput $parserOutput
	 * @return void
	 */
	public function onOutputPageParserOutput( $outputPage, $parserOutput ): void {
		// @todo check, if we need to omit execution on actions edit, submit, or history
		// $action = $outputPage->parserOptions()->getUser()->getRequest()->getVal( "action" );
		$hook = new OutputPageParserOutput( $outputPage, $this->getBootstrapComponentsService() );

		$hook->process();
	}

	/**
	 * Hook: ParserAfterParse
	 *
	 * Called from Parser::parse() just after the call to Parser::internalParse() returns.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserAfterParse
	 *
	 * @codeCoverageIgnore trivial
	 *
	 * @param Parser $parser
	 * @param string &$text
	 * @param StripState $stripState
	 * @return bool
	 */
	public function onParserAfterParse( $parser, &$text, $stripState ): bool {
		// Always add the styles fix on parsed pages; it doubles as the marker onBeforePageDisplay
		// reads to load Bootstrap for the active skin (which the shared, skin agnostic parser
		// cache cannot decide).
		$parser->getOutput()->addModuleStyles( [ self::BOOTSTRAP_FIX_MODULE ] );
		$skin = $this->getBootstrapComponentsService()->getNameOfActiveSkin();
		foreach ( $this->getBootstrapComponentsService()->getActiveComponents() as $activeComponent ) {
			if ( !$this->getComponentLibrary()->isRegistered( $activeComponent ) ) {
				continue;
			}
			foreach ( $this->getComponentLibrary()->getModulesFor( $activeComponent, 'vector' ) as $module ) {
				$parser->getOutput()->addModuleStyles( [ $module ] );
				$parser->getOutput()->addModules( [ $module ] );
			}
		}
		return true;
	}

	/**
	 * Hook: ParserFirstCallInit
	 *
	 * Called when the parser initializes for the first time.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
	 *
	 * @param Parser $parser
	 *
	 * @return bool
	 * @throws RuntimeException
	 */
	public function onParserFirstCallInit( $parser ): bool {
		$hook = new ParserFirstCallInit( $parser, $this->getComponentLibrary(), $this->getNestingController() );

		return $hook->process();
	}

	/**
	 * Hook: SetupAfterCache
	 *
	 * Called in Setup.php, after cache objects are set
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/SetupAfterCache
	 *
	 * @codeCoverageIgnore trivial
	 */
	public function onSetupAfterCache(): bool {
		// think about only adding modules for whitelisted components instead of all
		BootstrapManager::getInstance()->addAllBootstrapModules();
		return true;
	}

	/**
	 * @return BootstrapComponentsService
	 */
	protected function getBootstrapComponentsService(): BootstrapComponentsService {
		return $this->bootstrapComponentsService;
	}

	/**
	 * @return ComponentLibrary
	 */
	protected function getComponentLibrary(): ComponentLibrary {
		return $this->componentLibrary;
	}

	/**
	 * @return Config
	 */
	protected function getConfig(): Config {
		if ( !isset( $this->config ) ) {
			$this->config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'BootstrapComponents' );
		}
		return $this->config;
	}

	/**
	 * @return NestingController
	 */
	protected function getNestingController(): NestingController {
		return $this->nestingController;
	}
}
