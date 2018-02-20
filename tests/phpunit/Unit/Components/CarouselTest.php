<?php

namespace BootstrapComponents\Tests\Unit\Components;

use BootstrapComponents\Components\Carousel;
use BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \BootstrapComponents\Components\Carousel
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
class CarouselTest extends ComponentsTestBase {

	private $input = 'Botched input';

	/**
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\Components\\Carousel',
			new Carousel(
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
		$instance = new Carousel(
			$this->getComponentLibrary(),
			$this->getParserOutputHelper(),
			$this->getNestingController()
		);

		$parserRequest = $this->buildParserRequest( $input, $arguments );

		/** @noinspection PhpParamsInspection */
		$generatedOutput = $instance->parseComponent( $parserRequest );

		if ( is_array( $generatedOutput ) ) {
			$this->assertEquals(
				[ 0 => $expectedOutput, "isHTML" => true, "noparse" => true, ],
				$generatedOutput
			);
		} else {
			$this->assertEquals(
				$expectedOutput,
				$generatedOutput
			);
		}
	}

	/**
	 * @return array
	 */
	public function placeMeArgumentsProvider() {
		return [
			'simple'               => [
				'[[File:Mal.jpg|Malcolm Reynolds_0]]',
				[ '[[File:Mal.jpg|Malcolm Reynolds]]' => true, '[[File:Wash.jpg|link' => '|Hoban Washburne]]' ],
				'<div class="carousel slide" id="bsc_carousel_NULL" data-ride="carousel">
<ol class="carousel-indicators">
	<li data-target="#bsc_carousel_NULL" data-slide-to="0" class="active"></li>
	<li data-target="#bsc_carousel_NULL" data-slide-to="1"></li>
	<li data-target="#bsc_carousel_NULL" data-slide-to="2"></li>
</ol>
<div class="carousel-inner">
	<div class="item active">[[File:Mal.jpg|Malcolm Reynolds_0]]</div>
	<div class="item">[[File:Mal.jpg|Malcolm Reynolds]]</div>
	<div class="item">[[File:Wash.jpg|link=|Hoban Washburne]]</div>
</div><a class="left carousel-control" href="#bsc_carousel_NULL" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a><a class="right carousel-control" href="#bsc_carousel_NULL" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a></div>',
			],
			'images missing'       => [
				$this->input,
				[ 'class' => 'no_images' ],
				'bootstrap-components-carousel-images-missing',
			],
			'id, style, and class' => [
				$this->input,
				[
					'[[File:Mal.jpg|Malcolm Reynolds]]' => true, '[[File:Wash.jpg|link' => '|Hoban Washburne]]', 'class' => 'crew',
					'style'                             => 'float:none;background-color:black',
				],
				'<div class="carousel slide crew" style="float:none;background-color:black" id="bsc_carousel_NULL" data-ride="carousel">
<ol class="carousel-indicators">
	<li data-target="#bsc_carousel_NULL" data-slide-to="0" class="active"></li>
	<li data-target="#bsc_carousel_NULL" data-slide-to="1"></li>
</ol>
<div class="carousel-inner">
	<div class="item active">[[File:Mal.jpg|Malcolm Reynolds]]</div>
	<div class="item">[[File:Wash.jpg|link=|Hoban Washburne]]</div>
</div><a class="left carousel-control" href="#bsc_carousel_NULL" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a><a class="right carousel-control" href="#bsc_carousel_NULL" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a></div>',
			],
		];
	}
}
