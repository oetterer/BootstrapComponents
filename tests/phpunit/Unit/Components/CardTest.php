<?php

namespace BootstrapComponents\Tests\Unit\Components;

use BootstrapComponents\Components\Accordion;
use BootstrapComponents\Components\Card;
use BootstrapComponents\NestingController;
use BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \BootstrapComponents\Components\Card
 *
 * @ingroup Test
 *
 * @group   extension-bootstrap-components
 * @group   mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   4.0
 * @author  Tobias Oetterer
 */
class CardTest extends ComponentsTestBase {

	private $input = 'Card test text';

	/**
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$this->assertInstanceOf(
			Card::class,
			new Card(
				$this->getComponentLibrary(),
				$this->getParserOutputHelper(),
				$this->getNestingController()
			)
		);
	}

	/**
	 * @param string $input
	 * @param array  $arguments
	 * @param string $expectedOutput
	 *
	 * @throws MWException
	 *
	 * @dataProvider placeMeArgumentsProvider
	 */
	public function testCanRender( $input, $arguments, $expectedOutput ) {
		$instance = new Card(
			$this->getComponentLibrary(),
			$this->getParserOutputHelper(),
			$this->getNestingController()
		);

		$parserRequest = $this->buildParserRequest( $input, $arguments );

		/** @noinspection PhpParamsInspection */
		$generatedOutput = $instance->parseComponent( $parserRequest );

		$this->assertEquals( $expectedOutput, $generatedOutput );
	}

	/**
	 * @param string $input
	 * @param array  $arguments
	 * @param string $expectedOutput
	 *
	 * @throws MWException
	 *
	 * @dataProvider placeMeInsideAccordionArgumentsProvider
	 */
	public function testCanRenderAccordionCard( $input, $arguments, $expectedOutput ) {
		$accordion = $this->getMockBuilder( Accordion::class )
			->disableOriginalConstructor()
			->getMock();
		$accordion->expects( $this->any() )
			->method( 'getComponentName' )
			->willReturn( 'accordion' );
		$accordion->expects( $this->any() )
			->method( 'getId' )
			->willReturn( 'accordion0' );
		$nestingController = $this->getMockBuilder( NestingController::class )
			->disableOriginalConstructor()
			->getMock();
		$nestingController->expects( $this->any() )
			->method( 'generateUniqueId' )
			->will( $this->returnCallback( function( $componentName ) {
				return 'bsc_' . $componentName . '_NULL';
			} ) );
		$nestingController->expects( $this->any() )
			->method( 'getCurrentElement' )
			->willReturn( $accordion );

		/** @noinspection PhpParamsInspection */
		$instance = new Card(
			$this->getComponentLibrary(),
			$this->getParserOutputHelper(),
			$nestingController
		);

		$parserRequest = $this->buildParserRequest( $input, $arguments );

		/** @noinspection PhpParamsInspection */
		$generatedOutput = $instance->parseComponent( $parserRequest );

		$this->assertEquals( $expectedOutput, $generatedOutput );
	}

