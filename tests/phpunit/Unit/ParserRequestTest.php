<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ParserRequest;
use \PHPUnit_Framework_TestCase;
use \Parser;
use \PPFrame;

/**
 * @covers  \BootstrapComponents\ParserRequest
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
class ParserRequestTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var PPFrame
	 */
	private $frame;

	/**
	 * @var Parser
	 */
	private $parser;

	public function setUp(): void {
		parent::setUp();
		$this->frame = $this->getMockBuilder( 'PPFrame' )
			->disableOriginalConstructor()
			->getMock();
		$this->parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
	}

	/**
	 * @param array $arguments
	 * @param bool  $isParserFunction
	 *
	 * @throws \MWException
	 *
	 * @dataProvider constructionProvider
	 */
	public function testCanConstruct( $arguments, $isParserFunction ) {

		$this->assertInstanceOf(
			'BootstrapComponents\\ParserRequest',
			new ParserRequest( $arguments, $isParserFunction )
		);
	}

	/**
	 * @param array $arguments
	 * @param bool  $isParserFunction
	 *
	 * @expectedException \MWException
	 *
	 * @dataProvider constructionFailsProvider
	 */
	public function testCanNotConstruct( $arguments, $isParserFunction ) {

		if ( method_exists( $this, 'expectException' ) ) {
			$this->expectException( 'MWException' );
		} else {
			$this->setExpectedException( 'MWException' );
		}

		$this->assertInstanceOf(
			'BootstrapComponents\\ParserRequest',
			new ParserRequest( $arguments, $isParserFunction )
		);
	}

	/**
	 * @param array  $arguments
	 * @param bool   $isParserFunction
	 * @param string $expectedInput
	 * @param array  $expectedAttributes
	 *
	 * @throws \MWException
	 *
	 * @dataProvider constructionProvider
	 */
	public function testGetAttributesAndInput( $arguments, $isParserFunction, $expectedInput, $expectedAttributes ) {
		$instance = new ParserRequest( $arguments, $isParserFunction );

		$this->assertEquals(
			$expectedInput,
			$instance->getInput()
		);

		$this->assertEquals(
			$expectedAttributes,
			$instance->getAttributes()
		);

		$this->assertInstanceOf(
			'Parser',
			$instance->getParser()
		);

		if ( $isParserFunction ) {
			$this->assertInternalType(
				'null',
				$instance->getFrame()
			);
		} else {
			$this->assertInstanceOf(
				'PPFrame',
				$instance->getFrame()
			);
		}
	}

	/**
	 * @return array[]
	 */
	public function constructionProvider() {
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$frame = $this->getMockBuilder( 'PPFrame' )
			->disableOriginalConstructor()
			->getMock();
		$inputText = 'input';
		return [
			'pf'          => [
				[ $parser, $inputText ],
				true,
				$inputText,
				[]
			],
			'te'          => [
				[ $inputText, [], $parser, $frame ],
				false,
				$inputText,
				[]
			],
			'pf many'     => [
				[ $parser, $inputText, 'attr1=1', 'attr2=2', 'attr3=3', 'single', ],
				true,
				$inputText,
				[ 'attr1' => '1', 'attr2' => '2', 'attr3' => '3', 'single' => true, ],
			],
			'te many'     => [
				[ $inputText, [ 'attr1' => '1', 'attr2' => '2', 'attr3' => '3', 'single' => true, ], $parser, $frame ],
				false,
				$inputText,
				[ 'attr1' => '1', 'attr2' => '2', 'attr3' => '3', 'single' => true, ],
			],
			'pf no input' => [
				[ $parser, '', '', 'attr1=1', 'attr2=2', 'attr3=3', ],
				true,
				'',
				[ 'attr1' => '1', 'attr2' => '2', 'attr3' => '3', ],
			],
			'te no input' => [
				[ '', [ 'attr1' => '1', 'attr2' => '2', 'attr3' => '3', ], $parser, $frame ],
				false,
				'',
				[ 'attr1' => '1', 'attr2' => '2', 'attr3' => '3', ],
			],
		];
	}

	/**
	 * @return array[]
	 */
	public function constructionFailsProvider() {
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$frame = $this->getMockBuilder( 'PPFrame' )
			->disableOriginalConstructor()
			->getMock();
		return [
			'pf'  => [ [ null, 'input' ], true ],
			'pf0' => [ [ null ], true ],
			'pf1' => [ [ $parser, '', false ], true ],
			'te'  => [ [ 'input', [], null, $frame ], false ],
			'te1' => [ [ 'input', [ false ], $parser ], false ],
			'te2' => [ [ 'input', [ 13 ], $parser ], false ],
			'te3' => [ [ 'input', [], $parser ], false ],
		];
	}
}
