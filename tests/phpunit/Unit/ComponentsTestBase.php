<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use MediaWiki\Extension\BootstrapComponents\ComponentLibrary;
use MediaWiki\Extension\BootstrapComponents\NestingController;
use MediaWiki\Extension\BootstrapComponents\ParserOutputHelper;
use MediaWiki\Extension\BootstrapComponents\ParserRequest;
use PHPUnit\Framework\TestCase;
use Parser;
use PPFrame;

/**
 * @group extension-bootstrap-components
 * @group mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   1.0
 * @author  Tobias Oetterer
 */
abstract class ComponentsTestBase extends TestCase {
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
	private NestingController $nestingController;

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
		$this->frame = $this->createMock( PPFrame::class );
		$this->nestingController = $this->createMock( NestingController::class );
		$this->nestingController->expects( $this->any() )
			->method( 'generateUniqueId' )
			->will( $this->returnCallback( function( $componentName ) {
				return 'bsc_' . $componentName . '_NULL';
			} ) );
		$this->parser = $this->createMock( Parser::class );
		$this->parser->expects( $this->any() )
			->method( 'recursiveTagParse' )
			->will( $this->returnArgument( 0 ) );
		$this->parser->expects( $this->any() )
			->method( 'recursiveTagParseFully' )
			->will( $this->returnArgument( 0 ) );
		$this->parserOutputHelper = $this->createMock( ParserOutputHelper::class );
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
		$parserRequest = $this->createMock( ParserRequest::class );
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
