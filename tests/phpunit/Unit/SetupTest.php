<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\Setup as Setup;
use BootstrapComponents\ComponentLibrary;
use \Parser;
use \ParserOutput;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\Setup
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
class SetupTest extends PHPUnit_Framework_TestCase {
	/**
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\Setup',
			new Setup( [] )
		);
	}

	/**
	 * @throws \ConfigException cascading {@see \BootstrapComponents\Setup::onExtensionLoad}
	 * @throws \MWException
	 */
	public function testOnExtensionLoad() {
		$this->assertTrue(
			Setup::onExtensionLoad( [ 'version' => 'test' ] )
		);
	}

	/**
	 * @param string[] $hookList
	 *
	 * @throws \ConfigException
	 * @throws \MWException
	 *
	 * @dataProvider buildHookCallbackListForProvider
	 */
	public function testCanBuildHookCallbackListFor( $hookList ) {

		$instance = new Setup( [] );

		/** @noinspection PhpParamsInspection */
		$hookCallbackList = $instance->buildHookCallbackListFor( $hookList );
		list ( $expectedHookList, $invertedHookList ) = $this->buildHookListsForCanBuildHookListCheck( $hookList );

		foreach ( $expectedHookList as $hook ) {
			$this->doTestHookIsRegistered( $instance, $hookCallbackList, $hook, false );
		}
		foreach ( $invertedHookList as $hook ) {
			$this->doTestHookIsNotRegistered( $hookCallbackList, $hook );
		}
	}

	/**
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testCanClear() {

		$instance = new Setup( [] );
		$instance->register(
			$instance->buildHookCallbackListFor( Setup::AVAILABLE_HOOKS )
		);
		foreach ( Setup::AVAILABLE_HOOKS as $hook ) {
			$this->assertTrue(
				$instance->isRegistered( $hook ),
				'Hook ' . $hook . ' is not registered!'
			);
		}
		$instance->clear();
		foreach ( [ 'GalleryGetModes', 'ImageBeforeProduceHTML' ] as $hook ) {
			$this->assertTrue(
				!$instance->isRegistered( $hook ),
				'Hook ' . $hook . ' is still registered!'
			);
		}
	}

	/**
	 * @param string[] $listOfConfigSettingsSet
	 * @param string[] $expectedHookList
	 *
	 * @throws \ConfigException
	 * @throws \MWException
	 *
	 * @dataProvider hookRegistryProvider
	 */
	public function testCanCompileRequestedHooksListFor( $listOfConfigSettingsSet, $expectedHookList ) {
		$myConfig = $this->getMockBuilder( 'Config' )
			->disableOriginalConstructor()
			->getMock();
		$myConfig->expects( $this->any() )
			->method( 'has' )
			->will( $this->returnCallback(
				function( $configSetting ) use ( $listOfConfigSettingsSet )
				{
					return in_array( $configSetting, $listOfConfigSettingsSet );
				}
			) );
		$myConfig->expects( $this->any() )
			->method( 'get' )
			->will( $this->returnCallback(
				function( $configSetting ) use ( $listOfConfigSettingsSet )
				{
					return in_array( $configSetting, $listOfConfigSettingsSet );
				}
			) );

		$instance = new Setup( [] );

		/** @noinspection PhpParamsInspection */
		$compiledHookList = $instance->compileRequestedHooksListFor( $myConfig );

		$this->assertEquals(
			$expectedHookList,
			$compiledHookList
		);
	}

	/**
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testCanGetCompleteHookDefinitionList() {

		$myConfig = $this->getMockBuilder( 'Config' )
			->disableOriginalConstructor()
			->getMock();
		$componentLibrary = $this->getMockBuilder( 'BootstrapComponents\\ComponentLibrary' )
			->disableOriginalConstructor()
			->getMock();
		$nestingController = $this->getMockBuilder( 'BootstrapComponents\\NestingController' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new Setup( [] );

		/** @noinspection PhpParamsInspection */
		$completeHookDefinitionList = $instance->getCompleteHookDefinitionList( $myConfig, $componentLibrary, $nestingController );
		$this->assertEquals(
			Setup::AVAILABLE_HOOKS,
			array_keys( $completeHookDefinitionList )
		);

