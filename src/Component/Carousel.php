<?php
/**
 * Contains the component class for rendering a carousel.
 *
 * @copyright (C) 2018, Tobias Oetterer, Paderborn University
 * @license       https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 (or later)
 *
 * This file is part of the MediaWiki extension BootstrapComponents.
 * The BootstrapComponents extension is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The BootstrapComponents extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @file
 * @ingroup       BootstrapComponents
 * @author        Tobias Oetterer
 */

namespace BootstrapComponents\Component;

use BootstrapComponents\AbstractComponent;
use BootstrapComponents\ParserRequest;
use \Html;

/**
 * Class Carousel
 *
 * Class for component 'carousel'
 *
 * @see   https://github.com/oetterer/BootstrapComponents/blob/master/docs/components.md#Carousel
 * @since 1.0
 */
class Carousel extends AbstractComponent {
	/**
	 * @inheritdoc
	 *
	 * @param string $input
	 */
	public function placeMe( $input ) {
		$images = $this->extractAndParseImageList( $this->getParserRequest() );
		if ( !count( $images ) ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-carousel-images-missing' );
		}

		$class = [ 'carousel', 'slide' ];
		list ( $class, $style ) = $this->processCss( $class, [] );

		return [
			Html::rawElement(
				'div',
				[
					'class'     => $this->arrayToString( $class, ' ' ),
					'style'     => $this->arrayToString( $style, ';' ),
					'id'        => $this->getId(),
					'data-ride' => 'carousel',
				],
				$this->generateIndicators( count( $images ) )
				. Html::rawElement(
					'div',
					[ 'class' => 'carousel-inner' ],
					$this->convertImagesIntoSlides( $images )
				)
				. $this->buildControls()
			),
			"isHTML"  => true,
			"noparse" => true,
		];
	}

	/**
	 * Responsible for generating the a tags that make up the prev and next controls.
	 *
	 * @return string
	 */
	private function buildControls() {
		return Html::rawElement(
				'a',
				[
					'class'      => 'left carousel-control',
					'href'       => '#' . $this->getId(),
					'data-slide' => 'prev',
				],
				Html::rawElement( 'span', [ 'class' => 'glyphicon glyphicon-chevron-left' ] )
			) . Html::rawElement(
				'a',
				[
					'class'      => 'right carousel-control',
					'href'       => '#' . $this->getId(),
					'data-slide' => 'next',
				],
				Html::rawElement( 'span', [ 'class' => 'glyphicon glyphicon-chevron-right' ] )
			);
	}

	/**
	 * @param string $attribute
	 * @param string $value
	 *
	 * @return false|string
	 */
	private function buildImageString( $attribute, $value ) {
		$imageParts = [];
		if ( !is_int( $attribute ) ) {
			$imageParts[] = $attribute;
		}
		if ( is_string( $value ) ) {
			$imageParts[] = $value;
		}
		$imageString = implode( '=', $imageParts );
		if ( preg_match( '/\[.+\]/', $imageString ) ) {
			return $imageString;
		}
		return false;
	}

	/**
	 * Converts the carousel image into slides.
	 *
	 * @param string[] $images
	 *
	 * @return string
	 */
	private function convertImagesIntoSlides( $images ) {
		$slides = PHP_EOL;
		$active = ' active';
		foreach ( $images as $image ) {
			$slides .= "\t" . Html::rawElement(
					'div',
					[ 'class' => 'item' . $active ],
					$image
				) . PHP_EOL;
			$active = '';
		}
		return $slides;
	}

	/**
	 * Extracts and parses all images for the carousel.
	 *
	 * @param ParserRequest $parserRequest
	 *
	 * @return string[]
	 */
	private function extractAndParseImageList( ParserRequest $parserRequest ) {
		$elements = [];
		if ( !empty( $parserRequest->getInput() ) ) {
			$elements[] = $parserRequest->getInput();
		}
		$elements = array_merge( $elements, $parserRequest->getAttributes() );
		$images = [];
		foreach ( $elements as $key => $val ) {
			$imageString = $this->buildImageString( $key, $val );
			if ( is_string( $imageString ) ) {
				// we assume an image, local or remote
				$images[] = $parserRequest->getParser()->recursiveTagParse(
					$imageString,
					$parserRequest->getFrame()
				);
			}
		}
		return $images;
	}

	/**
	 * Generates the dots in the bottom section that let you jump to a specific image.
	 *
	 * @param int $num
	 *
	 * @return string
	 */
	private function generateIndicators( $num ) {
		$inner = PHP_EOL;
		$class = 'active';
		for ( $i = 0; $i < $num; $i++ ) {
			$inner .= "\t" . Html::rawElement(
					'li',
					[
						'data-target'   => '#' . $this->getId(),
						'data-slide-to' => $i,
						'class'         => $class,
					]
				) . PHP_EOL;
			$class = false;
		}
		return PHP_EOL . Html::rawElement(
				'ol',
				[ 'class' => 'carousel-indicators' ],
				$inner
			) . PHP_EOL;
	}
}