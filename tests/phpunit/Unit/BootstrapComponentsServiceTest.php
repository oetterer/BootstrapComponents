<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use MediaWiki\Config\Config;
use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
use MediaWiki\Extension\BootstrapComponents\Tests\Fixtures\TestConfig;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService
 *
 * @ingroup Test
 *
 * @group extension-bootstrap-components
 * @group mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   5.2
 * @author  Tobias Oetterer
 */

class BootstrapComponentsServiceTest extends TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			BootstrapComponentsService::class,
			new BootstrapComponentsService( $this->getMockBuilder( Config::class )->getMock() )
		);
	}


	public function testCanGetNameOfActiveSkin() {
		$instance = new BootstrapComponentsService( $this->getMockBuilder( Config::class )->getMock() );

		$this->assertEquals(
			'vector-2022',
			$instance->getNameOfActiveSkin()
		);
	}

	public function testRegisterComponentAsActive() {
		$instance = new BootstrapComponentsService( $this->getMockBuilder( Config::class )->getMock() );

		$instance->registerComponentAsActive( 'Foo' );
		$instance->registerComponentAsActive( 'Bar' );
		$instance->registerComponentAsActive( 'modal' );

		$this->assertEquals(
			['Foo', 'Bar', 'modal'],
			$instance->getActiveComponents()
		);
	}

	public function testVectorSkinInUse() {
		$instance = new BootstrapComponentsService( $this->getMockBuilder( Config::class )->getMock() );
		$this->assertIsBool( $instance->vectorSkinInUse() );
	}

	/**
	 * @dataProvider skinProvidesBootstrapScriptsProvider
	 */
	public function testSkinProvidesBootstrapScripts( string $skin, bool $expected ) {
		$instance = new BootstrapComponentsService( $this->getMockBuilder( Config::class )->getMock() );
		$this->assertSame( $expected, $instance->skinProvidesBootstrapScripts( $skin ) );
	}

	public static function skinProvidesBootstrapScriptsProvider(): array {
		return [
			'medik ships its own Bootstrap' => [ 'medik', true ],
			'tweeki ships its own Bootstrap' => [ 'tweeki', true ],
			'vector does not' => [ 'vector', false ],
			'vector-2022 does not' => [ 'vector-2022', false ],
			'chameleon uses Extension:Bootstrap scripts' => [ 'chameleon', false ],
			'monobook does not' => [ 'monobook', false ],
			'unknown skin does not' => [ 'serenity', false ],
		];
	}

	/**
	 * @dataProvider skinProvidesBootstrapStylesProvider
	 */
	public function testSkinProvidesBootstrapStyles( string $skin, bool $expected ) {
		$instance = new BootstrapComponentsService( $this->getMockBuilder( Config::class )->getMock() );
		$this->assertSame( $expected, $instance->skinProvidesBootstrapStyles( $skin ) );
	}

	public static function skinProvidesBootstrapStylesProvider(): array {
		return [
			'chameleon registers its own copy of the stylesheet' => [ 'chameleon', true ],
			'medik ships its own stylesheet' => [ 'medik', true ],
			'tweeki ships its own stylesheet' => [ 'tweeki', true ],
			'vector does not' => [ 'vector', false ],
			'unknown skin does not' => [ 'serenity', false ],
		];
	}

	/**
	 * @dataProvider getBootstrapScriptsModuleProvider
	 */
	public function testGetBootstrapScriptsModule( string $skin, string $expected ) {
		$instance = new BootstrapComponentsService( $this->getMockBuilder( Config::class )->getMock() );
		$this->assertSame( $expected, $instance->getBootstrapScriptsModule( $skin ) );
	}

	public static function getBootstrapScriptsModuleProvider(): array {
		return [
			'medik uses its own skin module' => [ 'medik', 'skins.medik.js' ],
			'vector uses Extension:Bootstrap' => [ 'vector', 'ext.bootstrap.scripts' ],
			'chameleon uses Extension:Bootstrap' => [ 'chameleon', 'ext.bootstrap.scripts' ],
			'unknown skin falls back to Extension:Bootstrap' => [ 'serenity', 'ext.bootstrap.scripts' ],
		];
	}

	public function testGetBootstrapScriptsModuleForTweekiDefaultsToItsScriptModule() {
		$instance = new BootstrapComponentsService( new TestConfig() );
		$this->assertSame( 'skins.tweeki.scripts', $instance->getBootstrapScriptsModule( 'tweeki' ) );
	}

	public function testGetBootstrapScriptsModuleForTweekiTreatsFalseCustomModuleAsUnset() {
		$config = new TestConfig();
		$config->set( 'TweekiSkinCustomScriptModule', false );
		$config->set( 'TweekiSkinUseCustomFiles', false );
		$instance = new BootstrapComponentsService( $config );
		$this->assertSame( 'skins.tweeki.scripts', $instance->getBootstrapScriptsModule( 'tweeki' ) );
	}

	public function testGetBootstrapScriptsModuleForTweekiUsesCustomFilesModule() {
		$config = new TestConfig();
		$config->set( 'TweekiSkinUseCustomFiles', true );
		$instance = new BootstrapComponentsService( $config );
		$this->assertSame( 'skins.tweeki.custom.scripts', $instance->getBootstrapScriptsModule( 'tweeki' ) );
	}

	public function testGetBootstrapScriptsModuleForTweekiHonorsConfiguredScriptModule() {
		$config = new TestConfig();
		$config->set( 'TweekiSkinCustomScriptModule', 'skins.tweeki.my.scripts' );
		$config->set( 'TweekiSkinUseCustomFiles', true );
		$instance = new BootstrapComponentsService( $config );
		$this->assertSame( 'skins.tweeki.my.scripts', $instance->getBootstrapScriptsModule( 'tweeki' ) );
	}

	/**
	 * @throws ReflectionException
	 */
	public function testPrivateCanDetectSkinInUse() {
		$config = new TestConfig();
		$instance = new BootstrapComponentsService( $config );

		$reflection = new ReflectionClass( BootstrapComponentsService::class );
		$method = $reflection->getMethod( 'detectSkinInUse' );

		// this is default
		$this->assertEquals(
			'vector-2022',
			$method->invokeArgs( $instance, [ false ] )
		);

		$config->set( 'DefaultSkin', 'serenity' );
		// this was introduced due to issue #9
		$this->assertEquals(
			'serenity',
			$method->invokeArgs( $instance, [ true ] )
		);
		$config->reset();
	}
}
