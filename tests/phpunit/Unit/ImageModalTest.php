<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use File;
use LocalFile;
use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
use MediaWiki\Extension\BootstrapComponents\ImageModal;
use \ConfigException;
use MediaWiki\Extension\BootstrapComponents\NestingController;
use MediaWiki\Extension\BootstrapComponents\ParserOutputHelper;
use \MediaWiki\MediaWikiServices;
use PHPUnit\Framework\TestCase;
use ThumbnailImage;
use Title;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\ImageModal
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
class ImageModalTest extends TestCase {

	public function setUp(): void {
		parent::setUp();
	}

	/**
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$file = $this->createMock( File::class );

		$this->assertInstanceOf(
			ImageModal::class,
			$this->createImageModalWithMocks()
		);
		$this->assertInstanceOf(
			ImageModal::class,
			$this->createImageModalWithMocks( null, null, $file )
		);
	}

	/**
	 * @throws \MWException
	 * @throws \ConfigException
	 */
	public function testCanParseOnFileNonExistent() {
		$file = $this->createMock( LocalFile::class );
		$file->expects( $this->any() )
			->method( 'allowInlineDisplay' )
			->willReturn( true );
		$file->expects( $this->any() )
			->method( 'exists' )
			->willReturn( false );

		$instance = $this->createImageModalWithMocks( null, null, $file );
		$fp = [];
		$hp = [];
		$time = false;
		$res = '';

		$resultOfParseCall = $instance->parse( $fp, $hp, $time, $res );

		$this->assertTrue(
			$resultOfParseCall
		);
	}

	/**
	 * @throws \MWException
	 * @throws \ConfigException
	 */
	public function testCanParseOnFileNoAllowInlineParse() {
		$file = $this->createMock( LocalFile::class );
		$file->expects( $this->any() )
			->method( 'allowInlineDisplay' )
			->willReturn( false );
		$file->expects( $this->any() )
			->method( 'exists' )
			->willReturn( true );

		$instance = $this->createImageModalWithMocks( null, null, $file );
		$fp = [];
		$hp = [];
		$time = false;
		$res = '';

		$resultOfParseCall = $instance->parse( $fp, $hp, $time, $res );

		$this->assertTrue(
			$resultOfParseCall
		);
	}

	/**
	 * @throws \MWException
	 * @throws \ConfigException
	 */
	public function testCanParseOnOnInvalidManualThumb() {
		$file = $this->getMockBuilder( 'LocalFile' )
			->disableOriginalConstructor()
			->getMock();
		$file->expects( $this->any() )
			->method( 'allowInlineDisplay' )
			->willReturn( true );
		$file->expects( $this->any() )
			->method( 'exists' )
			->willReturn( true );

		$instance = $this->createImageModalWithMocks( null, null, $file );
		$time = false;
		$res = '';
		$fp =  [ 'manualthumb' => 'ImageInvalid.png' ];
		$hp = [];

		$resultOfParseCall = $instance->parse( $fp, $hp, $time, $res );

		$this->assertTrue(
			$resultOfParseCall
		);
	}

