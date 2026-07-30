<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
use MediaWiki\Extension\BootstrapComponents\ComponentLibrary;
use MediaWiki\Extension\BootstrapComponents\HooksHandler;
use MediaWiki\Extension\BootstrapComponents\NestingController;
use MediaWiki\Extension\BootstrapComponents\Tests\Fixtures\TestConfig;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\ResourceLoader\Module;
use PHPUnit\Framework\TestCase;
use Skin;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\HooksHandler
 *
 * @ingroup Test
 *
 * @group   extension-bootstrap-components
 * @group   mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   5.2
 * @author  Tobias Oetterer
 */
class HooksHandlerTest extends TestCase {


	/**
	 * @throws \ConfigException
	 */
	public function testOnScribuntoExternalLibraries() {
		$libraries = [];
		$this->assertTrue(
			HooksHandler::onScribuntoExternalLibraries( '', $libraries )
		);
		$this->assertEquals(
			[],
			$libraries
		);
		$this->assertTrue(
			HooksHandler::onScribuntoExternalLibraries( 'lua', $libraries )
		);
		$this->assertArrayHasKey(
			'mw.bootstrap',
			$libraries
		);
		$this->assertEquals(
			'MediaWiki\\Extension\\BootstrapComponents\\LuaLibrary',
			$libraries['mw.bootstrap']
		);
	}

	public function testOnGalleryGetModes()	{
		$modes = [];
		$hooksHandler = new HooksHandler(
			$this->createMock( BootstrapComponentsService::class ),
			$this->createMock( ComponentLibrary::class ),
			$this->createMock( NestingController::class ),
		);
		$this->assertTrue(
        	$hooksHandler->onGalleryGetModes( $modes )
		);
		$this->assertTrue( isset( $modes['carousel'] ) );
		$this->assertEquals( 'MediaWiki\\Extension\\BootstrapComponents\\CarouselGallery', $modes['carousel'] );
	}

	public function testOnParserAfterParseAddsOnlyTheStylesFix() {
		$parserOutput = new ParserOutput();
		$parser = $this->createMock( Parser::class );
		$parser->method( 'getOutput' )->willReturn( $parserOutput );
		$text = '';

		$this->newHooksHandler()->onParserAfterParse( $parser, $text, null );

		$this->assertContains( 'ext.bootstrapComponents.bootstrap.fix', $parserOutput->getModuleStyles() );
		$this->assertNotContains( 'ext.bootstrap.styles', $parserOutput->getModuleStyles() );
		$this->assertNotContains( 'ext.bootstrap.scripts', $parserOutput->getModuleStyles() );
		$this->assertNotContains( 'ext.bootstrap.styles', $parserOutput->getModules() );
		$this->assertNotContains( 'ext.bootstrap.scripts', $parserOutput->getModules() );
	}

	public function testOnParserAfterParseKeepsGeneralModulesOutOfTheStylesQueue() {
		$parserOutput = $this->parserOutputAfterParsingWith( 'modal' );

		$this->assertNotEmpty( $parserOutput->getModules(), 'the active component contributed no modules' );
		$this->assertSame( [], $this->modulesOfType( $parserOutput->getModuleStyles(), Module::LOAD_GENERAL ) );
	}

	public function testOnParserAfterParseKeepsStylesOnlyModulesOutOfTheGeneralQueue() {
		$parserOutput = $this->parserOutputAfterParsingWith( 'modal' );

		$this->assertNotEmpty( $parserOutput->getModules(), 'the active component contributed no modules' );
		$this->assertSame( [], $this->modulesOfType( $parserOutput->getModules(), Module::LOAD_STYLES ) );
	}

	private function parserOutputAfterParsingWith( string $componentName ): ParserOutput {
		$bootstrapComponentsService = new BootstrapComponentsService( new TestConfig() );
		$bootstrapComponentsService->registerComponentAsActive( $componentName );
		$parserOutput = new ParserOutput();
		$parser = $this->createMock( Parser::class );
		$parser->method( 'getOutput' )->willReturn( $parserOutput );
		$text = '';

		$hooksHandler = new HooksHandler(
			$bootstrapComponentsService,
			new ComponentLibrary(),
			$this->createMock( NestingController::class ),
		);
		$hooksHandler->onParserAfterParse( $parser, $text, null );

		return $parserOutput;
	}

