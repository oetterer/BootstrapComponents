<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ModalBuilder;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\ModalBuilder
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
class ModalBuilderTest extends PHPUnit_Framework_TestCase {

	public function testCanConstruct() {
		$parserOutputHelper = $this->getMockBuilder( 'BootstrapComponents\\ParserOutputHelper' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$instance = new ModalBuilder( 'id', 'trigger', '', $parserOutputHelper );
		$this->assertInstanceOf(
			'BootstrapComponents\\ModalBuilder',
			$instance
		);
	}

	/**
	 * @param string $id
	 * @param string $trigger
	 * @param string $content
	 * @param string $header
	 * @param string $footer
	 * @param string $outerClass
	 * @param string $outerStyle
	 * @param string $innerClass
	 * @param string $expectedTrigger
	 * @param string $expectedModal
	 *
	 * @dataProvider parseDataProvider
	 */
	public function testCanParse( $id, $trigger, $content, $header, $footer, $outerClass, $outerStyle, $innerClass, $expectedTrigger, $expectedModal ) {

		$modalInjection = '';
		$parserOutputHelper = $this->getMockBuilder( 'BootstrapComponents\\ParserOutputHelper' )
			->disableOriginalConstructor()
			->getMock();
		$parserOutputHelper->expects( $this->any() )
			->method( 'injectLater' )
			->will( $this->returnCallback( function( $id, $text ) use ( &$modalInjection ) {
				$modalInjection .= $text;
			} ) );

		/** @noinspection PhpParamsInspection */
		$instance = new ModalBuilder( $id, $trigger, $content, $parserOutputHelper );
		if ( $header ) {
			$instance->setHeader( $header );
		}
		if ( $footer ) {
			$instance->setFooter( $footer );
		}
		if ( $outerClass ) {
			$instance->setOuterClass( $outerClass );
		}
		if ( $outerStyle ) {
			$instance->setOuterStyle( $outerStyle );
		}
		if ( $innerClass ) {
			$instance->setDialogClass( $innerClass );
		}
		$this->assertEquals(
			$expectedTrigger,
			$instance->parse()
		);
		$this->assertEquals(
			$expectedModal,
			$modalInjection
		);
	}

	/**
	 * @return array
	 */
	public function parseDataProvider() {
		return [
			'all'    => [
				'id0',
				'trigger0',
				'content0',
				'header0',
				'footer0',
				'outerClass0',
				'outerStyle0',
				'innerClass0',
				'<span class="modal-trigger" data-toggle="modal" data-target="#id0">trigger0</span>',
				'<div class="modal fade outerClass0" style="outerStyle0" role="dialog" id="id0" aria-hidden="true"><div class="modal-dialog innerClass0"><div class="modal-content"><div class="modal-header"><span class="modal-title">header0</span>'
				. '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body">content0</div><div class="modal-footer">footer0<button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div></div></div></div>' . "\n",
			],
			'scarce' => [
				'id1',
				'trigger1',
				'content1',
				'',
				'',
				'',
				'',
				'',
				'<span class="modal-trigger" data-toggle="modal" data-target="#id1">trigger1</span>',
				'<div class="modal fade" role="dialog" id="id1" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close">'
				. '<span aria-hidden="true">&times;</span></button></div><div class="modal-body">content1</div><div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div></div></div></div>' . "\n",
			],
		];
	}
}
