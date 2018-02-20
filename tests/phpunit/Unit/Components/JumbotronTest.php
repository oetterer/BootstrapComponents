<?php

namespace BootstrapComponents\Tests\Unit\Components;

use BootstrapComponents\Components\Jumbotron;
use BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \BootstrapComponents\Components\Jumbotron
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
class JumbotronTest extends ComponentsTestBase {

	private $input = 'Jumbotron test text';

	/**
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\Components\\Jumbotron',
			new Jumbotron(
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
		$instance = new Jumbotron(
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
				'<div class="container"><div class="jumbotron" id="bsc_jumbotron_NULL">' . $this->input . '</div></div>',
			],
			'manual id'       => [
				$this->input,
				[ 'id' => 'hms_dortmunder' ],
				'<div class="container"><div class="jumbotron" id="hms_dortmunder">' . $this->input . '</div></div>',
			],
			'style and class' => [
				$this->input,
				[ 'class' => 'dummy nice', 'style' => 'float:right;background-color:green' ],
				'<div class="container"><div class="jumbotron dummy nice" style="float:right;background-color:green" id="bsc_jumbotron_NULL">' . $this->input . '</div></div>',
			],
		];
	}
}