	/**
	 * @return array
	 */
	public function placeMeArgumentsProvider() {
		return [
			'simple'            => [
				$this->input,
				[],
				'<div class="card"><div id="bsc_card_NULL"><div class="card-body">' . $this->input . '</div></div></div>',
			],
			'text missing'      => [
				'',
				[ 'header' => 'watch this', 'footer' => 'watch what?', 'collapsible' => 'false', ],
				'<div class="card"><div class="card-header"><h4 class="card-title" style="margin-top:0;padding-top:0;">watch this</h4></div><div id="bsc_card_NULL"><div class="card-body"></div><div class="card-footer">watch what?</div></div></div>',
			],
			'heading alias' => [
				'',
				[ 'heading' => 'watch this', ],
				'<div class="card"><div class="card-header"><h4 class="card-title" style="margin-top:0;padding-top:0;">watch this</h4></div><div id="bsc_card_NULL"><div class="card-body"></div></div></div>',
			],
			'all attributes'    => [
				$this->input,
				[
					'class'       => 'dummy nice',
					'style'       => 'float:right;background-color:green',
					'id'          => 'badgers_bowler',
					'active'      => 'yes',
					'color'       => 'info',
					'collapsible' => '',
					'header'     => 'HEADING TEXT',
					'footer'      => 'FOOTER TEXT',
				],
				'<div class="card border-info dummy nice" style="float:right;background-color:green"><div class="card-header" data-toggle="collapse" href="#badgers_bowler"><h4 class="card-title" style="margin-top:0;padding-top:0;">HEADING TEXT</h4></div><div id="badgers_bowler" class="card-collapse collapse fade show"><div class="card-body text-info">' . $this->input . '</div><div class="card-footer">FOOTER TEXT</div></div></div>',
			],
			'collapsible false' => [
				$this->input,
				[ 'collapsible' => 'false', ],
				'<div class="card"><div id="bsc_card_NULL"><div class="card-body">' . $this->input . '</div></div></div>',
			],
			'background not white' => [
				$this->input,
				[ 'background' => 'danger', ],
				'<div class="card bg-danger text-white"><div id="bsc_card_NULL"><div class="card-body">' . $this->input . '</div></div></div>',
			],
			'background white' => [
				$this->input,
				[ 'background' => 'light', ],
				'<div class="card bg-light"><div id="bsc_card_NULL"><div class="card-body">' . $this->input . '</div></div></div>',
			],
		];
	}

	/**
	 * @return array
	 */
	public function placeMeInsideAccordionArgumentsProvider() {
		return [
			'simple'            => [
				$this->input,
				[],
				'<div class="card"><div class="card-header" data-parent="#accordion0" data-toggle="collapse" href="#bsc_card_NULL"><h4 class="card-title" style="margin-top:0;padding-top:0;">bsc_card_NULL</h4></div><div id="bsc_card_NULL" class="card-collapse collapse fade"><div class="card-body">' . $this->input . '</div></div></div>',
			],
			'text missing'      => [
				'',
				[ 'header' => 'watch this', 'footer' => 'watch what?', 'collapsible' => 'false', ],
				'<div class="card"><div class="card-header" data-parent="#accordion0" data-toggle="collapse" href="#bsc_card_NULL"><h4 class="card-title" style="margin-top:0;padding-top:0;">watch this</h4></div><div id="bsc_card_NULL" class="card-collapse collapse fade"><div class="card-body"></div><div class="card-footer">watch what?</div></div></div>',
			],
			'all attributes'    => [
				$this->input,
				[
					'class'       => 'dummy nice',
					'style'       => 'float:right;background-color:green',
					'id'          => 'badgers_bowler',
					'active'      => 'yes',
					'color'       => 'info',
					'collapsible' => '',
					'heading'     => 'HEADING TEXT',
					'footer'      => 'FOOTER TEXT',
				],
				'<div class="card border-info dummy nice" style="float:right;background-color:green"><div class="card-header" data-parent="#accordion0" data-toggle="collapse" href="#badgers_bowler"><h4 class="card-title" style="margin-top:0;padding-top:0;">HEADING TEXT</h4></div><div id="badgers_bowler" class="card-collapse collapse fade show"><div class="card-body text-info">' . $this->input . '</div><div class="card-footer">FOOTER TEXT</div></div></div>',
			],
			'collapsible false' => [
				$this->input,
				[ 'collapsible' => 'false', ],
				'<div class="card"><div class="card-header" data-parent="#accordion0" data-toggle="collapse" href="#bsc_card_NULL"><h4 class="card-title" style="margin-top:0;padding-top:0;">bsc_card_NULL</h4></div><div id="bsc_card_NULL" class="card-collapse collapse fade"><div class="card-body">' . $this->input . '</div></div></div>',
			],
		];
	}
}
