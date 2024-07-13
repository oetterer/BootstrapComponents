<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use MediaWiki\Extension\BootstrapComponents\ComponentLibrary;
use MediaWiki\Extension\BootstrapComponents\ParserOutputHelper;
use \MWException;
// TODO: when dropping 1.39, switch to MediaWiki\Parser\Parser and MediaWiki\Parser\ParserOutput
use Parser;
use ParserOutput;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\ParserOutputHelper
 *
 * @ingroup Test
 *
 * @group extension-bootstrap-components
 * @group mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   1.0
 * @author  Tobias Oetterer
 */
class ParserOutputHelperTest extends TestCase {
	/**
	 * @var Parser
	 */
	private Parser $parser;

	/**
	 * @var ParserOutput
	 */
	private $parserOutput;

	public function setUp(): void {
		parent::setUp();

		$this->parserOutput = new ParserOutput( 'ParserOutputMockText' );

		$this->parser = $this->createMock( 'Parser' );

		$this->parser->expects( $this->any() )
			->method( 'getOutput' )
			->willReturn( $this->parserOutput );
	}

	public function testCanConstruct() {

		$this->assertInstanceOf(
			ParserOutputHelper::class,
			new ParserOutputHelper( $this->parser )
		);
	}

	public function testCanAddErrorTrackingCategory() {

		$parser = $this->createMock( 'Parser' );
		$parser->expects( $this->once() )
			->method( 'getOutput' )
			->willReturn( false );

		$instance = new ParserOutputHelper( $parser );

		$instance->addErrorTrackingCategory();
		$instance->addErrorTrackingCategory();
	}

	public function testCanAddTrackingCategory() {
		$parser = $this->createMock( 'Parser' );
		$parser->expects( $this->once() )
			->method( 'getOutput' )
			->willReturn( false );

		$instance = new ParserOutputHelper( $parser );

		$instance->addTrackingCategory();
		$instance->addTrackingCategory();
	}

	/**
	 * @param string $messageText
	 * @param string $renderedMessageRegExp
	 *
	 * @dataProvider errorMessageProvider
	 */
	public function testCanRenderErrorMessage( $messageText, string $renderedMessageRegExp ) {
		$instance = new ParserOutputHelper(
			$this->buildFullyEquippedParser( ( $renderedMessageRegExp != '~^$~' ) )
		);

		// TODO when we drop support for MW1.39
		if ( version_compare( $GLOBALS['wgVersion'], '1.40', 'lt' ) ) {
			$this->assertRegExp( $renderedMessageRegExp, $instance->renderErrorMessage( $messageText ) );
		} else {
			$this->assertMatchesRegularExpression(
				$renderedMessageRegExp,
				$instance->renderErrorMessage( $messageText )
			);
		}

	}

	/**
	 * @return array[]
	 *
	 * @throws \ConfigException
	 * @throws MWException
	 */
	public function componentNameAndClassProvider() {
		$cl = new ComponentLibrary();
		$provider = [];
		foreach ( $cl->getRegisteredComponents() as $componentName ) {
			$provider['open ' . $componentName] = [ $componentName, $cl->getClassFor( $componentName ) ];
		}
		return $provider;
	}

	/**
	 * @return array[]
	 */
	public function errorMessageProvider() {
		return [
			'none'       => [ '', '~^$~' ],
			'empty'      => [ '      ', '~^$~' ],
			'word'       => [ '__rndErrorMessageTextNotInMessageFiles', '~^<span class="error">[^_]+__rndErrorMessageTextNotInMessageFiles[^<]+</span>$~' ],
			'word space' => [ '  __rndErrorMessageTextNotInMessageFiles  ', '~^<span class="error">[^_]+__rndErrorMessageTextNotInMessageFiles[^<]+</span>$~' ],
		];
	}



	/**
	 * @param bool $expectError
	 *
	 * @return Parser
	 */
	private function buildFullyEquippedParser( $expectError = true ) {
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		if ( $expectError ) {
			$parserOutput = $this->getMockBuilder( 'ParserOutput' )
				->disableOriginalConstructor()
				->getMock();
			$parserOutput->expects( $this->once() )
				->method( 'addCategory' )
				->with(
					$this->equalTo( 'Pages_with_bootstrap_component_errors' )
				);
			$parser->expects( $this->once() )
				->method( 'getOutput' )
				->willReturn( $parserOutput );
		}

		return $parser;
	}
}
