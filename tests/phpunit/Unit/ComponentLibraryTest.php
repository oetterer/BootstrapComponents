<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\ComponentLibrary;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\ComponentLibrary
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
class ComponentLibraryTest extends PHPUnit_Framework_TestCase {

	public function testCanConstruct() {

		$this->assertInstanceOf(
			ComponentLibrary::class,
			new ComponentLibrary()
		);
	}

	/**
	 * @param string $componentName
	 * @param string $expectedParserHookString
	 *
	 * @dataProvider compileParserHookStringProvider
	 */
	public function testCanCompileParserHookStringFor( $componentName, $expectedParserHookString ) {
		$this->assertEquals(
			$expectedParserHookString,
			ComponentLibrary::compileParserHookStringFor( $componentName )
		);
	}

	public function testCanCompileMagicWordsArray() {
		$instance = new ComponentLibrary();
		$this->assertEquals(
			[
				'bootstrap_badge'    => [ 0, 'bootstrap_badge' ],
				'bootstrap_button'   => [ 0, 'bootstrap_button' ],
				'bootstrap_carousel' => [ 0, 'bootstrap_carousel' ],
				'bootstrap_label'    => [ 0, 'bootstrap_label' ],
				'bootstrap_tooltip'  => [ 0, 'bootstrap_tooltip' ],
			],
			$instance->compileMagicWordsArray()
		);
	}

	/**
	 * @param string $componentName
	 *
	 * @throws \ConfigException
	 *
	 * @dataProvider componentNameAndClassProvider
	 */
	public function testIsRegistered( $componentName ) {
		$instance = new ComponentLibrary();
		$this->assertEquals(
			true,
			$instance->isRegistered( $componentName )
		);
	}

	/**
	 * @param string   $component
	 * @param string[] $expectedAliases
	 *
	 * @throws \ConfigException
	 * @throws \MWException
	 *
	 * @dataProvider componentAliasesProvider
	 */
	public function testGetAliasesFor( $component, $expectedAliases ) {
		$instance = new ComponentLibrary();
		$this->assertEquals(
			$expectedAliases,
			$instance->getAliasesFor( $component )
		);
	}

	/**
	 * @param string   $component
	 * @param string[] $expectedAttributes
	 *
	 * @throws \ConfigException
	 * @throws \MWException
	 *
	 * @dataProvider componentAttributesProvider
	 */
	public function testGetAttributesFor( $component, $expectedAttributes ) {
		$instance = new ComponentLibrary();
		$this->assertEquals(
			$expectedAttributes,
			$instance->getAttributesFor( $component )
		);
	}

	/**
	 * @param string $componentName
	 * @param string $componentClass
	 *
	 * @throws \ConfigException
	 * @throws \MWException
	 *
	 * @dataProvider componentNameAndClassProvider
	 */
	public function testGetClassFor( $componentName, $componentClass ) {
		$instance = new ComponentLibrary();
		$this->assertEquals(
			$componentClass,
			$instance->getClassFor( $componentName )
		);
	}

	/**
	 * @throws \ConfigException
	 */
	public function testGetAllRegisteredComponents() {
		$instance = new ComponentLibrary();
		$this->assertEquals(
			array_keys( $this->componentNameAndClassProvider() ),
			$instance->getRegisteredComponents()
		);
	}

	/**
	 * @param string $componentName
	 *
	 * @throws \ConfigException
	 *
	 * @dataProvider componentNameAndClassProvider
	 */
	public function testGetHandlerTypeFor( $componentName ) {
		$instance = new ComponentLibrary();

		$this->assertContains(
			$instance->getHandlerTypeFor( $componentName ),
			[ ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION, ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ]
		);
	}

	/**
	 * @throws \ConfigException
	 */
	public function testGetHandlerTypeForUnknownComponent() {
		$instance = new ComponentLibrary();

		$this->assertEquals(
			'UNKNOWN',
			$instance->getHandlerTypeFor( 'unknown' )
		);
	}

	/**
	 * @param string $componentName
	 * @param bool   $isParserFunction
	 *
	 * @throws \ConfigException
	 *
	 * @dataProvider handlerTypeProvider
	 */
	public function testIsHandlerType( $componentName, $isParserFunction ) {
		$instance = new ComponentLibrary();

		$this->assertTrue(
			!$isParserFunction xor $instance->isParserFunction( $componentName )
		);
		$this->assertTrue(
			$isParserFunction xor $instance->isTagExtension( $componentName )
		);
	}

	/**
	 * @param string $componentName
	 * @param string $skinName
	 * @param array  $expectedModules
	 *
	 * @throws \ConfigException
	 *
	 * @dataProvider modulesForComponentsProvider
	 */
	public function testGetModulesFor( $componentName, $skinName, $expectedModules ) {
		$instance = new ComponentLibrary();
		$this->assertEquals(
			$expectedModules,
			$instance->getModulesFor( $componentName, $skinName )
		);
	}

