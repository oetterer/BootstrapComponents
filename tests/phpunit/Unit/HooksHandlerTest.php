<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
use MediaWiki\Extension\BootstrapComponents\ComponentLibrary;
use MediaWiki\Extension\BootstrapComponents\HooksHandler;
use MediaWiki\Extension\BootstrapComponents\NestingController;
use Parser;
use ParserOutput;
use PHPUnit\Framework\TestCase;

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

	public function testOnParserAfterParseLoadsActiveComponentScriptsAndStyles() {
		$loadedStyles = [];
		$loadedModules = [];

		$parserOutput = $this->createMock( ParserOutput::class );
		$parserOutput->method( 'addModuleStyles' )
			->willReturnCallback( function ( $m ) use ( &$loadedStyles ) {
				$loadedStyles = array_merge( $loadedStyles, (array)$m );
			} );
		$parserOutput->method( 'addModules' )
			->willReturnCallback( function ( $m ) use ( &$loadedModules ) {
				$loadedModules = array_merge( $loadedModules, (array)$m );
			} );

		$parser = $this->createMock( Parser::class );
		$parser->method( 'getOutput' )->willReturn( $parserOutput );

		$service = $this->createMock( BootstrapComponentsService::class );
		$service->method( 'getNameOfActiveSkin' )->willReturn( 'vector' );
		$service->method( 'getActiveComponents' )->willReturn( [ 'tooltip', 'popover' ] );

		$library = $this->createMock( ComponentLibrary::class );
		$library->method( 'isRegistered' )->willReturn( true );
		$library->method( 'getModulesFor' )
			->willReturnCallback( function ( $name ) {
				return [ 'ext.bootstrapComponents.' . $name . '.fix' ];
			} );

		$handler = new HooksHandler(
			$service,
			$library,
			$this->createMock( NestingController::class )
		);

		$text = '';
		$handler->onParserAfterParse( $parser, $text, null );

		// Per-component fix modules must reach BOTH addModuleStyles and addModules
		// (style-only modules treat addModules as a no-op; modules with a scripts
		// entry get their JS init via that call).
		$this->assertContains( 'ext.bootstrapComponents.tooltip.fix', $loadedStyles );
		$this->assertContains( 'ext.bootstrapComponents.popover.fix', $loadedStyles );
		$this->assertContains( 'ext.bootstrapComponents.tooltip.fix', $loadedModules );
		$this->assertContains( 'ext.bootstrapComponents.popover.fix', $loadedModules );
	}
}
