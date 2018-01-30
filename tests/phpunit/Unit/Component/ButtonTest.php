<?php

namespace BootstrapComponents\Tests\Unit\Component;

use BootstrapComponents\Component\Button;
use BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \BootstrapComponents\Component\Button
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
class ButtonTest extends ComponentsTestBase {

	private $input = 'Button test text';

	/**
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\Component\\Button',
			new Button(
				$this->getComponentLibrary(),
				$this->getParserOutputHelper(),
				$this->getNestingController()
			)
		);
	}

	/**
	 * @param string $input
	 * @param array  $arguments
	 * @param string $expectedOutputPattern
	 *
	 * @dataProvider placeMeArgumentsProvider
	 * @throws MWException
	 */
	public function testCanRender( $input, $arguments, $expectedOutputPattern ) {
		$instance = new Button(
			$this->getComponentLibrary(),
			$this->getParserOutputHelper(),
			$this->getNestingController()
		);

		$parserRequest = $this->buildParserRequest( $input, $arguments );

		/** @noinspection PhpParamsInspection */
		$generatedOutput = $instance->parseComponent( $parserRequest );
		if ( is_array( $generatedOutput ) ) {
			$generatedOutput = $generatedOutput[0];
		}

		$this->assertRegExp(
			$expectedOutputPattern,
			$generatedOutput
		);
	}

	/**
	 * @throws MWException
	 */
	public function testCanInjectRawAttributes() {

		$instance = new Button(
			$this->getComponentLibrary(),
			$this->getParserOutputHelper(),
			$this->getNestingController()
		);

		$parserRequest = $this->buildParserRequest(
			$this->input,
			[ 'class' => 'manual', 'size' => 'md' ]
		);

		$instance->injectRawAttributes(
			[ 'data-toggle' => 'foo', 'data-target' => '#bar' ]
		);

		/** @noinspection PhpParamsInspection */
		$generatedOutput = $instance->parseComponent( $parserRequest );
		if ( is_array( $generatedOutput ) ) {
			$generatedOutput = $generatedOutput[0];
		}

		$this->assertRegExp(
			'~^<a class="btn btn-default btn-md manual" role="button" id="bsc_button_NULL" href=".*/' . str_replace( ' ', '_', $this->input ) . '" data-toggle="foo" data-target="#bar">' . $this->input . '</a>$~',
			$generatedOutput
		);
	}

	/**
	 * @return array
	 */
	public function placeMeArgumentsProvider() {
		return [
			'simple'             => [
				$this->input,
				[],
				'~^<a class="btn btn-default" role="button" id="bsc_button_NULL" href=".*/' . str_replace( ' ', '_', $this->input ) . '">' . $this->input . '</a>$~',
			],
			'empty'              => [
				'',
				[],
				'~bootstrap-components-button-target-missing~', // because getParserOutputHelper-mock returns the message key instead of parsing it.
			],
			'invalid'            => [
				'     ',
				[],
				'~bootstrap-components-button-target-invalid~', // because getParserOutputHelper-mock returns the message key instead of parsing it.
			],
			'disabled, color, text and id' => [
				$this->input,
				[ 'disabled' => true, 'color' => 'danger', 'text' => 'BUTTON', 'id' => 'red' ],
				'~^<a class="btn btn-danger disabled" role="button" id="red" href=".*/' . str_replace( ' ', '_', $this->input ) . '">BUTTON</a>$~',
			],
			'size and active' => [
				$this->input,
				[ 'size' => 'lg', 'active' => true ],
				'~^<a class="btn btn-default btn-lg active" role="button" id="bsc_button_NULL" href=".*/' . str_replace( ' ', '_', $this->input ) . '">' . $this->input . '</a>$~',
			],
			'invlid size' => [
				$this->input,
				[ 'size' => 'nice' ],
				'~^<a class="btn btn-default" role="button" id="bsc_button_NULL" href=".*/' . str_replace( ' ', '_', $this->input ) . '">' . $this->input . '</a>$~',
			],
			'link inside button' => [
				$this->input,
				[ 'text' => 'This is a <a href="/wiki/index.php/Link>Link</a> inside the button text' ],
				'~^<a class="btn btn-default" role="button" id="bsc_button_NULL" href=".*/' . str_replace( ' ', '_', $this->input ) . '">This is a Link inside the button text</a>$~',
			],
		];
	}
}
