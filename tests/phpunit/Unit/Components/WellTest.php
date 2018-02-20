<?php

namespace BootstrapComponents\Tests\Unit\Components;

use BootstrapComponents\Components\Well;
use BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \BootstrapComponents\Components\Well
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
class WellTest extends ComponentsTestBase {

	private $input = 'Well test text';

	/**
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\Components\\Well',
			new Well(
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
		$instance = new Well(
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
			'simple'                      => [
				$this->input,
				[],
				'<div class="well" id="bsc_well_NULL">' . $this->input . '</div>',
			],
			'manual id'                   => [
				$this->input,
				[ 'id' => 'hms_dortmunder', 'size' => 'lg' ],
				'<div class="well well-lg" id="hms_dortmunder">' . $this->input . '</div>',
			],
			'style and class, wrong size' => [
				$this->input,
				[ 'class' => 'dummy nice', 'style' => 'float:right;background-color:green', 'size' => 'wrong' ],
				'<div class="well dummy nice" style="float:right;background-color:green" id="bsc_well_NULL">' . $this->input . '</div>',
			],
		];
	}
}
