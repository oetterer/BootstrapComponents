<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use MediaWiki\Extension\BootstrapComponents\AbstractComponent;
use MediaWiki\Extension\BootstrapComponents\ComponentLibrary;
use MediaWiki\Extension\BootstrapComponents\NestableInterface;
use MWException;
use PHPUnit\Framework\MockObject\Stub;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\AbstractComponent
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
class AbstractComponentTest extends ComponentsTestBase {
	private $componentPlacing = '<component placing>';

	private $name = 'abstract';

	/**
	 * @return Stub
	 */
	protected function createStub( string $originalClassName ): Stub
	{
		$componentLibrary = $this->getMockBuilder( $originalClassName )
			->disableOriginalConstructor()
			->getMock();
		$componentLibrary->expects( $this->any() )
			->method( 'getNameFor' )
			->will( $this->returnValue( $this->name ) );
		$componentLibrary->expects( $this->any() )
			->method( 'getAttributesFor' )
			->will( $this->returnValue( [] ) );
		$componentLibrary->expects( $this->any() )
			->method( 'getAliasesFor' )
			->will( $this->returnValue( [] ) );

		$stub = $this->getMockForAbstractClass(
			AbstractComponent::class,
			[ $componentLibrary, $this->getParserOutputHelper(), $this->getNestingController() ]
		);
		$stub->expects( $this->any() )
			->method( 'placeMe' )
			->will( $this->returnValue( $this->componentPlacing ) );
		return $stub;
	}

	public function testCanConstruct() {
		$this->assertInstanceOf(
			AbstractComponent::class,
			$this->createStub(ComponentLibrary::class)
		);
		$this->assertInstanceOf(
			NestableInterface::class,
			$this->createStub(ComponentLibrary::class)
		);
	}

	public function testGetId() {
		$id = $this->createStub(ComponentLibrary::class)->getId();

		$this->assertEquals(
			null,
			$id
		);
	}

	/**
	 * @throws MWException
	 */
	public function testParseComponent() {
		$parserRequest = $this->buildParserRequest(
			'',
			[]
		);
		/** @noinspection PhpParamsInspection */
		$parsedString = $this->createStub(ComponentLibrary::class)->parseComponent(
			$parserRequest
		);

		$this->assertEquals(
			$this->componentPlacing,
			$parsedString
		);
	}

	/**
	 * @param string $component
	 *
	 * @throws MWException
	 * @dataProvider allComponentsProvider
	 */
	public function testSimpleOutput( string $component ) {
		$parserRequest = $this->buildParserRequest(
			'test input',
			[ 'class' => 'test-class', 'style' => 'color:black', 'text' => 'test text', 'header' => 'test header' ]
		);
		$class = $this->getComponentLibrary()->getClassFor( $component );
		/** @var AbstractComponent $instance */
		$instance = new $class(
			$this->getComponentLibrary(),
			$this->getParserOutputHelper(),
			$this->getNestingController()
		);
		/** @noinspection PhpParamsInspection */
		$parsedString = $instance->parseComponent(
			$parserRequest
		);
		if ( is_array( $parsedString ) ) {
			$parsedString = reset( $parsedString );
		}
		$this->assertIsString( $parsedString );
		$this->assertRegExp(
			'/class="[^"]*test-class"/',
			$parsedString
		);
		$this->assertRegExp(
			'/style="[^"]*color:black"/',
			$parsedString
		);
	}

	/**
	 * @param string $component
	 *
	 * @throws MWException
	 * @dataProvider allComponentsProvider
	 */
	public function testInvalidParserRequest( $component ) {
		$class = $this->getComponentLibrary()->getClassFor( $component );
		/** @var AbstractComponent $instance */
		$instance = new $class(
			$this->getComponentLibrary(),
			$this->getParserOutputHelper(),
			$this->getNestingController()
		);
		$this->expectException( 'MWException' );
		/** @noinspection PhpParamsInspection */
		$instance->parseComponent(
			'noParser'
		);
	}

	/**
	 * Only components, not returning an error message on an empty attribute be used here
	 *
	 * @return array
	 */
	public function allComponentsProvider() {
		return [
			'accordion' => [ 'accordion' ],
			'alert'     => [ 'alert' ],
			'badge'     => [ 'badge' ],
			'button'    => [ 'button' ],
			'card'      => [ 'card' ],
			'collapse'  => [ 'collapse' ],
			'jumbotron' => [ 'jumbotron' ],
			'label'     => [ 'label' ],
			'panel'     => [ 'panel' ],
			'popover'   => [ 'popover' ],
			'tooltip'   => [ 'tooltip' ],
			'well'      => [ 'well' ],
		];
	}
}
