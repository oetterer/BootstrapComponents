<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit\ResourceLoader;

use MediaWiki\Extension\BootstrapComponents\ResourceLoader\BootstrapDependentModule;
use MediaWiki\ResourceLoader\Context;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MediaWiki\Extension\BootstrapComponents\ResourceLoader\BootstrapDependentModule
 *
 * @group extension-bootstrap-components
 * @group mediawiki-databaseless
 *
 * @license GNU GPL v3+
 */
class BootstrapDependentModuleTest extends TestCase {

	public function testAddsTheSkinsOwnBootstrapModuleWhenTheSkinShipsOne() {
		$module = $this->newModuleWithDependencies( [ 'some.other.module' ] );

		$dependencies = $module->getDependencies( $this->newContextForSkin( 'medik' ) );

		$this->assertContains( 'skins.medik.js', $dependencies );
		$this->assertNotContains( 'ext.bootstrap.scripts', $dependencies );
		$this->assertContains( 'some.other.module', $dependencies );
	}

	/**
	 * @dataProvider skinWithoutReachableOwnBootstrapProvider
	 */
	public function testAddsExtBootstrapScriptsForOtherSkins( string $skin ) {
		$module = $this->newModuleWithDependencies( [] );

		$this->assertSame(
			[ 'ext.bootstrap.scripts' ],
			$module->getDependencies( $this->newContextForSkin( $skin ) )
		);
	}

	public static function skinWithoutReachableOwnBootstrapProvider(): array {
		return [
			'vector' => [ 'vector' ],
			'chameleon' => [ 'chameleon' ],
			'tweeki' => [ 'tweeki' ],
			'monobook' => [ 'monobook' ],
		];
	}

	public function testAddsExtBootstrapScriptsWhenNoSkinContext() {
		$module = $this->newModuleWithDependencies( [] );

		$this->assertSame( [ 'ext.bootstrap.scripts' ], $module->getDependencies() );
	}

	private function newModuleWithDependencies( array $dependencies ): BootstrapDependentModule {
		return new BootstrapDependentModule( [ 'dependencies' => $dependencies ] );
	}

	private function newContextForSkin( string $skin ): Context {
		$context = $this->createMock( Context::class );
		$context->method( 'getSkin' )->willReturn( $skin );
		return $context;
	}
}
