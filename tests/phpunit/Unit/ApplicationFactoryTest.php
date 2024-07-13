<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use MediaWiki\Extension\BootstrapComponents\ApplicationFactory;
use MediaWiki\Extension\BootstrapComponents\ModalBuilder;
use MediaWiki\Extension\BootstrapComponents\ParserOutputHelper;
use MediaWiki\Extension\BootstrapComponents\ParserRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\ApplicationFactory
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
class ApplicationFactoryTest extends TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			ApplicationFactory::class,
			new ApplicationFactory()
		);
	}

	/**
	 * @param string $application
	 *
	 * @dataProvider applicationNameProvider
	 */
	public function testGetApplicationAndReset( $application ) {
		$instance = new ApplicationFactory();
		$this->assertInstanceOf(
			'MediaWiki\\Extension\\BootstrapComponents\\' . $application,
			call_user_func( [ $instance, 'get' . $application ] )
		);
		$this->assertTrue(
			$instance->resetLookup( $application )
		);
		// again
		$this->assertInstanceOf(
			'MediaWiki\\Extension\\BootstrapComponents\\' . $application,
			call_user_func( [ $instance, 'get' . $application ] )
		);
		// and again
		$this->assertInstanceOf(
			'MediaWiki\\Extension\\BootstrapComponents\\' . $application,
			call_user_func( [ $instance, 'get' . $application ] )
		);
	}

	public function testGetNewAttributeManager() {
		$instance = new ApplicationFactory();

		$this->assertInstanceOf(
			'MediaWiki\\Extension\\BootstrapComponents\\AttributeManager',
			$instance->getNewAttributeManager( [], [] )
		);
	}

	public function testGetNewModalBuilder() {
		$parserOutputHelper = $this->createMock( ParserOutputHelper::class );

		$factory = new ApplicationFactory();

		$modalBuilder = $factory->getNewModalBuilder( '', '', '', $parserOutputHelper );

		$this->assertInstanceOf(
			ModalBuilder::class,
			$modalBuilder
		);
	}

	/**
	 * @param array $arguments
	 * @param bool  $isParserFunction
	 *
	 * @throws \MWException
	 *
	 * @dataProvider parserRequestProvider
	 */
	public function testGetNewParserRequest( $arguments, $isParserFunction ) {
		$instance = new ApplicationFactory();

		$this->assertInstanceOf(
			ParserRequest::class,
			$instance->getNewParserRequest( $arguments, $isParserFunction )
		);
	}

	/**
	 * @throws \MWException
	 */
	public function testGetParserOutputHelper() {
		$instance = new ApplicationFactory();

		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$this->assertInstanceOf(
			ParserOutputHelper::class,
			$instance->getParserOutputHelper( $parser )
		);
	}

	/**
	 * @param array $arguments
	 * @param bool  $isParserFunction
	 *
	 * @dataProvider parserRequestFailureProvider
	 */
	public function testFailingGetNewParserRequest( array $arguments, bool $isParserFunction ) {
		$instance = new ApplicationFactory();

		$this->expectException( 'MWException' );

		$instance->getNewParserRequest( $arguments, $isParserFunction );
	}

	/**
	 * @throws \MWException
	 */
	public function testCanRegisterApplication() {
		$instance = new ApplicationFactory();
		$this->assertTrue(
			$instance->registerApplication( 'test', 'ReflectionClass' )
		);
	}

	/**
	 * @throws \MWException
	 */
	public function testCanNotRegisterApplicationOnInvalidName() {
		$instance = new ApplicationFactory();
		$this->assertTrue(
			!$instance->registerApplication( '', 'ReflectionClass' )
		);
		$this->assertTrue(
			!$instance->registerApplication( '   ', 'ReflectionClass' )
		);
	}

	public function testCanNotRegisterApplicationOnInvalidClass() {
		$instance = new ApplicationFactory();
		$this->expectException( 'MWException' );
		$instance->registerApplication( 'test', 'FooBar' );
	}

	public function testCanResetLookup() {
		$instance = new ApplicationFactory();
		$this->assertTrue(
			$instance->resetLookup()
		);
		$this->assertTrue(
			!$instance->resetLookup( 'hasBeenReset' )
		);
	}

	/**
	 * Why have this provider? once there were more applications that could be requested from ApplicationFactory.
	 *
	 * @return array[]
	 */
	public function applicationNameProvider(): array {
		return [
			'ParserOutputHelper' => [ 'ParserOutputHelper' ],
		];
	}

	/**
	 * @return array[]
	 */
	public function parserRequestProvider(): array {
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$frame = $this->getMockBuilder( 'PPFrame' )
			->disableOriginalConstructor()
			->getMock();
		return [
			'simpleTE' => [
				[ 'input', [], $parser, $frame ],
				false,
			],
			'simplePF' => [
				[ $parser, 'input', 'class=test' ],
				true,
			],
		];
	}

	/**
	 * @return array[]
	 */
	public function parserRequestFailureProvider(): array {
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$frame = $this->getMockBuilder( 'PPFrame' )
			->disableOriginalConstructor()
			->getMock();
		return [
			'wrongHandlerType PF instead of TE' => [
				[ 'input', [], $parser, $frame ],
				true,
			],
			'emptyPF'                           => [
				[],
				true,
			],
			'Parser Function no parser'         => [
				[ '1', '2', '3' ],
				true,
			],
			'Tag Extensions no parser'          => [
				[ '1', '2', '3', '4' ],
				false,
			],
			'Tag Extensions wrong #of args'     => [
				[ '1', '2', $parser ],
				false,
			],
		];
	}
}
