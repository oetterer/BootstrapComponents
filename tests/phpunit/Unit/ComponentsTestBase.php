<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ComponentLibrary;
use BootstrapComponents\NestingController;
use BootstrapComponents\ParserOutputHelper;
use BootstrapComponents\ParserRequest;
use \PHPUnit_Framework_TestCase;
use \Parser;
use \PPFrame;

/**
 * @group extension-bootstrap-components
 * @group mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   1.0
 * @author  Tobias Oetterer
 */
abstract class ComponentsTestBase extends PHPUnit_Framework_TestCase {
	/**
	 * @var ComponentLibrary
	 */
	private $componentLibrary;

	/**
	 * @var PPFrame
	 */
	private $frame;

	/**
	 * @var NestingController
	 */
	private $nestingController;

	/**
	 * @var Parser
	 */
	private $parser;

	/**
	 * @var ParserOutputHelper
	 */
	private $parserOutputHelper;

	/**
	 * @throws \ConfigException
	 */
	public function setUp(): void {
		parent::setUp();
		$this->componentLibrary = new ComponentLibrary();
		$this->frame = $this->getMockBuilder( 'PPFrame' )
			->disableOriginalConstructor()
			->getMock();
		$this->nestingController = $this->getMockBuilder( 'BootstrapComponents\\NestingController' )
			->disableOriginalConstructor()
			->getMock();
		$this->nestingController->expects( $this->any() )
			->method( 'generateUniqueId' )
			->will( $this->returnCallback( function( $componentName ) {
				return 'bsc_' . $componentName . '_NULL';
			} ) );
		$this->parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$this->parser->expects( $this->any() )
			->method( 'recursiveTagParse' )
			->will( $this->returnArgument( 0 ) );
		$this->parser->expects( $this->any() )
			->method( 'recursiveTagParseFully' )
			->will( $this->returnArgument( 0 ) );
		$this->parserOutputHelper = $this->getMockBuilder( 'BootstrapComponents\\ParserOutputHelper' )
			->disableOriginalConstructor()
			->getMock();
		$this->parserOutputHelper->expects( $this->any() )
			->method( 'renderErrorMessage' )
			->will( $this->returnArgument( 0 ) );
	}

	/**
	 * Builds a mock ParserRequest object to be used in placeMe methods
	 *
	 * @param string $input
	 * @param array  $options
	 *
	 * @return ParserRequest
	 */
	protected function buildParserRequest( $input, $options ) {
		$parserRequest = $this->getMockBuilder( 'BootstrapComponents\\ParserRequest' )
			->disableOriginalConstructor()
			->getMock();
		$parserRequest->expects( $this->any() )
			->method( 'getAttributes' )
			->willReturn( $options );
		$parserRequest->expects( $this->any() )
			->method( 'getFrame' )
			->willReturn( $this->getFrame() );
		$parserRequest->expects( $this->any() )
			->method( 'getInput' )
			->willReturn( $input );
		$parserRequest->expects( $this->any() )
			->method( 'getParser' )
			->willReturn( $this->getParser() );
		return $parserRequest;
	}

	/**
	 * @return ComponentLibrary
	 */
	protected function getComponentLibrary() {
		return $this->componentLibrary;
	}

	/**
	 * @return PPFrame
	 */
	protected function getFrame() {
		return $this->frame;
	}

	/**
	 * @return NestingController
	 */
	protected function getNestingController() {
		return $this->nestingController;
	}

	/**
	 * @return Parser
	 */
	protected function getParser() {
		return $this->parser;
	}

	/**
	 * @return ParserOutputHelper
	 */
	protected function getParserOutputHelper() {
		return $this->parserOutputHelper;
	}
}
