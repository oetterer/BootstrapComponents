<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
use MediaWiki\Extension\BootstrapComponents\ComponentLibrary;
use MediaWiki\Extension\BootstrapComponents\HooksHandler;
use MediaWiki\Extension\BootstrapComponents\NestingController;
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
}