	/**
	 * @param string[] $moduleNames
	 * @return string[]
	 */
	private function modulesOfType( array $moduleNames, string $type ): array {
		$resourceLoader = MediaWikiServices::getInstance()->getResourceLoader();

		return array_values( array_filter(
			$moduleNames,
			static function ( string $moduleName ) use ( $resourceLoader, $type ): bool {
				$module = $resourceLoader->getModule( $moduleName );

				return $module !== null && $module->getType() === $type;
			}
		) );
	}

	public function testOnBeforePageDisplayLoadsExtensionBootstrapOnNonProviderContentPage() {
		$out = $this->createMock( OutputPage::class );
		$out->method( 'getModuleStyles' )->willReturn( [ 'ext.bootstrapComponents.bootstrap.fix' ] );
		$out->expects( $this->once() )->method( 'addJsConfigVars' )
			->with( 'wgBootstrapComponentsBootstrapModules', [ 'scripts' => 'ext.bootstrap.scripts', 'styles' => 'ext.bootstrap.styles' ] );
		$out->expects( $this->once() )->method( 'addModuleStyles' )
			->with( [ 'ext.bootstrap.styles' ] );
		$out->expects( $this->once() )->method( 'addModules' )
			->with( [ 'ext.bootstrap.scripts' ] );

		$this->newHooksHandler()->onBeforePageDisplay( $out, $this->newSkin( 'vector' ) );
	}

	public function testOnBeforePageDisplaySkipsExtensionBootstrapOnSkinThatShipsItsOwn() {
		$out = $this->createMock( OutputPage::class );
		$out->method( 'getModuleStyles' )->willReturn( [ 'ext.bootstrapComponents.bootstrap.fix' ] );
		$out->expects( $this->once() )->method( 'addJsConfigVars' )
			->with( 'wgBootstrapComponentsBootstrapModules', [ 'scripts' => 'skins.medik.js', 'styles' => null ] );
		$out->expects( $this->never() )->method( 'addModuleStyles' );
		$out->expects( $this->never() )->method( 'addModules' );

		$this->newHooksHandler()->onBeforePageDisplay( $out, $this->newSkin( 'medik' ) );
	}

	public function testOnBeforePageDisplaySkipsOnlyTheStylesheetOnChameleon() {
		$out = $this->createMock( OutputPage::class );
		$out->method( 'getModuleStyles' )->willReturn( [ 'ext.bootstrapComponents.bootstrap.fix' ] );
		$out->expects( $this->once() )->method( 'addJsConfigVars' )
			->with( 'wgBootstrapComponentsBootstrapModules', [ 'scripts' => 'ext.bootstrap.scripts', 'styles' => null ] );
		$out->expects( $this->never() )->method( 'addModuleStyles' );
		$out->expects( $this->once() )->method( 'addModules' )
			->with( [ 'ext.bootstrap.scripts' ] );

		$this->newHooksHandler()->onBeforePageDisplay( $out, $this->newSkin( 'chameleon' ) );
	}

	public function testOnBeforePageDisplayOnlySetsTheConfigVariableWhenNoContentWasParsed() {
		$out = $this->createMock( OutputPage::class );
		$out->method( 'getModuleStyles' )->willReturn( [] );
		$out->expects( $this->once() )->method( 'addJsConfigVars' )
			->with( 'wgBootstrapComponentsBootstrapModules', [ 'scripts' => 'ext.bootstrap.scripts', 'styles' => 'ext.bootstrap.styles' ] );
		$out->expects( $this->never() )->method( 'addModules' );
		$out->expects( $this->never() )->method( 'addModuleStyles' );

		$this->newHooksHandler()->onBeforePageDisplay( $out, $this->newSkin( 'vector' ) );
	}

	private function newHooksHandler(): HooksHandler {
		return new HooksHandler(
			new BootstrapComponentsService( new TestConfig() ),
			$this->createMock( ComponentLibrary::class ),
			$this->createMock( NestingController::class ),
		);
	}

	private function newSkin( string $skinName ): Skin {
		$skin = $this->createMock( Skin::class );
		$skin->method( 'getSkinName' )->willReturn( $skinName );

		return $skin;
	}
}
