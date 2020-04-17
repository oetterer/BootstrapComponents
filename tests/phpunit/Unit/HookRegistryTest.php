<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ParserOutputHelper;
use BootstrapComponents\HookRegistry as HookRegistry;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\HookRegistry
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
class HookRegistryTest extends PHPUnit_Framework_TestCase {
	/**
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\HookRegistry',
			new HookRegistry()
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

		$instance = new HookRegistry();

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

		$instance = new HookRegistry();
		$instance->register(
			$instance->buildHookCallbackListFor( HookRegistry::AVAILABLE_HOOKS )
		);
		foreach ( HookRegistry::AVAILABLE_HOOKS as $hook ) {
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

		$instance = new HookRegistry();

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

		$instance = new HookRegistry();

		/** @noinspection PhpParamsInspection */
		$completeHookDefinitionList = $instance->getCompleteHookDefinitionList( $myConfig, $componentLibrary, $nestingController );
		$this->assertEquals(
			HookRegistry::AVAILABLE_HOOKS,
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

		$instance = new HookRegistry();

		/** @noinspection PhpParamsInspection */
		list ( $componentLibrary, $nestingController ) = $instance->initializeApplications( $myConfig );

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

		$instance = new HookRegistry();

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

		$instance = new HookRegistry();

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

		$callback = $this->getCallBackForHook( 'GalleryGetModes' );
		$modesForTest = [ 'default' => 'TestGallery' ];

		$callback( $modesForTest );
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
		$callback = $this->getCallBackForHook( 'ImageBeforeProduceHTML' );
		$linker = $title = $file = $frameParams = $handlerParams = $time = $res = false;

		$this->assertTrue(
			$callback( $linker, $title, $file, $frameParams, $handlerParams, $time, $res )
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
		$parserOutput->expects( $this->exactly( 2 ) )
			->method( 'setExtensionData' )
			->with(
				$this->stringContains( 'bsc_no_image_modal' ),
				$this->isType( 'boolean' )
			);

		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$parser->expects( $this->exactly( 2 ) )
			->method( 'getOutput' )
			->willReturn( $parserOutput );

		$callback = $this->getCallBackForHook( 'InternalParseBeforeLinks' );

		$text = '';
		$this->assertTrue(
			$callback( $parser, $text )
		);
		$this->assertEquals(
			'',
			$text
		);
		$text = '__NOIMAGEMODAL__';
		$this->assertTrue(
			$callback( $parser, $text )
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
	public function testHookOutputPageParserOutput() {
		$content = 'CONTENT';
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();
		$parserOutput = $this->getMockBuilder( 'ParserOutput' )
			->disableOriginalConstructor()
			->getMock();
		$parserOutput->expects( $this->exactly( 2 ) )
			->method( 'getExtensionData' )
			->with(
				$this->stringContains( 'bsc_deferredContent' )
			)
			->willReturnOnConsecutiveCalls( [], [ 'call2' ] );
		$parserOutput->expects( $this->exactly( 2 ) )
			->method( 'getText' )
			->will( $this->returnCallback( function() use ( &$content ) {
				return $content;
			} ) );
		$parserOutput->expects( $this->exactly( 2 ) )
			->method( 'setText' )
			->will( $this->returnCallback( function( $injection ) use ( &$content ) {
				$content = $injection;
			} ) );
		$outputPage = $this->getMockBuilder( 'OutputPage' )
			->disableOriginalConstructor()
			->getMock();

		/** @noinspection PhpParamsInspection */
		$parserOutputHelper = new ParserOutputHelper( $parser );

		$callback = $this->getCallBackForHook( 'OutputPageParserOutput' );

		$this->assertTrue(
			$callback( $outputPage, $parserOutput, $parserOutputHelper )
		);
		$this->assertEquals(
			'CONTENT',
			$content
		);
		$this->assertTrue(
			$callback( $outputPage, $parserOutput, $parserOutputHelper )
		);
		$this->assertEquals(
			'CONTENT<!-- injected by Extension:BootstrapComponents -->call2<!-- /injected by Extension:BootstrapComponents -->',
			$content
		);
	}

	/**
	 * Note: Hook ParserFirstCallInit is tested in detail in {@see \BootstrapComponents\Tests\Unit\Hooks\ParserFirstCallInitTest}.
	 *
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testHookParserFirstCallInit() {
		$parser = $this->getMockBuilder( 'Parser' )
			->disableOriginalConstructor()
			->getMock();

		$callback = $this->getCallBackForHook( 'ParserFirstCallInit' );

		$this->assertTrue(
			$callback( $parser )
		);
	}


	/**
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testHookScribuntoExternalLibraries() {
		$callback = $this->getCallBackForHook( 'ScribuntoExternalLibraries' );

		$libraries = [];
		$this->assertTrue(
			$callback( '', $libraries )
		);
		$this->assertEquals(
			[],
			$libraries
		);
		$this->assertTrue(
			$callback( 'lua', $libraries )
		);
		$this->assertArrayHasKey(
			'mw.bootstrap',
			$libraries
		);
		$this->assertEquals(
			'BootstrapComponents\\LuaLibrary',
			$libraries['mw.bootstrap']
		);
	}

	/**
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function testHookSetupAfterCache() {
		$callback = $this->getCallBackForHook( 'SetupAfterCache' );
		$this->assertTrue(
			$callback()
		);
	}

	/**
	 * @return array
	 */
	public function buildHookCallbackListForProvider() {
		return [
			'empty'               => [ [] ],
			'default'             => [ [ 'ParserAfterTidy', 'ParserFirstCallInit', 'SetupAfterCache', 'ScribuntoExternalLibraries' ] ],
			'alsoImageModal'      => [ [ 'ImageBeforeProduceHTML', 'InternalParseBeforeLinks', 'ParserAfterTidy', 'ParserFirstCallInit', 'SetupAfterCache', 'ScribuntoExternalLibraries' ] ],
			'alsoCarouselGallery' => [ [ 'GalleryGetModes', 'ParserAfterTidy', 'ParserFirstCallInit', 'SetupAfterCache', 'ScribuntoExternalLibraries' ] ],
			'all'                 => [ [ 'GalleryGetModes', 'ImageBeforeProduceHTML', 'InternalParseBeforeLinks', 'ParserFirstCallInit', 'SetupAfterCache', 'ScribuntoExternalLibraries' ] ],
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
				[ 'OutputPageParserOutput', 'ParserAfterParse', 'ParserFirstCallInit', 'SetupAfterCache', 'ScribuntoExternalLibraries', ],
				[ 'GalleryGetModes', 'ImageBeforeProduceHTML', 'InternalParseBeforeLinks', ],
			],
			'gallery activated' => [
				[ 'BootstrapComponentsEnableCarouselGalleryMode' ],
				[ 'OutputPageParserOutput', 'ParserAfterParse', 'ParserFirstCallInit', 'SetupAfterCache', 'ScribuntoExternalLibraries', 'GalleryGetModes', ],
				[ 'ImageBeforeProduceHTML', 'InternalParseBeforeLinks', ],
			],
			'image replacement activated' => [
				[ 'BootstrapComponentsModalReplaceImageTag' ],
				[ 'OutputPageParserOutput', 'ParserAfterParse', 'ParserFirstCallInit', 'SetupAfterCache', 'ScribuntoExternalLibraries', 'ImageBeforeProduceHTML', 'InternalParseBeforeLinks', ],
				[ 'GalleryGetModes', ],
			],
			'both activated' => [
				[ 'BootstrapComponentsEnableCarouselGalleryMode', 'BootstrapComponentsModalReplaceImageTag' ],
				[ 'OutputPageParserOutput', 'ParserAfterParse', 'ParserFirstCallInit', 'SetupAfterCache', 'ScribuntoExternalLibraries', 'GalleryGetModes', 'ImageBeforeProduceHTML', 'InternalParseBeforeLinks', ],
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
			if ( in_array( $hook, HookRegistry::AVAILABLE_HOOKS ) ) {
				$expectedHookList[] = $hook;
			}
		}
		foreach ( HookRegistry::AVAILABLE_HOOKS as $availableHook ) {
			if ( !in_array( $availableHook, $hookList ) ) {
				$invertedHookList[] = $availableHook;
			}
		}
		return [ $expectedHookList, $invertedHookList ];
	}

	/**
	 * @param HookRegistry $instance
	 * @param array        $registeredHooks
	 * @param string       $expectedHook
	 * @param bool         $hardRegisterTest
	 */
	private function doTestHookIsRegistered( HookRegistry $instance, $registeredHooks, $expectedHook, $hardRegisterTest = true ) {
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
	 * @param string $hook
	 *
	 * @throws \ConfigException
	 * @throws \MWException
	 *
	 * @return \Closure
	 */
	private function getCallBackForHook( $hook ) {
		$instance = new HookRegistry();
		$hookCallbackList = $instance->buildHookCallbackListFor(
			[ $hook ]
		);
		$this->assertArrayHasKey(
			$hook,
			$hookCallbackList
		);
		$this->assertTrue(
			is_callable( $hookCallbackList[$hook] )
		);
		return $hookCallbackList[$hook];
	}
}
