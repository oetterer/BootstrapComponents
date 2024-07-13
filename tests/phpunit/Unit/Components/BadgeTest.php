<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit\Components;

use MediaWiki\Extension\BootstrapComponents\Components\Badge;
use MediaWiki\Extension\BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\Components\Badge
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
class BadgeTest extends ComponentsTestBase {

	private $input = 'Badge test text';

	/**
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'MediaWiki\\Extension\\BootstrapComponents\\Components\\Badge',
			new Badge(
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
		$instance = new Badge(
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
				'<span class="badge badge-primary" id="bsc_badge_NULL">' . $this->input . '</span>',
			],
			'empty'           => [
				'',
				[],
				'bootstrap-components-badge-content-missing',
			],
			'manual id'       => [
				$this->input,
				[ 'id' => 'book' ],
				'<span class="badge badge-primary" id="book">' . $this->input . '</span>',
			],
			'style and class' => [
				$this->input,
				[ 'class' => 'dummy nice', 'style' => 'float:right;background-color:#80266e' ],
				'<span class="badge badge-primary dummy nice" style="float:right;background-color:#80266e" id="bsc_badge_NULL">' . $this->input . '</span>',
			],
			'pill'       => [
				$this->input,
				[ 'pill' => 'true' ],
				'<span class="badge badge-pill badge-primary" id="bsc_badge_NULL">' . $this->input . '</span>',
			],
			'no pill'       => [
				$this->input,
				[ 'pill' => 0 ],
				'<span class="badge badge-primary" id="bsc_badge_NULL">' . $this->input . '</span>',
			],
		];
	}
}
