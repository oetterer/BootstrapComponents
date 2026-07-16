<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit\Hooks;

use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
use MediaWiki\Extension\BootstrapComponents\Hooks\OutputPageParserOutput;
use MediaWiki\Output\OutputPage;
use PHPUnit\Framework\TestCase;
use Skin;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\Hooks\OutputPageParserOutput
 *
 * @ingroup Test
 *
 * @group   extension-bootstrap-components
 * @group   mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   1.2
 * @author  Tobias Oetterer
 */
class OutputPageParserOutputTest extends TestCase {

	public function testCanConstruct() {

		$outputPage = $this->createMock( OutputPage::class );

		$instance = new OutputPageParserOutput(
			$outputPage,
			$this->createMock( BootstrapComponentsService::class )
		);

		$this->assertInstanceOf(
			OutputPageParserOutput::class,
			$instance
		);
	}

	public function testAddsBootstrapStylesForSkinWithoutOwnBootstrap() {
		$outputPage = $this->newOutputPageForSkin( 'monobook' );

		$this->process( $outputPage, vectorSkinInUse: false );

		$this->assertContains( 'ext.bootstrap.styles', $outputPage->getModuleStyles() );
	}

	public function testAddsBootstrapScriptsForSkinWithoutOwnBootstrap() {
		$outputPage = $this->newOutputPageForSkin( 'monobook' );

		$this->process( $outputPage, vectorSkinInUse: false );

		$this->assertContains( 'ext.bootstrap.scripts', $outputPage->getModules() );
	}

	/**
	 * @dataProvider skinLoadingBootstrapItselfProvider
	 */
	public function testOmitsBootstrapStylesForSkinLoadingBootstrapItself( string $skinName ) {
		$outputPage = $this->newOutputPageForSkin( $skinName );

		$this->process( $outputPage, vectorSkinInUse: false );

		$this->assertNotContains( 'ext.bootstrap.styles', $outputPage->getModuleStyles() );
	}

	/**
	 * @dataProvider skinLoadingBootstrapItselfProvider
	 */
	public function testOmitsBootstrapScriptsForSkinLoadingBootstrapItself( string $skinName ) {
		$outputPage = $this->newOutputPageForSkin( $skinName );

		$this->process( $outputPage, vectorSkinInUse: false );

		$this->assertNotContains( 'ext.bootstrap.scripts', $outputPage->getModules() );
	}

	public static function skinLoadingBootstrapItselfProvider(): array {
		return [
			'chameleon' => [ 'chameleon' ],
			'medik' => [ 'medik' ],
			'tweeki' => [ 'tweeki' ],
		];
	}

	public function testHookOutputPageParserOutputLoadsVectorFixUnderVector() {
		$outputPage = $this->newOutputPageForSkin( 'vector' );

		$this->process( $outputPage, vectorSkinInUse: true );

		$this->assertContains( 'ext.bootstrapComponents.vector-fix', $outputPage->getModules() );
	}

	public function testOmitsVectorFixWhenVectorIsNotInUse() {
		$outputPage = $this->newOutputPageForSkin( 'monobook' );

		$this->process( $outputPage, vectorSkinInUse: false );

		$this->assertNotContains( 'ext.bootstrapComponents.vector-fix', $outputPage->getModules() );
	}

	private function newOutputPageForSkin( string $skinName ): OutputPage {
		$skin = $this->createMock( Skin::class );
		$skin->method( 'getSkinName' )->willReturn( $skinName );

		$context = new RequestContext();
		$context->setSkin( $skin );

		return new OutputPage( $context );
	}

	private function process( OutputPage $outputPage, bool $vectorSkinInUse ): void {
		$bootstrapService = $this->createMock( BootstrapComponentsService::class );
		$bootstrapService->method( 'vectorSkinInUse' )->willReturn( $vectorSkinInUse );

		$instance = new OutputPageParserOutput( $outputPage, $bootstrapService );
		$instance->process();
	}
}
