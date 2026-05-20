<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit\Hooks;

use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
use MediaWiki\Extension\BootstrapComponents\Hooks\OutputPageParserOutput;
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\ParserOutput;
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

	public function testHookOutputPageParserOutputLoadsModules() {
		// Collect every addModules() call so we can assert the union of module sets
		$loadedModules = [];

		$outputPage = $this->createMock( OutputPage::class );
		$outputPage->expects( $this->atLeastOnce() )
			->method( 'addModules' )
			->will( $this->returnCallback( function( $modules ) use ( &$loadedModules ) {
				$loadedModules = array_merge( $loadedModules, (array)$modules );
			} ) );

		// addHTML should NOT be called any more — the deferred-content injection
		// pattern was replaced by inline emission in ModalBuilder::parse().
		$outputPage->expects( $this->never() )->method( 'addHTML' );

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

		// Bootstrap library JS, BC's per-component JS init modules, and the
		// Vector-fix module (because vectorSkinInUse returns true above) should
		// all reach OutputPage::addModules().
		$expected = [
			'ext.bootstrap.scripts',
			'ext.bootstrapComponents.modal.fix',
			'ext.bootstrapComponents.popover.fix',
			'ext.bootstrapComponents.tooltip.fix',
			'ext.bootstrapComponents.carousel.fix',
			'ext.bootstrapComponents.vector-fix',
		];
		sort( $loadedModules );
		sort( $expected );
		$this->assertEquals( $expected, $loadedModules );
	}

	public function testVectorFixSkippedWhenNotVectorSkin() {
		$loadedModules = [];

		$outputPage = $this->createMock( OutputPage::class );
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
