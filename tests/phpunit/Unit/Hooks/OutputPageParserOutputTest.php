<?php

namespace BootstrapComponents\Tests\Unit\Hooks;

use BootstrapComponents\Hooks\OutputPageParserOutput;
use BootstrapComponents\ParserOutputHelper;
use \ParserOutput;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\Hooks\OutputPageParserOutput
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
class OutputPageParserOutputTest extends PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$outputPage = $this->getMockBuilder( 'OutputPage' )
			->disableOriginalConstructor()
			->getMock();
		$parserOutput = $this->getMockBuilder( 'ParserOutput' )
			->disableOriginalConstructor()
			->getMock();
		$parserOutputHelper = $this->getMockBuilder( 'BootstrapComponents\ParserOutputHelper' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$instance = new OutputPageParserOutput( $outputPage, $parserOutput, $parserOutputHelper );

		$this->assertInstanceOf(
			'BootstrapComponents\\Hooks\\OutputPageParserOutput',
			$instance
		);
	}

	public function testHookOutputPageParserOutput() {
		$content = 'CONTENT';
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$outputPage = $this->getMockBuilder( 'OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		$observerParserOutput = $this->getMockBuilder(ParserOutput::class )
			->disableOriginalConstructor()
#			->setMethods( [ 'getText', 'setText', 'getExtensionData' ] )
			->getMock();
		$observerParserOutput->expects( $this->exactly( 1 ) )
			->method( 'getText' )
			->will( $this->returnCallback( function() use ( &$content ) {
				return $content;
			} ) );
		$observerParserOutput->expects( $this->exactly( 1 ) )
			->method( 'setText' )
			->will( $this->returnCallback( function( $injection ) use ( &$content ) {
				$content = $injection;
			} ) );
		$observerParserOutput->expects( $this->exactly( 1 ) )
			->method( 'getExtensionData' )
			->with(
				$this->stringContains( 'bsc_deferredContent' )
			)
			->willReturn( [ 'test' ] );

		/** @noinspection PhpParamsInspection */
		$parserOutputHelper = new ParserOutputHelper( $parser );

		/** @noinspection PhpParamsInspection */
		$instance = new OutputPageParserOutput( $outputPage, $observerParserOutput, $parserOutputHelper );

		$this->assertTrue(
			$instance->process()
		);
		$this->assertEquals(
			'CONTENT<!-- injected by Extension:BootstrapComponents -->test<!-- /injected by Extension:BootstrapComponents -->',
			$content
		);
	}
}
