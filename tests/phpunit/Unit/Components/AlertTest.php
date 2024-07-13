<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit\Components;

use MediaWiki\Extension\BootstrapComponents\Components\Alert;
use MediaWiki\Extension\BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\Components\Alert
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
class AlertTest extends ComponentsTestBase {

	private $input = 'Alert test text';

	/**
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$this->assertInstanceOf(
			Alert::class,
			new Alert(
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
		$instance = new Alert(
			$this->getComponentLibrary(),
			$this->getParserOutputHelper(),
			$this->getNestingController()
		);

		$parserRequest = $this->buildParserRequest( $input, $arguments );

		$generatedOutput = $instance->parseComponent( $parserRequest );

		// TODO when we drop support for MW1.39
		if ( version_compare( $GLOBALS['wgVersion'], '1.40', 'lt' ) ) {
			$this->assertRegExp(
				'~^<div.+class="alert alert-.+".*role="alert".*>' . $this->input . '(<button.*button>)?</div>$~',
				$generatedOutput
			);
		} else {
			$this->assertMatchesRegularExpression(
				'~^<div.+class="alert alert-.+".*role="alert".*>' . $this->input . '(<button.*button>)?</div>$~',
				$generatedOutput
			);
		}

		$this->assertEquals( $expectedOutput, $generatedOutput );
	}

	/**
	 * @return array
	 */
	public function placeMeArgumentsProvider() {
		return [
			'simple'                => [
				$this->input,
				[],
				'<div class="alert alert-primary" id="bsc_alert_NULL" role="alert">' . $this->input . '</div>',
			],
			'color_unknown'         => [
				$this->input,
				[ 'color' => 'unknown' ],
				'<div class="alert alert-primary" id="bsc_alert_NULL" role="alert">' . $this->input . '</div>',
			],
			'dismiss_arbitrary'       => [
				$this->input,
				[ 'dismissible' => 'bla' ],
				'<div class="alert alert-primary alert-dismissible" id="bsc_alert_NULL" role="alert">' . $this->input . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>',
			],
			'dismiss'               => [
				$this->input,
				[ 'dismissible' => true ],
				'<div class="alert alert-primary alert-dismissible" id="bsc_alert_NULL" role="alert">' . $this->input . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>',
			],
			'fading'                => [
				$this->input,
				[ 'dismissible' => 'fade', 'color' => 'warning' ],
				'<div class="alert alert-warning alert-dismissible fade show" id="bsc_alert_NULL" role="alert">' . $this->input . '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>',
			],
			'manual id, no dismiss' => [
				$this->input,
				[ 'color' => 'danger', 'id' => 'hms_dortmunder', 'dismissible' => 'false' ],
				'<div class="alert alert-danger" id="hms_dortmunder" role="alert">' . $this->input . '</div>',
			],
			'style and class'       => [
				$this->input,
				[ 'class' => 'dummy nice', 'style' => 'float:right;background-color:green' ],
				'<div class="alert alert-primary dummy nice" style="float:right;background-color:green" id="bsc_alert_NULL" role="alert">' . $this->input . '</div>',
			],
		];
	}
}
