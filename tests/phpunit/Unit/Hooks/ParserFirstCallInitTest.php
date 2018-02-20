<?php

namespace BootstrapComponents\Tests\Unit\Hooks;

use BootstrapComponents\Hooks\ParserFirstCallInit as ParserFirstCallInit;
use BootstrapComponents\ComponentLibrary;
use \Parser;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\Hooks\ParserFirstCallInit
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
class ParserFirstCallInitTest extends PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$componentLibrary = $this->getMockBuilder( 'BootstrapComponents\ComponentLibrary' )
			->disableOriginalConstructor()
			->getMock();
		$nestingController = $this->getMockBuilder( 'BootstrapComponents\NestingController' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$instance = new ParserFirstCallInit( $parser, $componentLibrary, $nestingController );

		$this->assertInstanceOf(
			'BootstrapComponents\\Hooks\\ParserFirstCallInit',
			$instance
		);
	}

	/**
	 * @throws \ConfigException
	 */
	public function testHookParserFirstCallInit() {
		$prefix = ComponentLibrary::PARSER_HOOK_PREFIX;
		$observerParser = $this->getMockBuilder(Parser::class )
			->disableOriginalConstructor()
			->setMethods( [ 'setFunctionHook', 'setHook' ] )
			->getMock();
		$observerParser->expects( $this->exactly( 6 ) )
			->method( 'setFunctionHook' )
			->withConsecutive(
				[ $this->equalTo( $prefix . 'badge' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'button' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'carousel' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'icon' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'label' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'tooltip' ), $this->callback( 'is_callable' ) ]
			);
		$observerParser->expects( $this->exactly( 8 ) )
			->method( 'setHook' )
			->withConsecutive(
				[ $this->equalTo( $prefix . 'accordion' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'alert' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'collapse' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'jumbotron' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'modal' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'panel' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'popover' ), $this->callback( 'is_callable' ) ],
				[ $this->equalTo( $prefix . 'well' ), $this->callback( 'is_callable' ) ]
			);
		$componentLibrary = new ComponentLibrary( true );
		$nestingController = $this->getMockBuilder( 'BootstrapComponents\NestingController' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$instance = new ParserFirstCallInit( $observerParser, $componentLibrary, $nestingController );

		$this->assertTrue(
			$instance->process()
		);
	}

	/**
	 * @throws \ConfigException
	 */
	public function testCanCreateParserHooks() {
		$registeredParserHooks = [];
		$extractionParser = $this->getMockBuilder(Parser::class )
			->disableOriginalConstructor()
			->setMethods( [ 'setFunctionHook', 'setHook' ] )
			->getMock();
		$extractionParser->expects( $this->exactly( 6 ) )
			->method( 'setFunctionHook' )
			->will( $this->returnCallback( function( $parserHookString, $callBack ) use ( &$registeredParserHooks ) {
				$registeredParserHooks[$parserHookString] = [ $callBack, ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION ];
			} ) );
		$extractionParser->expects( $this->exactly( 8 ) )
			->method( 'setHook' )
			->will( $this->returnCallback( function( $parserHookString, $callBack ) use ( &$registeredParserHooks ) {
				$registeredParserHooks[$parserHookString] = [ $callBack, ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ];
			} ) );

		$componentLibrary = new ComponentLibrary( true );
		$nestingController = $this->getMockBuilder( 'BootstrapComponents\NestingController' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$instance = new ParserFirstCallInit( $extractionParser, $componentLibrary, $nestingController );

		$this->assertTrue(
			$instance->process()
		);

		$this->assertEquals(
			14,
			count( $registeredParserHooks )
		);

		foreach ( $registeredParserHooks as $registeredParserHook => $data ) {
			$this->doTestParserHook( $registeredParserHook, $data[0], $data[1] );
		}
	}

	/**
	 * @param string   $registeredParserHook
	 * @param \Closure $callback
	 * @param string   $handlerType
	 */
	private function doTestParserHook( $registeredParserHook, $callback, $handlerType ) {
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$input = 'test';
		if ( $handlerType == ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ) {
			$ret = $callback( $input, [], $parser, null );
		} elseif ( $handlerType == ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION ) {
			$ret = $callback( $parser, $input );
		} else {
			$ret = false;
		}
		$this->assertInternalType(
			'string',
			$ret,
			'Failed testing parser hook for parser hook string ' . $registeredParserHook
		);
	}
}