	/**
	 * @param string $componentName
	 * @param string $componentClass
	 *
	 * @throws \ConfigException
	 * @throws \MWException
	 *
	 * @dataProvider componentNameAndClassProvider
	 */
	public function testGetNameFor( $componentName, $componentClass ) {
		$instance = new ComponentLibrary();
		$this->assertEquals(
			$componentName,
			$instance->getNameFor( $componentClass )
		);
	}

	/**
	 * @param bool|string[] $whiteList
	 * @param string[]      $expectedComponents
	 *
	 * @throws \ConfigException
	 *
	 * @dataProvider whiteListProvider
	 */
	public function testSetWhiteList( $whiteList, $expectedComponents ) {
		$instance = new ComponentLibrary( $whiteList );
		$this->assertEquals(
			$expectedComponents,
			$instance->getRegisteredComponents()
		);
	}

	/**
	 * @param string $method
	 *
	 * @throws \ConfigException
	 *
	 * @expectedException \MWException
	 *
	 * @dataProvider exceptionThrowingMethodsProvider
	 */
	public function testFails( $method ) {
		$instance = new ComponentLibrary();

		$this->expectException( 'MWException' );

		call_user_func_array( [ $instance, $method ], [ null ] );
	}

	/**
	 * @throws \ConfigException
	 */
	public function testRegisterVsKnown() {
		$instance = new ComponentLibrary( [ 'alert', 'modal', 'panel' ] );
		$this->assertEquals(
			[ 'alert', 'modal', 'panel', ],
			$instance->getRegisteredComponents()
		);
		$this->assertEquals(
			ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION,
			$instance->getHandlerTypeFor( 'well' )
		);
		foreach ( $this->modulesForComponentsProvider() as $args ) {
			[ $component, $skin, $expectedModules ] = $args;
			$this->assertEquals(
				$expectedModules,
				$instance->getModulesFor( $component, $skin )
			);
		}
		$this->assertFalse(
			$instance->isRegistered( 'well' )
		);
	}

	/**
	 * @throws \ConfigException
	 *
	 * @expectedException \MWException
	 */
	public function testUnknownComponentName() {
		$instance = new ComponentLibrary( true );

		$this->expectException( 'MWException' );
		$instance->getClassFor( 'foobar' );
	}

	/**
	 * @throws \ConfigException
	 *
	 * @expectedException \MWException
	 */
	public function testUnknownComponentClass() {
		$instance = new ComponentLibrary( true );

		$this->expectException( 'MWException' );
		$instance->getNameFor( '\BootstrapComponents\Components\Foobar' );
	}

	/**
	 * @return array
	 */
	public function compileParserHookStringProvider() {
		return [
			'accordion' => [ 'accordion', 'bootstrap_accordion' ],
			'alert'     => [ 'alert', 'bootstrap_alert' ],
			'badge'     => [ 'badge', 'bootstrap_badge' ],
			'button'    => [ 'button', 'bootstrap_button' ],
			'carousel'  => [ 'carousel', 'bootstrap_carousel' ],
			'card'      => [ 'card', 'bootstrap_card' ],
			'collapse'  => [ 'collapse', 'bootstrap_collapse' ],
			'jumbotron' => [ 'jumbotron', 'bootstrap_jumbotron' ],
			'label'     => [ 'label', 'bootstrap_label' ],
			'modal'     => [ 'modal', 'bootstrap_modal' ],
			'panel'     => [ 'panel', 'bootstrap_panel' ],
			'popover'   => [ 'popover', 'bootstrap_popover' ],
			'tooltip'   => [ 'tooltip', 'bootstrap_tooltip' ],
			'well'      => [ 'well', 'bootstrap_well' ],
		];
	}

	/**
	 * @return array[]
	 */
	public function componentNameAndClassProvider() {
		return [
			'accordion' => [ 'accordion', 'BootstrapComponents\\Components\\Accordion' ],
			'alert'     => [ 'alert', 'BootstrapComponents\\Components\\Alert' ],
			'badge'     => [ 'badge', 'BootstrapComponents\\Components\\Badge' ],
			'button'    => [ 'button', 'BootstrapComponents\\Components\\Button' ],
			'card'      => [ 'card', 'BootstrapComponents\\Components\\Card' ],
			'carousel'  => [ 'carousel', 'BootstrapComponents\\Components\\Carousel' ],
			'collapse'  => [ 'collapse', 'BootstrapComponents\\Components\\Collapse' ],
			'jumbotron' => [ 'jumbotron', 'BootstrapComponents\\Components\\Jumbotron' ],
			'modal'     => [ 'modal', 'BootstrapComponents\\Components\\Modal' ],
			'popover'   => [ 'popover', 'BootstrapComponents\\Components\\Popover' ],
			'tooltip'   => [ 'tooltip', 'BootstrapComponents\\Components\\Tooltip' ],
			'label'     => [ 'badge', 'BootstrapComponents\\Components\\Badge' ],
			'panel'     => [ 'card', 'BootstrapComponents\\Components\\Card' ],
			'well'      => [ 'card', 'BootstrapComponents\\Components\\Card' ],
		];
	}

