<?php

namespace BootstrapComponents\Tests\Unit\Components;

use BootstrapComponents\Components\Panel;
use BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \BootstrapComponents\Components\Panel
 *
 * @ingroup Test
 *
 * @group   extension-bootstrap-components
 * @group   mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   1.0
 * @author  Tobias Oetterer
 */
class PanelTest extends ComponentsTestBase {

	private $input = 'Panel test text';

	/**
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\Components\\Panel',
			new Panel(
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
		$instance = new Panel(
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
	public function testCanRenderAccordionPanel( $input, $arguments, $expectedOutput ) {
		$accordion = $this->getMockBuilder( 'BootstrapComponents\\Components\\Accordion' )
			->disableOriginalConstructor()
			->getMock();
		$accordion->expects( $this->any() )
			->method( 'getComponentName' )
			->willReturn( 'accordion' );
		$accordion->expects( $this->any() )
			->method( 'getId' )
			->willReturn( 'accordion0' );
		$nestingController = $this->getMockBuilder( 'BootstrapComponents\\NestingController' )
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
		$instance = new Panel(
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
				'<div class="panel panel-default"><div id="bsc_panel_NULL"><div class="panel-body">' . $this->input . '</div></div></div>',
			],
			'text missing'      => [
				'',
				[ 'heading' => 'watch this', 'footer' => 'watch what?', 'collapsible' => 'false', ],
				'<div class="panel panel-default"><div class="panel-heading"><h4 class="panel-title" style="margin-top:0;padding-top:0;">watch this</h4></div><div id="bsc_panel_NULL"><div class="panel-body"></div><div class="panel-footer">watch what?</div></div></div>',
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
				'<div class="panel panel-info dummy nice" style="float:right;background-color:green"><div class="panel-heading" data-toggle="collapse" href="#badgers_bowler"><h4 class="panel-title" style="margin-top:0;padding-top:0;">HEADING TEXT</h4></div><div id="badgers_bowler" class="panel-collapse collapse fade in"><div class="panel-body">' . $this->input . '</div><div class="panel-footer">FOOTER TEXT</div></div></div>',
			],
			'collapsible false' => [
				$this->input,
				[ 'collapsible' => 'false', ],
				'<div class="panel panel-default"><div id="bsc_panel_NULL"><div class="panel-body">' . $this->input . '</div></div></div>',
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
				'<div class="panel panel-default"><div class="panel-heading" data-parent="#accordion0" data-toggle="collapse" href="#bsc_panel_NULL"><h4 class="panel-title" style="margin-top:0;padding-top:0;">bsc_panel_NULL</h4></div><div id="bsc_panel_NULL" class="panel-collapse collapse fade"><div class="panel-body">' . $this->input . '</div></div></div>',
			],
			'text missing'      => [
				'',
				[ 'heading' => 'watch this', 'footer' => 'watch what?', 'collapsible' => 'false', ],
				'<div class="panel panel-default"><div class="panel-heading" data-parent="#accordion0" data-toggle="collapse" href="#bsc_panel_NULL"><h4 class="panel-title" style="margin-top:0;padding-top:0;">watch this</h4></div><div id="bsc_panel_NULL" class="panel-collapse collapse fade"><div class="panel-body"></div><div class="panel-footer">watch what?</div></div></div>',
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
				'<div class="panel panel-info dummy nice" style="float:right;background-color:green"><div class="panel-heading" data-parent="#accordion0" data-toggle="collapse" href="#badgers_bowler"><h4 class="panel-title" style="margin-top:0;padding-top:0;">HEADING TEXT</h4></div><div id="badgers_bowler" class="panel-collapse collapse fade in"><div class="panel-body">' . $this->input . '</div><div class="panel-footer">FOOTER TEXT</div></div></div>',
			],
			'collapsible false' => [
				$this->input,
				[ 'collapsible' => 'false', ],
				'<div class="panel panel-default"><div class="panel-heading" data-parent="#accordion0" data-toggle="collapse" href="#bsc_panel_NULL"><h4 class="panel-title" style="margin-top:0;padding-top:0;">bsc_panel_NULL</h4></div><div id="bsc_panel_NULL" class="panel-collapse collapse fade"><div class="panel-body">' . $this->input . '</div></div></div>',
			],
		];
	}
}
