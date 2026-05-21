<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit\Hooks;

use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
use MediaWiki\Extension\BootstrapComponents\Hooks\OutputPageParserOutput;
use MediaWiki\Output\OutputPage;
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
			$this->createMock( BootstrapComponentsService::class )
		);

		$this->assertInstanceOf(
			OutputPageParserOutput::class,
			$instance
		);
	}

	public function testHookOutputPageParserOutputLoadsVectorFixUnderVector() {
		$outputPage = $this->createMock( OutputPage::class );
		$outputPage->expects( $this->never() )->method( 'addHTML' );
		$outputPage->expects( $this->once() )
			->method( 'addModules' )
			->with(
				$this->equalTo( [ 'ext.bootstrapComponents.vector-fix' ] )
			);

		$bootstrapService = $this->createMock( BootstrapComponentsService::class );
		$bootstrapService->expects( $this->once() )
			->method( 'vectorSkinInUse' )
			->willReturn( true );

		$instance = new OutputPageParserOutput( $outputPage, $bootstrapService );
		$instance->process();
	}

	public function testHookDoesNothingWhenNotVector() {
		$outputPage = $this->createMock( OutputPage::class );
		$outputPage->expects( $this->never() )->method( 'addHTML' );
		$outputPage->expects( $this->never() )->method( 'addModules' );

		$bootstrapService = $this->createMock( BootstrapComponentsService::class );
		$bootstrapService->expects( $this->once() )
			->method( 'vectorSkinInUse' )
			->willReturn( false );

		$instance = new OutputPageParserOutput( $outputPage, $bootstrapService );
		$instance->process();
	}
}
