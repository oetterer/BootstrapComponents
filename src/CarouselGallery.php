<?php
/**
 * Contains the class providing and rendering the carousel gallery mode.
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

namespace MediaWiki\Extension\BootstrapComponents;

use ImageGalleryBase;
use MediaWiki\Extension\BootstrapComponents\Components\Carousel;
use MediaWiki\MediaWikiServices;
use MediaWiki\Title\Title;

/**
 * Class CarouselGallery
 *
 * @since 1.0
 */
class CarouselGallery extends ImageGalleryBase {

	/**
	 * Renders the carousel gallery.
	 *
	 * @param ParserOutputHelper $parserOutputHelper used for unit tests
	 *
	 * @throws RuntimeException cascading {@see CarouselGallery::constructCarouselParserRequest} and  {@see AbstractComponent::parseComponent}
	 * @return string
	 */
	public function toHTML( $parserOutputHelper = null ) {
		$parserOutputHelper = is_null( $parserOutputHelper )
			? ApplicationFactory::getInstance()->getParserOutputHelper( $this->mParser )
			: $parserOutputHelper;

		// if there were no images registered with the gallery, display error message and exit.
		if ( $this->isEmpty() ) {
			return $parserOutputHelper->renderErrorMessage( 'bootstrap-components-carousel-images-missing' );
		}

		$carousel = new Carousel(
			MediaWikiServices::getInstance()->getService( 'BootstrapComponents.ComponentLibrary' ),
			$parserOutputHelper,
			MediaWikiServices::getInstance()->getService( 'BootstrapComponents.NestingController' ),
		);
		$carouselParserRequest = $this->constructCarouselParserRequest();

		// if there were no valid images found in the list of registered images, display error message and exit.
		if ( $carouselParserRequest === false ) {
			return $parserOutputHelper->renderErrorMessage( 'bootstrap-components-carousel-images-missing' );
		}
		$carouselPrintOut = $carousel->parseComponent( $carouselParserRequest );
		// the component returns its parser function result, but the <gallery> tag hook must return the html only
		if ( is_array( $carouselPrintOut ) ) {
			$carouselPrintOut = $carouselPrintOut[0];
		}
		return $carouselPrintOut;
	}

	/**
	 * This merges two different kind of attributes, so that the merger can later be treated as parser function attributes.
	 *
	 * @param array $origAttributes
	 * @param array $newAttributes
	 *
	 * @return array
	 */
	private function addParserFunctionAttributes( $origAttributes, $newAttributes ) {
		if ( empty( $newAttributes ) ) {
			return $origAttributes;
		}
		return array_merge( $origAttributes, $newAttributes );
	}

	/**
	 * Builds an image tag like it is normally used in wiki text.
	 *
	 * @param array $imageData
	 *
	 * @return string
	 */
	private function buildImageStringFromData( $imageData ) {

		/** @var \Title $imageTitle */
		list( $imageTitle, $imageCaption, $imageAlt, , $imageParams, , $imageOptions ) = $imageData;
		// core adds targetlang to every gallery image; it is not image syntax, and makeImage sets it again on the re-parse
		unset( $imageParams['targetlang'] );
		$imageParams['alt'] = $imageAlt;
		$imageParams['class'] = empty( $imageOptions['class'] ) ? 'img-fluid' : $imageOptions['class'] . ' img-fluid';

		// imageParams is an associative array param => value
		$carouselImage = '[[' . $imageTitle->getPrefixedText();
		if ( !empty( $imageCaption ) ) {
			$carouselImage .= '|' . $imageCaption;
		}
		foreach ( $imageParams as $key => $val ) {
			if ( !empty( $val ) ) {
				$carouselImage .= '|' . $key . '=' . $val;
			}
		}
		$carouselImage .= $this->buildLinkOption( $imageOptions ?? [] ) . ']]';

		return $carouselImage;
	}

	private function buildLinkOption( array $imageOptions ): string {
		if ( isset( $imageOptions['link-url'] ) ) {
			return '|link=' . $imageOptions['link-url'];
		}
		if ( isset( $imageOptions['link-title'] ) ) {
			return '|link=' . $imageOptions['link-title']->getFullText();
		}
		if ( isset( $imageOptions['no-link'] ) ) {
			return '|link=';
		}
		return '';
	}

	/**
	 * Extracts the gallery images and builds image tags for every valid image.
	 *
	 * @param         $imageList
	 * @param \Parser $parser
	 * @param bool    $hideBadImages
	 * @param Title|null    $contextTitle
	 *
	 * @return array
	 */
	private function convertImages( $imageList, $parser = null, $hideBadImages = true, $contextTitle = null ) {
		$newImageList = [];
		foreach ( $imageList as $imageData ) {
			/** @var \Title $imageTitle */
			$imageTitle = $imageData[0];

			if ( $imageTitle->getNamespace() !== NS_FILE ) {
				if ( is_a( $parser, 'Parser' ) ) {
					$parser->addTrackingCategory( 'broken-file-category' );
				}
				continue;
			} elseif (
				$hideBadImages && MediaWikiServices::getInstance()->getBadFileLookup()
					->isBadFile( $imageTitle->getDBkey(), $contextTitle )
			) {
				continue;
			}

			$carouselImage = $this->buildImageStringFromData( $imageData );
			$newImageList[] = $carouselImage;
		}
		return $newImageList;
	}

	/**
	 * From array of supplies images and some other object properties, this constructs a parser request object,
	 * to be used in the carousel component.
	 *
	 * @throws RuntimeException cascading {@see ApplicationFactory::getNewParserRequest}
	 *
	 * @return false|ParserRequest  returns false, if no valid images were detected
	 */
	private function constructCarouselParserRequest() {
		$carouselAttributes = $this->convertImages(
			$this->getImages(),
			$this->mParser,
			$this->mHideBadImages,
			$this->getContextTitle()
		);
		if ( !count( $carouselAttributes ) ) {
			return false;
		}
		$carouselAttributes = $this->addParserFunctionAttributes( $carouselAttributes, $this->mAttribs );
		$input = array_shift( $carouselAttributes );

		return ApplicationFactory::getInstance()->getNewParserRequest(
			[ $input, $carouselAttributes, $this->mParser, null ],
			false,
			'gallery carousel'
		);
	}
}
