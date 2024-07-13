<?php

namespace MediaWiki\Extension\BootstrapComponents;

use Bootstrap\BootstrapManager;
use Config;
use MediaWiki\Extension\BootstrapComponents\Hooks\OutputPageParserOutput;
use MediaWiki\Extension\BootstrapComponents\Hooks\ParserFirstCallInit;
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
	GalleryGetModesHook,
	ImageBeforeProduceHTMLHook,
	InternalParseBeforeLinksHook,
	OutputPageParserOutputHook,
	ParserAfterParseHook,
	ParserFirstCallInitHook,
	SetupAfterCacheHook
{
	/**
	 * @var Config
	 */
	var Config $config;

	/**
	 * @var BootstrapComponentsService
	 */
	var BootstrapComponentsService $bootstrapComponentsService;

	/**
	 * @var ComponentLibrary
	 */
	var ComponentLibrary $componentLibrary;

	/**
	 * @var NestingController
	 */
	var NestingController $nestingController;

	public function __construct(
		BootstrapComponentsService $bootstrapComponentsService, ComponentLibrary $componentLibrary,
		NestingController $nestingController
	) {
		$this->bootstrapComponentsService = $bootstrapComponentsService;
		$this->componentLibrary = $componentLibrary;
		$this->nestingController = $nestingController;
	}

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
			$extraLibraries['mw.bootstrap'] = LuaLibrary::class;
		}

		return true;
	}

	/**
	 * Hook: GalleryGetModes
	 *
	 * Allows extensions to add classes that can render different modes of a gallery.
	 *
	 * @see https://www.mediawiki.org/wiki/Manual:Hooks/GalleryGetModes
	 *
	 * @param array $modeArray
	 * @return bool
	 */

	public function onGalleryGetModes( &$modeArray ): bool
	{
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
	 * @param \DummyLinker $linker
	 * @param \Title $title
	 * @param File|\LocalFile $file
	 * @param array $frameParams
	 * @param array $handlerParams
	 * @param bool|string $time
	 * @param null|string $res
	 * @param Parser $parser
	 * @param string $query
	 * @param null|int $widthOption
	 * @throws \MWException
	 */
	public function onImageBeforeProduceHTML(
		$linker, &$title, &$file, &$frameParams, &$handlerParams, &$time, &$res, $parser, &$query, &$widthOption
	): bool {
		if ( $this->getConfig()->has( 'BootstrapComponentsModalReplaceImageTag' ) &&
			$this->getConfig()->get( 'BootstrapComponentsModalReplaceImageTag' ) )
		{
			$imageModal = new ImageModal( $linker, $title, $file,
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
	 * @param string $text
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

	public function onOutputPageParserOutput( $outputPage, $parserOutput ): void {
		// @todo check, if we need to omit execution on actions edit, submit, or history
		// $action = $outputPage->parserOptions()->getUser()->getRequest()->getVal( "action" );
		$hook =
			new OutputPageParserOutput( $outputPage, $parserOutput, $this->getBootstrapComponentsService() );

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
	 * @param string $text
	 * @param StripState $stripState
	 * @return bool
	 */
	public function onParserAfterParse( $parser, &$text, $stripState ): bool {

		// once, this was only loaded, when a component was paced on the page. now, we load it always
		// to keep the layout of all the wiki pages consistent.
		$parser->getOutput()->addModuleStyles( ['ext.bootstrapComponents.bootstrap.fix'] );
		$parser->getOutput()->addModuleStyles( ['ext.bootstrap.styles'] );
		$parser->getOutput()->addModules( ['ext.bootstrap.scripts'] );
		$skin = $this->getBootstrapComponentsService()->getNameOfActiveSkin();
		foreach ( $this->getBootstrapComponentsService()->getActiveComponents() as $activeComponent ) {
			if ( !$this->getComponentLibrary()->isRegistered( $activeComponent ) ) {
				continue;
			}
			foreach ( $this->getComponentLibrary()->getModulesFor( $activeComponent ) as $module ) {
				$parser->getOutput()->addModuleStyles( $module, $skin );
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
	 * @throws \MWException
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
			$this->config = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig('BootstrapComponents');
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