		foreach ( $completeHookDefinitionList as $callback ) {
			$this->assertTrue(
				is_callable( $callback )
			);
		}
	}

	/**
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testCanInitializeApplications() {

		$myConfig = $this->getMockBuilder( 'Config' )
			->disableOriginalConstructor()
			->getMock();

		$instance = new Setup( [] );

		/** @noinspection PhpParamsInspection */
		list( $componentLibrary, $nestingController ) = $instance->initializeApplications( $myConfig );

		$this->assertInstanceOf(
			'BootstrapComponents\\ComponentLibrary',
			$componentLibrary
		);
		$this->assertInstanceOf(
			'BootstrapComponents\\NestingController',
			$nestingController
		);
	}

	/**
	 * @param array $listOfConfigSettingsSet
	 * @param array $expectedRegisteredHooks
	 * @param array $expectedNotRegisteredHooks
	 *
	 * @throws \ConfigException cascading {@see \Config::get}
	 * @throws \MWException
	 *
	 * @dataProvider hookRegistryProvider
	 */
	public function testHookRegistrationProcess( $listOfConfigSettingsSet, $expectedRegisteredHooks, $expectedNotRegisteredHooks ) {

		$instance = new Setup( [] );

		$hookCallbackList = $instance->buildHookCallbackListFor(
			$expectedRegisteredHooks
		);

		$this->assertTrue(
			is_array( $listOfConfigSettingsSet )
		);

		$this->assertEquals(
			count( $expectedRegisteredHooks ),
			$instance->register( $hookCallbackList )
		);

		foreach ( $expectedRegisteredHooks as $expectedHook ) {
			$this->doTestHookIsRegistered( $instance, $hookCallbackList, $expectedHook );
		}

		foreach ( $expectedNotRegisteredHooks as $notExpectedHook ) {
			$this->doTestHookIsNotRegistered( $hookCallbackList, $notExpectedHook );
		}
	}

	/**
	 * @throws \ConfigException cascading {@see \Config::get}
	 * @throws \MWException
	 */
	public function testCanRun() {

		$instance = new Setup( [] );

		$this->assertInternalType(
			'integer',
			$instance->run()
		);
	}

	/*
	 * Here end the tests for all the public methods.
	 * Following one test per hook function and one test for all the parser hook registrations.
	 */

	/**
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testHookGalleryGetModes() {
		$instance = new Setup( [] );
		$hookCallbackList = $instance->buildHookCallbackListFor(
			[ 'GalleryGetModes' ]
		);
		$this->assertArrayHasKey(
			'GalleryGetModes',
			$hookCallbackList
		);
		$this->assertTrue(
			is_callable( $hookCallbackList['GalleryGetModes'] )
		);
		$modesForTest = [ 'default' => 'TestGallery' ];
		$hookCallbackList['GalleryGetModes']( $modesForTest );
		$this->assertEquals(
			2,
			count( $modesForTest )
		);
		$this->assertArrayHasKey(
			'carousel',
			$modesForTest
		);
		$this->assertEquals(
			'BootstrapComponents\\CarouselGallery',
			$modesForTest['carousel']
		);
	}

	/**
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testHookImageBeforeProduceHTML() {
		$instance = new Setup( [] );
		$hookCallbackList = $instance->buildHookCallbackListFor(
			[ 'ImageBeforeProduceHTML' ]
		);
		$this->assertArrayHasKey(
			'ImageBeforeProduceHTML',
			$hookCallbackList
		);
		$this->assertTrue(
			is_callable( $hookCallbackList['ImageBeforeProduceHTML'] )
		);
		$linker = $title = $file = $frameParams = $handlerParams = $time = $res = false;

		$this->assertTrue(
			$hookCallbackList['ImageBeforeProduceHTML']( $linker, $title, $file, $frameParams, $handlerParams, $time, $res )
		);
	}

	/**
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testHookInternalParseBeforeLinks() {
		$parserOutput = $this->getMockBuilder( 'ParserOutput' )
			->disableOriginalConstructor()
			->getMock();
		$parserOutput->expects( $this->once() )
			->method( 'setExtensionData' )
			->with(
				$this->stringContains( 'bsc_no_image_modal' ),
				$this->isTrue()
			);
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$parser->expects( $this->any() )
			->method( 'getOutput' )
			->willReturn( $parserOutput );

		$instance = new Setup( [] );

		$hookCallbackList = $instance->buildHookCallbackListFor(
			[ 'InternalParseBeforeLinks' ]
		);
		$this->assertArrayHasKey(
			'InternalParseBeforeLinks',
			$hookCallbackList
		);
		$text = '';
		$this->assertTrue(
			isset( $hookCallbackList['InternalParseBeforeLinks'] )
			&& is_callable( $hookCallbackList['InternalParseBeforeLinks'] )
			&& $hookCallbackList['InternalParseBeforeLinks']( $parser, $text )
		);
		$this->assertEquals(
			'',
			$text
		);
		$text = '__NOIMAGEMODAL__';
		$this->assertTrue(
			$hookCallbackList['InternalParseBeforeLinks']( $parser, $text )
		);
		$this->assertEquals(
			'',
			$text
		);
	}

	/**
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testHookParserBeforeTidy() {
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$parserOutputHelper = $this->getMockBuilder( 'BootstrapComponents\\ParserOutputHelper' )
			->disableOriginalConstructor()
			->getMock();
		$parserOutputHelper->expects( $this->exactly( 2 ) )
			->method( 'getContentForLaterInjection' )
			->willReturnOnConsecutiveCalls( '', 'call2' );


		$instance = new Setup( [] );
		$hookCallbackList = $instance->buildHookCallbackListFor(
			[ 'ParserBeforeTidy' ]
		);

		$text = '';
		$this->assertTrue(
			isset( $hookCallbackList['ParserBeforeTidy'] )
			&& is_callable( $hookCallbackList['ParserBeforeTidy'] )
			&& $hookCallbackList['ParserBeforeTidy']( $parser, $text, $parserOutputHelper )
		);
		$this->assertEquals(
			'',
			$text
		);
		$hookCallbackList['ParserBeforeTidy']( $parser, $text, $parserOutputHelper );
		$this->assertEquals(
			'call2',
			$text
		);
	}

	/**
	 * @throws \ConfigException
	 * @throws \MWException
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

		$instance = new Setup( [] );
		$hookCallbackList = $instance->buildHookCallbackListFor(
			[ 'ParserFirstCallInit' ]
		);
		$this->assertTrue(
			isset( $hookCallbackList['ParserFirstCallInit'] )
			&& is_callable( $hookCallbackList['ParserFirstCallInit'] )
			&& $hookCallbackList['ParserFirstCallInit']( $observerParser )
		);
	}

	/**
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testHookSetupAfterCache() {
		$instance = new Setup( [] );
		$hookCallbackList = $instance->buildHookCallbackListFor(
			[ 'SetupAfterCache' ]
		);
		$this->assertArrayHasKey(
			'SetupAfterCache',
			$hookCallbackList
		);
		$this->assertTrue(
			is_callable( $hookCallbackList['SetupAfterCache'] )
		);
		$this->assertTrue(
			$hookCallbackList['SetupAfterCache']()
		);
	}

	/**
	 * @throws \ConfigException
	 * @throws \MWException
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

		$instance = new Setup( [] );

		$hookCallbackList = $instance->buildHookCallbackListFor(
			[ 'ParserFirstCallInit' ]
		);

		$this->assertTrue(
			isset( $hookCallbackList['ParserFirstCallInit'] )
			&& is_callable( $hookCallbackList['ParserFirstCallInit'] )
			&& $hookCallbackList['ParserFirstCallInit']( $extractionParser )
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
	 * @return array
	 */
	public function buildHookCallbackListForProvider() {
		return [
			'empty'               => [ [] ],
			'default'             => [ [ 'ParserFirstCallInit', 'SetupAfterCache' ] ],
			'alsoImageModal'      => [ [ 'ImageBeforeProduceHTML', 'InternalParseBeforeLinks', 'ParserBeforeTidy', 'ParserFirstCallInit', 'SetupAfterCache' ] ],
			'alsoCarouselGallery' => [ [ 'GalleryGetModes', 'ParserFirstCallInit', 'SetupAfterCache' ] ],
			'all'                 => [ [ 'GalleryGetModes', 'ImageBeforeProduceHTML', 'InternalParseBeforeLinks', 'ParserBeforeTidy', 'ParserFirstCallInit', 'SetupAfterCache' ] ],
			'invalid'             => [ [ 'nonExistingHook', 'PageContentSave' ] ],
		];
	}

	/**
	 * @return string[]
	 */
	public function hookRegistryProvider() {
		return [
			'onlydefault' => [
				[],
				[ 'ParserFirstCallInit', 'SetupAfterCache' ],
				[ 'GalleryGetModes', 'ImageBeforeProduceHTML' ],
			],
			'gallery activated' => [
				[ 'BootstrapComponentsEnableCarouselGalleryMode' ],
				[ 'ParserFirstCallInit', 'SetupAfterCache', 'GalleryGetModes' ],
				[ 'ImageBeforeProduceHTML', 'InternalParseBeforeLinks', 'ParserBeforeTidy'  ],
			],
			'image replacement activated' => [
				[ 'BootstrapComponentsModalReplaceImageTag' ],
				[ 'ParserFirstCallInit', 'SetupAfterCache', 'ImageBeforeProduceHTML', 'InternalParseBeforeLinks', 'ParserBeforeTidy' ],
				[ 'GalleryGetModes' ],
			],
			'both activated' => [
				[ 'BootstrapComponentsEnableCarouselGalleryMode', 'BootstrapComponentsModalReplaceImageTag' ],
				[ 'ParserFirstCallInit', 'SetupAfterCache', 'GalleryGetModes', 'ImageBeforeProduceHTML', 'InternalParseBeforeLinks', 'ParserBeforeTidy' ],
				[],
			],
		];
	}

	/**
	 * @param $hookList
	 *
	 * @return array $expectedHookList, $invertedHookList
	 */
	private function buildHookListsForCanBuildHookListCheck( $hookList ) {
		$expectedHookList = [];
		$invertedHookList = [];
		foreach ( $hookList as $hook ) {
			if ( in_array( $hook, Setup::AVAILABLE_HOOKS ) ) {
				$expectedHookList[] = $hook;
			}
		}
		foreach ( Setup::AVAILABLE_HOOKS as $availableHook ) {
			if ( !in_array( $availableHook, $hookList ) ) {
				$invertedHookList[] = $availableHook;
			}
		}
		return [ $expectedHookList, $invertedHookList ];
	}

	/**
	 * @param Setup  $instance
	 * @param array  $registeredHooks
	 * @param string $expectedHook
	 * @param bool   $hardRegisterTest
	 */
	private function doTestHookIsRegistered( Setup $instance, $registeredHooks, $expectedHook, $hardRegisterTest = true ) {
		if ( $hardRegisterTest ) {
			$this->assertTrue(
				$instance->isRegistered( $expectedHook )
			);
		}
		$this->assertArrayHasKey(
			$expectedHook,
			$registeredHooks,
			'Expected hook "' . $expectedHook . '" to be registered but was not! '
		);
		$this->assertTrue(
			is_callable( $registeredHooks[$expectedHook] )
		);
	}

	/**
	 * @param array  $registeredHooks
	 * @param string $notExpectedHook
	 */
	private function doTestHookIsNotRegistered( $registeredHooks, $notExpectedHook ) {
		$this->assertArrayNotHasKey(
			$notExpectedHook,
			$registeredHooks,
			'Expected hook "' . $notExpectedHook . '" to not be registered but was! '
		);
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
