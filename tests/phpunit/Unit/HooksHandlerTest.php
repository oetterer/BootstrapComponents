<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
use MediaWiki\Extension\BootstrapComponents\ComponentLibrary;
use MediaWiki\Extension\BootstrapComponents\HooksHandler;
use MediaWiki\Extension\BootstrapComponents\NestingController;
use MediaWiki\Output\OutputPage;
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

	/**
	 * this hook is tested in
	 * @see OutputPageParserOutputTest::testHookOutputPageParserOutput
	 *
	 * @return void
	 */
	public function testOnOutputPageParserOutput() {
		$this->assertTrue( true );
	}

	public function testOnBeforePageDisplayLoadsBootstrapForNonProviderSkinWithParsedContent() {
		$outputPage = $this->createMock( OutputPage::class );
		$outputPage->method( 'getModuleStyles' )->willReturn( [ 'ext.bootstrapComponents.bootstrap.fix' ] );
		$outputPage->expects( $this->once() )
			->method( 'addModuleStyles' )
			->with( [ 'ext.bootstrap.styles' ] );
		$outputPage->expects( $this->once() )
			->method( 'addModules' )
			->with( [ 'ext.bootstrap.scripts' ] );

		$this->newHooksHandler( false )->onBeforePageDisplay( $outputPage, $this->newSkin( 'vector' ) );
	}

	public function testOnBeforePageDisplaySkipsSkinThatProvidesBootstrap() {
		$outputPage = $this->createMock( OutputPage::class );
		$outputPage->expects( $this->never() )->method( 'addModuleStyles' );
		$outputPage->expects( $this->never() )->method( 'addModules' );

		$this->newHooksHandler( true )->onBeforePageDisplay( $outputPage, $this->newSkin( 'medik' ) );
	}

	public function testOnBeforePageDisplaySkipsPageThatParsedNoContent() {
		$outputPage = $this->createMock( OutputPage::class );
		$outputPage->method( 'getModuleStyles' )->willReturn( [] );
		$outputPage->expects( $this->never() )->method( 'addModuleStyles' );
		$outputPage->expects( $this->never() )->method( 'addModules' );

		$this->newHooksHandler( false )->onBeforePageDisplay( $outputPage, $this->newSkin( 'vector' ) );
	}

	private function newHooksHandler( bool $skinProvidesBootstrap ): HooksHandler {
		$service = $this->createMock( BootstrapComponentsService::class );
		$service->method( 'skinProvidesBootstrap' )->willReturn( $skinProvidesBootstrap );

		return new HooksHandler(
			$service,
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
