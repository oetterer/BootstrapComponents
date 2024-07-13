<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit\Components;

use MediaWiki\Extension\BootstrapComponents\Components\Carousel;
use MediaWiki\Extension\BootstrapComponents\Tests\Unit\ComponentsTestBase;
use \MWException;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\Components\Carousel
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
class CarouselTest extends ComponentsTestBase {

	private $input = 'Botched input';

	/**
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'MediaWiki\\Extension\\BootstrapComponents\\Components\\Carousel',
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
			'simple'                     => [
				'[[File:Mal.jpg|Malcolm Reynolds_0]]',
				[ '[[File:Mal.jpg|Malcolm Reynolds]]' => true, '[[File:Wash.jpg|link' => '|Hoban Washburne]]' ],
				'<div class="carousel slide" id="bsc_carousel_NULL" data-ride="carousel">
<ol class="carousel-indicators">
	<li data-target="#bsc_carousel_NULL" data-slide-to="0" class="active"></li>
	<li data-target="#bsc_carousel_NULL" data-slide-to="1"></li>
	<li data-target="#bsc_carousel_NULL" data-slide-to="2"></li>
</ol>
<div class="carousel-inner">
	<div class="carousel-item active">[[File:Mal.jpg|Malcolm Reynolds_0]]</div>
	<div class="carousel-item">[[File:Mal.jpg|Malcolm Reynolds]]</div>
	<div class="carousel-item">[[File:Wash.jpg|link=|Hoban Washburne]]</div>
</div><a class="carousel-control-prev" href="#bsc_carousel_NULL" role="button" data-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span></a><a class="carousel-control-next" href="#bsc_carousel_NULL" role="button" data-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span></a></div>',
			],
			'images missing'             => [
				$this->input,
				[ 'class' => 'no_images' ],
				'bootstrap-components-carousel-images-missing',
			],
			'id, fade, style, and class' => [
				$this->input,
				[
					'[[File:Mal.jpg|Malcolm Reynolds]]' => true,
					'[[File:Wash.jpg|link'              => '|Hoban Washburne]]',
					'class'                             => 'crew',
					'fade'                              => true,
					'style'                             => 'float:none;background-color:black',
				],
				'<div class="carousel slide carousel-fade crew" style="float:none;background-color:black" id="bsc_carousel_NULL" data-ride="carousel">
<ol class="carousel-indicators">
	<li data-target="#bsc_carousel_NULL" data-slide-to="0" class="active"></li>
	<li data-target="#bsc_carousel_NULL" data-slide-to="1"></li>
</ol>
<div class="carousel-inner">
	<div class="carousel-item active">[[File:Mal.jpg|Malcolm Reynolds]]</div>
	<div class="carousel-item">[[File:Wash.jpg|link=|Hoban Washburne]]</div>
</div><a class="carousel-control-prev" href="#bsc_carousel_NULL" role="button" data-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span></a><a class="carousel-control-next" href="#bsc_carousel_NULL" role="button" data-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span></a></div>',
			],
		];
	}
}
