<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit\Hooks;

use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
use MediaWiki\Extension\BootstrapComponents\Hooks\OutputPageParserOutput;
use OutputPage;
use Parser;
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
			'MediaWiki\\Extension\\BootstrapComponents\\Hooks\\OutputPageParserOutput',
			$instance
		);
	}

	public function testHookOutputPageParserOutput() {
		$content = 'CONTENT';

		$outputPage = $this->createMock( OutputPage::class );
		$outputPage->expects( $this->once() )
			->method( 'addHTML' )
			->will( $this->returnCallback( function( $injection ) use ( &$content ) {
				$content .= $injection;
			} ) );
		$outputPage->expects( $this->once() )
			->method( 'addModules' )
			->with(
				$this->equalTo( [ 'ext.bootstrapComponents.vector-fix' ] )
			);

		$observerParserOutput = $this->createMock( ParserOutput::class );
		$observerParserOutput->expects( $this->exactly( 1 ) )
			->method( 'getExtensionData' )
			->with(
				$this->stringContains( 'bsc_deferredContent' )
			)
			->willReturn( [ 'test' ] );

		$bootstrapService = $this->createMock( BootstrapComponentsService::class );
		$bootstrapService->expects( $this->once() )
			->method( 'vectorSkinInUse' )
			->willReturn( true );

		$instance = new OutputPageParserOutput( $outputPage, $observerParserOutput, $bootstrapService );
		$instance->process();

		$this->assertEquals(
			'CONTENT<!-- injected by Extension:BootstrapComponents -->test<!-- /injected by Extension:BootstrapComponents -->',
			$content
		);
	}
}