	/**
	 * @throws \MWException
	 * @throws \ConfigException
	 */
	public function testCanParseOnOnInvalidContentImage() {
		$title = $this->createMock( Title::class );
		$title->expects( $this->any() )
			->method( 'getLocalUrl' )
			->willReturn( '/File:Serenity.png' );

		$thumb = $this->createMock( ThumbnailImage::class );
		$thumb->expects( $this->any() )
			->method( 'getWidth' )
			->willReturn( 52 );
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
					return '<img src=TEST_OUTPUT ' . implode( ' ', $ret ) . '>';
				}
			) );
		$file = $this->createMock( LocalFile::class );
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
			->willReturn( false );
		$file->expects( $this->any() )
			->method( 'transform' )
			->willReturn( $thumb );

		$instance = $this->createImageModalWithMocks( null, $title, $file );
		$time = false;
		$res = '';
		$fp = [ 'align' => 'left' ]; # otherwise, this test produces an exception while trying to call $title->getPageLanguage()->alignEnd()
		$hp = [];

		$resultOfParseCall = $instance->parse( $fp, $hp, $time, $res );

		$this->assertTrue(
			$resultOfParseCall
		);
	}

	/**
	 * @param array  $fp
	 * @param array  $hp
	 * @param string $expectedTrigger
	 * @param string $expectedModal
	 *
	 * @throws \MWException
	 * @throws \ConfigException
	 *
	 * @dataProvider canParseDataProvider
	 */
	public function testCanParse( $fp, $hp, $expectedTrigger, $expectedModal ) {

		$title = $this->createMock( Title::class );
		$title->expects( $this->any() )
			->method( 'getLocalUrl' )
			->willReturn( '/File:Serenity.png' );

		$thumb = $this->createMock( ThumbnailImage::class );
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
					return '<img src=TEST_OUTPUT ' . implode( ' ', $ret ) . '>';
				}
			) );
		$file = $this->createMock( LocalFile::class );
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

		$nestingController = $this->createMock( NestingController::class );
		$nestingController->expects( $this->any() )
			->method( 'generateUniqueId' )
			->will( $this->returnCallback(
				function( $component ) {
					return 'bsc_' . $component . '_test';
				}
			) );

		$modalInjection = '';
		$parserOutputHelper = $this->createMock( ParserOutputHelper::class );
		$parserOutputHelper->expects( $this->any() )
			->method( 'injectLater' )
			->will( $this->returnCallback( function( $id, $text ) use ( &$modalInjection ) {
				$modalInjection .= $text;
			} ) );

		$instance = $this->createImageModalWithMocks( null, $title, $file, $nestingController, null, $parserOutputHelper );
		$time = false;
		$res = '';

		$resultOfParseCall = $instance->parse( $fp, $hp, $time, $res );

		if ( version_compare( $GLOBALS['wgVersion'], '1.40', 'lt' ) ) {
			$this->assertRegExp(
				$expectedTrigger,
				$resultOfParseCall ?: $res,
				'failed with test data:' . $this->generatePhpCodeForManualProviderDataOneCase( $fp, $hp )
			);
		} else {
			$this->assertMatchesRegularExpression(
				$expectedTrigger,
				$resultOfParseCall ?: $res,
				'failed with test data:' . $this->generatePhpCodeForManualProviderDataOneCase( $fp, $hp )
				. '++ ' . $expectedTrigger . PHP_EOL
				. '--  ' . ($resultOfParseCall ?: $res)
			);
		}
		$this->assertEquals(
			$expectedModal,
			$modalInjection,
			'failed modal with test data:' . $this->generatePhpCodeForManualProviderDataOneCase( $fp, $hp )
		);
	}

	/**
	 * @throws ConfigException cascading {@see \Config::get}
	 * @return array[]
	 */
	public function canParseDataProvider(): array
	{
		$globalConfig = MediaWikiServices::getInstance()->getMainConfig();
		$scriptPath = $globalConfig->get( 'ScriptPath' );
		/*
		 * notes on adding tests:
		 * - when using manual thumbnail, inject $scriptPath: <img alt="" src="' . $scriptPath . '/images/a/aa/Shuttle.png" width="68" height="18" ...
		 * - always supply an align value, otherwise testing will fail with an exception due to bad class design (blame @oetterer)
		 * - switch values (booleans) are true when not present and false. see "frameless" on test
		 * "manual width, frameless"
		 */
		return [
			'no params' => [
				[],
				[],
				'~<span class="modal-trigger" data-toggle="modal" data-target="#bsc_modal_test"><img src=TEST_OUTPUT ></span>~',
				'<div class="modal fade" role="dialog" id="bsc_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-fluid"></div><div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div></div></div></div>' . "\n",
			],
			'frame params w/o thumbnail' => [
				[
					'align'   => 'left',
					'alt'     => 'test_alt',
					'caption' => 'test_caption:' . PHP_EOL . 'not next line, ' . PHP_EOL . 'still not next line, .' . PHP_EOL . PHP_EOL . 'next line',
					'class'   => 'test_class',
					'title'   => 'test_title',
					'valign'  => 'text-top',
				],
				[],
				'~<div class="floatleft"><span class="modal-trigger" data-toggle="modal" data-target="#bsc_modal_test"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="test_class"></span></div>~',
				'<div class="modal fade" role="dialog" id="bsc_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'
				. '<div class="modal-body"><img src=TEST_OUTPUT alt="test_alt" title="test_title" class="test_class img-fluid"> <div class="modal-caption">test_caption:not next line, still not next line, .' . PHP_EOL . PHP_EOL . 'next line</div></div><div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div></div></div></div>' . "\n",
			],
			'manual width, frameless' => [
				[
					'align'     => 'left',
					'frameless' => false,
				],
				[
					'width' => 200,
					'page'  => 7,
				],
				'~<div class="floatleft"><span class="modal-trigger" data-toggle="modal" data-target="#bsc_modal_test"><img src=TEST_OUTPUT ></span></div>~',
				'<div class="modal fade" role="dialog" id="bsc_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-fluid"></div><div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png?page=7">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div></div></div></div>' . "\n",
			],
			'thumbnail, manual width' => [
				[
					'align'     => 'middle',
					'thumbnail' => false,
				],
				[
					'width' => 200,
					'page'  => 7,
				],
				'~<div class="thumb tmiddle"><span class="modal-trigger" data-toggle="modal" data-target="#bsc_modal_test"><div class="thumbinner" style="width:642px;"><img src=TEST_OUTPUT class="thumbimage">  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></span></div>~',
				'<div class="modal fade" role="dialog" id="bsc_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><img src=TEST_OUTPUT class="img-fluid"></div>'
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png?page=7">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div></div></div></div>' . "\n",
			],
			'manual thumbnail, NOT centered' => [
				[
					'align'       => 'center',
					'manualthumb' => 'Shuttle.png',
					'framed'      => false,
				],
				[],
				'~<div class="thumb tnone"><span class="modal-trigger" data-toggle="modal" data-target="#bsc_modal_test"><div class="thumbinner" style="width:96px;">(<span>)?<img alt="" src="' . $scriptPath . '/images/a/aa/Shuttle.png" decoding="async" width="94" height="240" class="thumbimage" />(</span>)?  <div class="thumbcaption"></div></div></span></div>~',
				'<div class="modal fade" role="dialog" id="bsc_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div><div class="modal-body"><img src=TEST_OUTPUT class="img-fluid"></div>'
				. '<div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div></div></div></div>' . "\n",
			],
			'framed' => [
				[
					'align'  => 'center',
					'framed' => false,
				],
				[],
				'~<div class="thumb tnone"><span class="modal-trigger" data-toggle="modal" data-target="#bsc_modal_test"><div class="thumbinner" style="width:642px;"><img src=TEST_OUTPUT class="thumbimage">  <div class="thumbcaption"></div></div></span></div>~',
				'<div class="modal fade" role="dialog" id="bsc_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-fluid"></div><div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div></div></div></div>' . "\n",
			],
			'centered' => [
				[
					'align' => 'center',
				],
				[
					'width' => 200,
				],
				'~<div class="center"><span class="modal-trigger" data-toggle="modal" data-target="#bsc_modal_test"><img src=TEST_OUTPUT ></span></div>~',
				'<div class="modal fade" role="dialog" id="bsc_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-fluid"></div><div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div></div></div></div>' . "\n",
			],
			'manual thumbnail, upright' => [
				[
					'align'       => 'left',
					'upright'     => 2342,
					'manualthumb' => 'Shuttle.png',
				],
				[],
				'~<div class="thumb tleft"><span class="modal-trigger" data-toggle="modal" data-target="#bsc_modal_test"><div class="thumbinner" style="width:96px;">(<span>)?<img alt="" src="' . $scriptPath . '/images/a/aa/Shuttle.png" decoding="async" width="94" height="240" class="thumbimage" />(</span>)?  <div class="thumbcaption"><div class="magnify"><a class="internal" title="Enlarge"></a></div></div></div></span></div>~',
				'<div class="modal fade" role="dialog" id="bsc_modal_test" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button></div>'
				. '<div class="modal-body"><img src=TEST_OUTPUT class="img-fluid"></div><div class="modal-footer"><a class="btn btn-primary" role="button" href="/File:Serenity.png">Visit Source</a><button type="button" class="btn btn-default" data-dismiss="modal" aria-label="Close">Close</button></div></div></div></div>' . "\n",
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
		/** @noinspection PhpUnusedParameterInspection  */
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

	private function createImageModalWithMocks(
		$dummyLinker = null, $title = null, $file = null, $nestingController = null,
		$bootstrapService = null, $parserOutputHelper = null
	) {
		$dummyLinker = null;
		$title = $title ?? $this->createMock( Title::class );
		$file = $file ?? $this->createMock( LocalFile::class );
		$nestingController = $nestingController ?? $this->createMock( NestingController::class );
		$bootstrapService = $bootstrapService ?? $this->createMock( BootstrapComponentsService::class );
		$parserOutputHelper = $parserOutputHelper ?? $this->createMock( ParserOutputHelper::class );

		return new ImageModal( $dummyLinker, $title, $file, $nestingController, $bootstrapService, $parserOutputHelper );
	}
}
