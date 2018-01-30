<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\CarouselGallery;
use BootstrapComponents\ParserRequest;
use \MWException;
use \PHPUnit_Framework_TestCase;
use \Title;

/**
 * @covers  \BootstrapComponents\CarouselGallery
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
class CarouselGalleryTest extends PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\CarouselGallery',
			new CarouselGallery( 'carousel' )
		);
	}

	/**
	 * @param array  $imageList
	 * @param array  $additionalAttributes
	 * @param string $expectedOutput
	 *
	 * @throws MWException
	 * @dataProvider galleryDataProvider
	 */
	public function testToHtml( $imageList, $additionalAttributes, $expectedOutput ) {
		$parserOutputHelper = $this->getMockBuilder( 'BootstrapComponents\\ParserOutputHelper' )
			->disableOriginalConstructor()
			->getMock();
		$parserOutputHelper->expects( $this->any() )
			->method( 'renderErrorMessage' )
			->will( $this->returnArgument( 0 ) );

		$instance = new CarouselGallery( 'carousel' );
		$instance->mParser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$instance->mParser->expects( $this->any() )
			->method( 'recursiveTagParse' )
			->will( $this->returnArgument( 0 ) );

		foreach ( $imageList as $imageData ) {
			$instance->add( Title::newFromText( $imageData[0] ), $imageData[1], $imageData[2], $imageData[3], $imageData[4] );
		}
		$instance->setAttributes( $additionalAttributes );
		$this->assertEquals(
			$expectedOutput,
			$instance->toHTML( $parserOutputHelper )
		);
	}

	/**
	 * @return array
	 */
	public function galleryDataProvider() {
		return [
			'simple' => [
				[
					[ 'File:Mal.jpg', 'Malcolm Reynolds', '(alt) Malcolm Reynolds', '', [] ],
					[ 'File:Wash.jpg', 'Hoban Washburne', '', '/List_of_best_Pilots_in_the_Verse', [] ],
					[ 'File:MirandaSecretFiles.pdf', '(c) by Hands of Blue', '', '', [ 'page' => '13', 'float' => 'none' ] ],
				],
				[
					'class' => 'firefly',
					'style' => 'float:space',
					'id'    => 'youcanttakethesky',
				],
				[
					0 => '<div class="carousel slide firefly" style="float:space" id="youcanttakethesky" data-ride="carousel">' . PHP_EOL
						. '<ol class="carousel-indicators">' . PHP_EOL
						. "\t". '<li data-target="#youcanttakethesky" data-slide-to="0" class="active"></li>' . PHP_EOL
						. "\t". '<li data-target="#youcanttakethesky" data-slide-to="1"></li>' . PHP_EOL
						. "\t". '<li data-target="#youcanttakethesky" data-slide-to="2"></li>' . PHP_EOL
						. '</ol>' . PHP_EOL
						. '<div class="carousel-inner">' . PHP_EOL
						. "\t". '<div class="item active">[[File:Mal.jpg|Malcolm Reynolds|alt=(alt) Malcolm Reynolds|class=img-responsive]]</div>' . PHP_EOL
						. "\t". '<div class="item">[[File:Wash.jpg|Hoban Washburne|link=/List_of_best_Pilots_in_the_Verse|class=img-responsive]]</div>' . PHP_EOL
						. "\t". '<div class="item">[[File:MirandaSecretFiles.pdf|(c) by Hands of Blue|page=13|float=none|class=img-responsive]]</div>' . PHP_EOL
						. '</div><a class="left carousel-control" href="#youcanttakethesky" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a><a class="right carousel-control" href="#youcanttakethesky" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a></div>',
					'isHTML' => true,
					'noparse' => true,
				],
			],
			'no local attributes' => [
				[
					[ 'File:Mal.jpg', 'Malcolm Reynolds', '(alt) Malcolm Reynolds', '', [] ],
					[ 'File:Wash.jpg', 'Hoban Washburne', '', '/List_of_best_Pilots_in_the_Verse', [] ],
				],
				[],
				[
					0 => '<div class="carousel slide" id="bsc_carousel_0" data-ride="carousel">' . PHP_EOL
						. '<ol class="carousel-indicators">' . PHP_EOL
						. "\t". '<li data-target="#bsc_carousel_0" data-slide-to="0" class="active"></li>' . PHP_EOL
						. "\t". '<li data-target="#bsc_carousel_0" data-slide-to="1"></li>' . PHP_EOL
						. '</ol>' . PHP_EOL
						. '<div class="carousel-inner">' . PHP_EOL
						. "\t". '<div class="item active">[[File:Mal.jpg|Malcolm Reynolds|alt=(alt) Malcolm Reynolds|class=img-responsive]]</div>' . PHP_EOL
						. "\t". '<div class="item">[[File:Wash.jpg|Hoban Washburne|link=/List_of_best_Pilots_in_the_Verse|class=img-responsive]]</div>' . PHP_EOL
						. '</div><a class="left carousel-control" href="#bsc_carousel_0" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a><a class="right carousel-control" href="#bsc_carousel_0" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a></div>',
					'isHTML' => true,
					'noparse' => true,
				],
			],
			'only invalid images' => [
				[
					[ 'Londinium', 'Londinium', '', '', [] ],
					[ 'Template:Planets', 'Planets', '', '', [] ],
				],
				[],
				'bootstrap-components-carousel-images-missing'
			],
			'no images' => [
				[],
				[],
				'bootstrap-components-carousel-images-missing'
			]
		];
	}
}
