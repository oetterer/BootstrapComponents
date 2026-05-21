<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit\Hooks;

use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
use MediaWiki\Extension\BootstrapComponents\Hooks\OutputPageParserOutput;
use OutputPage;
use ParserOutput;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\Hooks\OutputPageParserOutput
 *
 * @ingroup Test
 *
 * @group   extension-bootstrap-components
 * @group   mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   1.2
 * @author  Tobias Oetterer
 */
class OutputPageParserOutputTest extends TestCase {

	public function testCanConstruct() {

		$outputPage = $this->createMock( OutputPage::class );

		$instance = new OutputPageParserOutput(
			$outputPage,
			$this->createMock( ParserOutput::class ),
			$this->createMock( BootstrapComponentsService::class )
		);

		$this->assertInstanceOf(
			OutputPageParserOutput::class,
			$instance
		);
	}

	public function testProcessLoadsPerComponentInitModulesAndVectorFix() {
		$loadedModules = [];

		$outputPage = $this->createMock( OutputPage::class );
		$outputPage->expects( $this->never() )->method( 'addHTML' );
		$outputPage->expects( $this->atLeastOnce() )
			->method( 'addModules' )
			->will( $this->returnCallback( function( $modules ) use ( &$loadedModules ) {
				$loadedModules = array_merge( $loadedModules, (array)$modules );
			} ) );

		$bootstrapService = $this->createMock( BootstrapComponentsService::class );
		$bootstrapService->expects( $this->once() )
			->method( 'vectorSkinInUse' )
			->willReturn( true );

		$instance = new OutputPageParserOutput(
			$outputPage,
			$this->createMock( ParserOutput::class ),
			$bootstrapService
		);
		$instance->process();

		$expected = [
			'ext.bootstrapComponents.tooltip.fix',
			'ext.bootstrapComponents.popover.fix',
			'ext.bootstrapComponents.carousel.fix',
			'ext.bootstrapComponents.vector-fix',
		];
		sort( $loadedModules );
		sort( $expected );
		$this->assertEquals( $expected, $loadedModules );
	}

	public function testProcessSkipsVectorFixWhenNotVector() {
		$loadedModules = [];

		$outputPage = $this->createMock( OutputPage::class );
		$outputPage->expects( $this->never() )->method( 'addHTML' );
		$outputPage->expects( $this->atLeastOnce() )
			->method( 'addModules' )
			->will( $this->returnCallback( function( $modules ) use ( &$loadedModules ) {
				$loadedModules = array_merge( $loadedModules, (array)$modules );
			} ) );

		$bootstrapService = $this->createMock( BootstrapComponentsService::class );
		$bootstrapService->expects( $this->once() )
			->method( 'vectorSkinInUse' )
			->willReturn( false );

		$instance = new OutputPageParserOutput(
			$outputPage,
			$this->createMock( ParserOutput::class ),
			$bootstrapService
		);
		$instance->process();

		$this->assertNotContains( 'ext.bootstrapComponents.vector-fix', $loadedModules );
	}
}