	/**
	 * @return array
	 */
	public function componentAliasesProvider() {
		return [
			'alert' => [ 'alert', [] ],
			'button' => [ 'button', [] ],
			'card' => [ 'card', [ 'footing' => 'footer', 'heading' => 'header', 'title' => 'header', ], ],
			'panel' => [ 'panel', [ 'footing' => 'footer', 'heading' => 'header', 'title' => 'header', ], ],
		];
	}

	/**
	 * @return array
	 */
	public function componentAttributesProvider() {
		return [
			'accordion' => [ 'accordion', [ 'class', 'id', 'style' ] ],
			'alert'     => [ 'alert', [ 'color', 'dismissible', 'class', 'id', 'style' ] ],
			'modal'     => [ 'modal', [ 'color', 'footer', 'header', 'size', 'text', 'class', 'id', 'style' ] ],
		];
	}

	/**
	 * @return array[]
	 */
	public function exceptionThrowingMethodsProvider() {
		return [
			'getAttributesFor' => [ 'getAttributesFor' ],
			'getClassFor'      => [ 'getClassFor' ],
			'getNameFor'       => [ 'getNameFor' ],
		];
	}

	/**
	 * @return array
	 */
	public function handlerTypeProvider() {
		return [
			'accordion' => [ 'accordion', false ],
			'panel'     => [ 'panel', false ],
			'popover'   => [ 'popover', false ],
			'button'    => [ 'button', true ],
			'tooltip'   => [ 'tooltip', true ],
		];
	}

	/**
	 * @return array[]
	 */
	public function modulesForComponentsProvider() {
		return [
			'button'          => [
				'button',
				null,
				[ 'ext.bootstrapComponents.button.fix' ],
			],
			'button_vector'   => [
				'button',
				'vector',
				[ 'ext.bootstrapComponents.button.fix' ],
			],
			'carousel'        => [
				'carousel',
				null,
				[ 'ext.bootstrapComponents.carousel.fix' ],
			],
			'carousel_vector' => [
				'carousel',
				'vector',
				[ 'ext.bootstrapComponents.carousel.fix' ],
			],
			'modal'           => [
				'modal',
				null,
				[ 'ext.bootstrapComponents.button.fix', 'ext.bootstrapComponents.modal.fix' ],
			],
			'modal_vector'    => [
				'modal',
				'vector',
				[ 'ext.bootstrapComponents.button.fix', 'ext.bootstrapComponents.modal.fix', 'ext.bootstrapComponents.modal.vector-fix' ],
			],
			'popover'         => [
				'popover',
				null,
				[ 'ext.bootstrapComponents.button.fix', 'ext.bootstrapComponents.popover' ],
			],
			'popover_vector'  => [
				'popover',
				'vector',
				[ 'ext.bootstrapComponents.button.fix', 'ext.bootstrapComponents.popover', 'ext.bootstrapComponents.popover.vector-fix', ],
			],
			'tooltip'         => [
				'tooltip',
				null,
				[ 'ext.bootstrapComponents.tooltip' ],
			],
			'tooltip_vector'  => [
				'tooltip',
				'vector',
				[ 'ext.bootstrapComponents.tooltip' ],
			],
		];
	}

	/**
	 * @return array
	 */
	public function whiteListProvider() {
		return [
			'true'     => [
				true, array_keys( $this->componentNameAndClassProvider() ),
			],
			'false'    => [
				false, [],
			],
			'normal' => [
				[ 'alert', 'card', 'modal' ],
				[ 'alert', 'card', 'modal', ],
			],
			'alias w/ corresponding component' => [
				[ 'alert', 'card', 'modal', 'panel' ],
				[ 'alert', 'card', 'modal', 'panel' ],
			],
			'alias w/o corresponding component' => [
				[ 'alert', 'modal', 'panel' ],
				[ 'alert', 'modal', 'panel', ],
			],
			'manual 2' => [
				[ 'collapse', 'jumbotron', 'well', 'foobar' ],
				[ 'collapse', 'jumbotron', 'well' ],
			],
		];
	}
}
