<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit\Components;

use MediaWiki\Extension\BootstrapComponents\Components\Collapse;
use MediaWiki\Extension\BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\Components\Collapse
 *
 * @ingroup Test
 *
 * @group extension-bootstrap-components
 * @group mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   1.0
 * @author  Tobias Oetterer
 */
class CollapseTest extends ComponentsTestBase {

	private $input = 'Collapse test text';

	/**
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'MediaWiki\\Extension\\BootstrapComponents\\Components\\Collapse',
			new Collapse(
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
	 * @dataProvider placeMeArgumentsProvider
	 * @throws MWException
	 */
	public function testCanRender( $input, $arguments, $expectedOutput ) {
		$instance = new Collapse(
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
	 * @return array
	 */
	public function placeMeArgumentsProvider() {
		return [
			'simple'          => [
				$this->input,
				[],
				'<a class="btn btn-primary" role="button" id="bsc_button_NULL" href="#bsc_collapse_NULL" data-toggle="collapse">#bsc_collapse_NULL</a><div class="collapse" id="bsc_collapse_NULL">' . $this->input . '</div>',
			],
			'color_unknown'   => [
				$this->input,
				[ 'color' => 'unknown' ],
				'<a class="btn btn-primary" role="button" id="bsc_button_NULL" href="#bsc_collapse_NULL" data-toggle="collapse">#bsc_collapse_NULL</a><div class="collapse" id="bsc_collapse_NULL">' . $this->input . '</div>',
			],
			'button text'     => [
				$this->input,
				[ 'text' => 'BUTTON' ],
				'<a class="btn btn-primary" role="button" id="bsc_button_NULL" href="#bsc_collapse_NULL" data-toggle="collapse">BUTTON</a><div class="collapse" id="bsc_collapse_NULL">' . $this->input . '</div>',
			],
			'manual id'       => [
				$this->input,
				[ 'color' => 'success', 'id' => 'alliance' ],
				'<a class="btn btn-success" role="button" id="bsc_button_NULL" href="#alliance" data-toggle="collapse">#alliance</a><div class="collapse" id="alliance">' . $this->input . '</div>',
			],
			'style and class' => [
				$this->input,
				[ 'class' => 'dummy nice', 'style' => 'float:right;background-color:green' ],
				'<a class="btn btn-primary dummy nice" style="float:right;background-color:green" role="button" id="bsc_button_NULL" href="#bsc_collapse_NULL" data-toggle="collapse">#bsc_collapse_NULL</a><div class="collapse dummy nice" style="float:right;background-color:green" id="bsc_collapse_NULL">' . $this->input . '</div>',
			],
		];
	}
}
