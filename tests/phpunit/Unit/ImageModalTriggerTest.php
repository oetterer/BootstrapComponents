<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ImageModalTrigger;
use \MediaWiki\MediaWikiServices;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\ImageModalTrigger
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
class ImageModalTriggerTest extends PHPUnit_Framework_TestCase {

	public function setUp() {
		parent::setUp();
	}

	public function testCanConstruct() {

		$localFile = $this->getMockBuilder( 'LocalFile' )
			->disableOriginalConstructor()
			->getMock();

		$file = $this->getMockBuilder( 'File' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$this->assertInstanceOf(
			'BootstrapComponents\\ImageModalTrigger',
			new ImageModalTrigger(
				'id',
				$localFile
			)
		);
		/** @noinspection PhpParamsInspection */
		$this->assertInstanceOf(
			'BootstrapComponents\\ImageModalTrigger',
			new ImageModalTrigger(
				'id',
				$file
			)
		);
	}

	/**
	 * @param array  $sfp
	 * @param array  $hp
	 * @param string $expectedRegExp
	 *
	 * @dataProvider canGenerateProvider
	 * @throws \ConfigException
	 */
	public function testCanParse( $sfp, $hp, $expectedRegExp ) {

		$thumb = $this->getMockBuilder( 'ThumbnailImage' )
			->disableOriginalConstructor()
			->getMock();
		$thumb->expects( $this->any() )
			->method( 'getWidth' )
			->willReturn( 640 );
		$thumb->expects( $this->any() )
			->method( 'toHtml' )
			->will( $this->returnCallback(
				function( $params ) {
					$ret = [];
					foreach ( [ 'alt', 'title', 'img-class' ] as $itemToPrint ) {
						if ( isset( $params[$itemToPrint] ) && $params[$itemToPrint] ) {
							$ret[] = ($itemToPrint != 'img-class' ? $itemToPrint : 'class') . '="' . $params[$itemToPrint] . '"';
						}
					}
					return '<img src="thumbnail::toHtml()/return/value.png" ' . implode( ' ', $ret ) . '>';
				}
			) );
		$file = $this->getMockBuilder( 'LocalFile' )
			->disableOriginalConstructor()
			->getMock();
		$file->expects( $this->any() )
			->method( 'allowInlineDisplay' )
			->willReturn( true );
		$file->expects( $this->any() )
			->method( 'exists' )
			->willReturn( true );
		$file->expects( $this->any() )
			->method( 'getWidth' )
			->willReturn( 52 );
		$file->expects( $this->any() )
			->method( 'mustRender' )
			->willReturn( false );
		$file->expects( $this->any() )
			->method( 'getUnscaledThumb' )
			->willReturn( $thumb );
		$file->expects( $this->any() )
			->method( 'transform' )
			->willReturn( $thumb );

		/** @noinspection PhpParamsInspection */
		$instance = new ImageModalTrigger( 'id', $file );

		$resultOfParseCall = $instance->generate( $sfp, $hp );

		/** @noinspection PhpParamsInspection */
		$this->assertEquals(
			$expectedRegExp,
			$resultOfParseCall,
			'failed with test data:' . $this->generatePhpCodeForManualProviderDataOneCase( $sfp, $hp )
		);
	}

	/**
	 * @throws \ConfigException cascading {@see \Config::get}
	 * @return array
	 */
	public function canGenerateProvider() {
		$globalConfig = MediaWikiServices::getInstance()->getMainConfig();
		$scriptPath = $globalConfig->get( 'ScriptPath' );
		/*
		 * 1. Parameter: Sanitized frame parameter:
		 * All of these must be present and true or false:
		 * - thumbnail
		 * - framed
		 * - frameless
		 * - border
		 * All of these must be present and a string (can be empty)
		 * - align
		 * - alt
		 * - class
		 * - title
		 * - valign
		 *
		 * optional
		 * - manualthumb *when setting this, insert $scriptPath into parameter 3
		 * - upright
		 *
		 * 2. Parameter: handler parameter
		 * - width
		 * - page *must be present as integer or false*
		 *
		 * 3. Parameter: expected result string
		 */
		return [
			'no params'                      => [
				[
					'thumbnail' => false,
					'framed'    => false,
					'frameless' => false,
					'border'    => false,
					'align'     => '',
					'alt'       => '',
					'caption'   => '',
					'class'     => '',
					'title'     => '',
					'valign'    => '',
				],
				[
					'page' => false,
				],
				'<span class="modal-trigger" data-toggle="modal" data-target="#id"><img src="thumbnail::toHtml()/return/value.png" class="img-responsive"></span>',
			],
			'frame params w/o thumbnail'     => [
				[
					'thumbnail' => false,
					'framed'    => false,
					'frameless' => false,
					'border'    => false,
					'align'     => 'left',
					'alt'       => 'test_alt',
					'caption'   => 'test_caption:' . PHP_EOL . 'not next line, ' . PHP_EOL . 'still not next line, .' . PHP_EOL . PHP_EOL . 'next line',
					'class'     => 'test_class',
					'title'     => 'test_title',
					'valign'    => 'text-top',
				],
				[
					'page' => false,
				],
				'<div class="floatleft"><span class="modal-trigger" data-toggle="modal" data-target="#id"><img src="thumbnail::toHtml()/return/value.png" alt="test_alt" title="test_title" class="test_class img-responsive"></span></div>',
			],
			'manual width, frameless'        => [
				[
					'thumbnail' => false,
					'framed'    => false,
					'frameless' => true,
					'border'    => false,
					'align'     => 'left',
					'alt'       => '',
					'caption'   => '',
					'class'     => '',
					'title'     => '',
					'valign'    => '',
				],
				[
					'width' => 200,
					'page'  => 7,
				],
				'<div class="floatleft"><span class="modal-trigger" data-toggle="modal" data-target="#id"><img src="thumbnail::toHtml()/return/value.png" class="img-responsive"></span></div>',
			],
			'thumbnail, manual width'        => [
				[
					'thumbnail' => true,
					'framed'    => false,
					'frameless' => false,
					'border'    => false,
					'align'     => 'middle',
					'alt'       => '',
					'caption'   => '',
					'class'     => '',
					'title'     => '',
					'valign'    => '',
				],
				[
					'width' => 200,
					'page'  => 7,
				],
				'<div class="thumb tmiddle"><span class="modal-trigger" data-toggle="modal" data-target="#id"><div class="thumbinner" style="width:642px;"><img src="thumbnail::toHtml()/return/value.png" class="img-responsive thumbimage">  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></span></div>',
			],
			'manual thumbnail, NOT centered' => [
				[

					'thumbnail'   => false,
					'framed'      => true,
					'frameless'   => false,
					'border'      => false,
					'manualthumb' => 'Shuttle.png',
					'align'       => 'center',
					'alt'         => '',
					'caption'     => '',
					'class'       => '',
					'title'       => '',
					'valign'      => '',
				],
				[
					'page' => false,
				],
				'<div class="thumb tnone"><span class="modal-trigger" data-toggle="modal" data-target="#id"><div class="thumbinner" style="width:70px;"><img alt="" src="' . $scriptPath . '/images/a/aa/Shuttle.png" width="68" height="18" class="img-responsive thumbimage" />  <div class="thumbcaption"></div></div></span></div>',
			],
			'framed'                         => [
				[
					'thumbnail' => false,
					'framed'    => true,
					'frameless' => false,
					'border'    => false,
					'align'     => 'center',
					'alt'       => '',
					'caption'   => '',
					'class'     => '',
					'title'     => '',
					'valign'    => '',
				],
				[
					'page' => false,
				],
				'<div class="thumb tnone"><span class="modal-trigger" data-toggle="modal" data-target="#id"><div class="thumbinner" style="width:642px;"><img src="thumbnail::toHtml()/return/value.png" class="img-responsive thumbimage">  <div class="thumbcaption"></div></div></span></div>',
			],
			'centered'                       => [
				[
					'thumbnail' => false,
					'framed'    => false,
					'frameless' => false,
					'border'    => false,
					'align'     => 'center',
					'alt'       => '',
					'caption'   => '',
					'class'     => '',
					'title'     => '',
					'valign'    => '',
				],
				[
					'width' => 200,
					'page'  => false,
				],
				'<div class="center"><span class="modal-trigger" data-toggle="modal" data-target="#id"><img src="thumbnail::toHtml()/return/value.png" class="img-responsive"></span></div>',
			],
			'manual thumbnail, upright'      => [
				[
					'thumbnail'   => false,
					'framed'      => false,
					'frameless'   => false,
					'border'      => false,
					'manualthumb' => 'Shuttle.png',
					'align'       => 'left',
					'alt'         => '',
					'caption'     => '',
					'class'       => '',
					'title'       => '',
					'upright'     => 2342,
					'valign'      => '',
				],
				[
					'page' => false,
				],
				'<div class="thumb tleft"><span class="modal-trigger" data-toggle="modal" data-target="#id"><div class="thumbinner" style="width:70px;"><img alt="" src="' . $scriptPath . '/images/a/aa/Shuttle.png" width="68" height="18" class="img-responsive thumbimage" />  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></span></div>',
			],
		];
	}

	/**
	 * @param array $frameParams
	 * @param array $handlerParams
	 *
	 * @return string
	 */
	private function generatePhpCodeForManualProviderDataOneCase(
		/** @noinspection PhpUnusedParameterInspection */
		$frameParams, $handlerParams
	) {
		$ret = PHP_EOL;
		foreach ( [ 'frameParams', 'handlerParams' ] as $arrayArg ) {
			$ret .= '$' . $arrayArg . ' = [' . PHP_EOL;
			foreach ( $$arrayArg as $key => $val ) {
				$ret .= "\t'" . $key . '\' => ';
				switch ( gettype( $val ) ) {
					case 'boolean' :
						$ret .= $val ? 'true' : 'false';
						break;
					case 'integer' :
						$ret .= $val;
						break;
					default :
						$ret .= '\'' . $val . '\'';
						break;
				}
				$ret .= ',' . PHP_EOL;
			}
			$ret .= '],' . PHP_EOL;
		}
		return $ret;
	}
}
