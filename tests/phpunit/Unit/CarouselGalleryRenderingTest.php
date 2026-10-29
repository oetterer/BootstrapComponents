<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use DOMDocument;
use DOMXPath;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Title\Title;
use PHPUnit\Framework\TestCase;

/**
 * Parses `<gallery mode="carousel">` with the wiki's own parser, so it needs File:Serenity.png and
 * File:Shuttle.png, which CI imports from tests/resources/.
 *
 * @covers  \MediaWiki\Extension\BootstrapComponents\CarouselGallery
 *
 * @group   extension-bootstrap-components
 */
class CarouselGalleryRenderingTest extends TestCase {

	private const SLIDE_IMAGE = '(//div[contains(@class, "carousel-item")]//img)[1]';

	public function testSlideImageTooltipIsTheCaption() {
		$html = $this->parseGallery( 'File:Serenity.png|Serenity caption' );

		$this->assertSame( 'Serenity caption', $this->getSlideImageTooltip( $html ) );
	}

	public function testSlideImageLinksToThePageGivenByLink() {
		$html = $this->parseGallery( 'File:Serenity.png|Internal|link=Main Page#Crew' );

		$this->assertSame(
			Title::newFromText( 'Main Page#Crew' )->getLinkURL(),
			$this->getSlideImageLink( $html )
		);
	}

	public function testSlideImageLinksToTheUrlGivenByLink() {
		$html = $this->parseGallery( 'File:Shuttle.png|External|link=https://example.org/x' );

		$this->assertSame( 'https://example.org/x', $this->getSlideImageLink( $html ) );
	}

	public function testSlideImageWithEmptyLinkIsNotClickable() {
		$html = $this->parseGallery( 'File:Serenity.png|Nolink|link=' );

		$this->assertTrue( $this->hasUnclickableSlideImage( $html ) );
	}

	private function parseGallery( string $galleryLine ): string {
		// the explicit id leaves the automatic carousel ids that CarouselGalleryTest expects untouched
		return MediaWikiServices::getInstance()->getParserFactory()->create()->parse(
			"<gallery mode=\"carousel\" id=\"crew\">\n$galleryLine\n</gallery>",
			Title::makeTitle( NS_MAIN, 'CarouselGalleryRenderingTest' ),
			ParserOptions::newFromAnon()
		)->getContentHolderText();
	}

	private function getSlideImageTooltip( string $html ): ?string {
		return $this->queryFirstValue( $html, self::SLIDE_IMAGE . '/ancestor-or-self::*[@title][1]/@title' );
	}

	private function getSlideImageLink( string $html ): ?string {
		return $this->queryFirstValue( $html, self::SLIDE_IMAGE . '/ancestor::a[1]/@href' );
	}

	private function hasUnclickableSlideImage( string $html ): bool {
		return $this->newXPath( $html )->evaluate( 'boolean(' . self::SLIDE_IMAGE . '[not(ancestor::*[self::a or @data-bs-toggle])])' );
	}

	private function queryFirstValue( string $html, string $expression ): ?string {
		$nodes = $this->newXPath( $html )->query( $expression );

		return $nodes->length ? $nodes->item( 0 )->nodeValue : null;
	}

	private function newXPath( string $html ): DOMXPath {
		$document = new DOMDocument();
		$document->loadHTML( $html, LIBXML_NOERROR );

		return new DOMXPath( $document );
	}

}
