<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Title\Title;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\AttributeManager
 *
 * @group   extension-bootstrap-components
 */
class ComponentStyleRenderingTest extends TestCase {

	public function testCssThatMediaWikiBlocksIsNotRenderedAfterTemplateExpansion() {
		$html = $this->parse( '<bootstrap_alert style="background:{{lc:URL}}(//example.org/a.png)">Text</bootstrap_alert>' );

		$this->assertStringNotContainsString( 'example.org', $html );
	}

	public function testOrdinaryCssIsRendered() {
		$html = $this->parse( '<bootstrap_alert style="width: 300px;">Text</bootstrap_alert>' );

		$this->assertStringContainsString( '300px', $html );
	}

	private function parse( string $wikitext ): string {
		return MediaWikiServices::getInstance()->getParserFactory()->create()->parse(
			$wikitext,
			Title::makeTitle( NS_MAIN, 'ComponentStyleRenderingTest' ),
			ParserOptions::newFromAnon()
		)->getContentHolderText();
	}

}
