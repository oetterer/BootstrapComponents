<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use Config;
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
			'vector',
			$instance->getNameOfActiveSkin()
		);
	}

	public function testRegisterModules() {
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
	 * @throws ReflectionException
	 */
	public function testPrivateCanDetectSkinInUse() {
		$config = new TestConfig();
		$instance = new BootstrapComponentsService( $config );

		$reflection = new ReflectionClass( BootstrapComponentsService::class );
		$method = $reflection->getMethod( 'detectSkinInUse' );

		// this is default
		$this->assertEquals(
			'vector',
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
