<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use ConfigException;
use MediaWiki\Extension\BootstrapComponents\ComponentLibrary;
use PHPUnit\Framework\TestCase;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\ComponentLibrary
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
class ComponentLibraryTest extends TestCase {

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
		$instance = new ComponentLibrary( true );
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
	 * @throws ConfigException
	 *
	 * @dataProvider componentNameAndClassProvider
	 */
	public function testIsRegistered( string $componentName ) {
		$instance = new ComponentLibrary( true );
		$this->assertEquals(
			true,
			$instance->isRegistered( $componentName )
		);
	}

	/**
	 * @param string   $component
	 * @param string[] $expectedAliases
	 *
	 * @throws ConfigException
	 * @throws \MWException
	 *
	 * @dataProvider componentAliasesProvider
	 */
	public function testGetAliasesFor( string $component, array $expectedAliases ) {
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
	 * @throws ConfigException
	 * @throws \MWException
	 *
	 * @dataProvider componentAttributesProvider
	 */
	public function testGetAttributesFor( string $component, array $expectedAttributes ) {
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
	 * @throws ConfigException
	 *
	 * @dataProvider componentNameAndClassProvider
	 */
	public function testGetClassFor( string $componentName, string $componentClass ) {
		$instance = new ComponentLibrary();
		$this->assertEquals(
			$componentClass,
			$instance->getClassFor( $componentName )
		);
	}

	/**
	 * @throws ConfigException
	 */
	public function testGetAllRegisteredComponents() {
		$instance = new ComponentLibrary();
		$allKeys = array_keys( $this->componentNameAndClassProvider() );
		sort( $allKeys );
		$this->assertEquals(
			$allKeys,
			$instance->getRegisteredComponents()
		);
	}

	/**
	 * @param string $componentName
	 *
	 * @throws ConfigException
	 *
	 * @dataProvider componentNameAndClassProvider
	 */
	public function testGetHandlerTypeFor( string $componentName ) {
		$instance = new ComponentLibrary();

		$this->assertContains(
			$instance->getHandlerTypeFor( $componentName ),
			[ ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION, ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION ]
		);
	}

	/**
	 * @throws ConfigException
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
	 * @throws ConfigException
	 *
	 * @dataProvider handlerTypeProvider
	 */
	public function testIsHandlerType( string $componentName, bool $isParserFunction ) {
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
	 * @param string|null $skinName
	 * @param array  $expectedModules
	 *
	 * @throws ConfigException
	 *
	 * @dataProvider modulesForComponentsProvider
	 */
	public function testGetModulesFor( string $componentName, ?string $skinName, array $expectedModules ) {
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
	 * @throws ConfigException
	 * @throws \MWException
	 *
	 * @dataProvider componentNameAndClassProvider
	 */
	public function testGetNameFor( string $componentName, string $componentClass ) {
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
	 * @throws ConfigException
	 *
	 * @dataProvider whiteListProvider
	 */
	public function testSetWhiteList( bool|array $whiteList, array $expectedComponents ) {
		$instance = new ComponentLibrary( $whiteList );
		$this->assertEquals(
			$expectedComponents,
			$instance->getRegisteredComponents()
		);
	}

	/**
	 * @param string $method
	 * @param mixed $param
	 *
	 * @throws ConfigException
	 *
	 * @dataProvider exceptionThrowingMethodsProvider
	 */
	public function testFails( $method, $param ) {
		$instance = new ComponentLibrary();

		$this->expectException( 'MWException' );

		call_user_func_array( [ $instance, $method ], [ $param ] );
	}

	/**
	 * @throws ConfigException
	 */
	public function testRegisterVsKnown() {
		$instance = new ComponentLibrary( [ 'alert', 'badge', 'modal', 'panel' ] );
		$this->assertEquals(
			[ 'alert', 'badge', 'modal', 'panel', ],
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
				$instance->getModulesFor( $component, $skin ),
				'Failed ComponentLibrary:getModulesFor() for test-data ' . $component
			);
		}
		$this->assertFalse(
			$instance->isRegistered( 'well' )
		);
	}

	/**
	 * @throws ConfigException
	 */
	public function testUnknownComponentName() {
		$instance = new ComponentLibrary( true );

		$this->expectException( 'MWException' );
		$instance->getClassFor( 'foobar' );
	}

	/**
	 * @throws ConfigException
	 */
	public function testUnknownComponentClass() {
		$instance = new ComponentLibrary( true );

		$this->expectException( 'MWException' );
		$instance->getNameFor( '\BootstrapComponents\Components\Foobar' );
	}

	/**
	 * @return array
	 */
	public function compileParserHookStringProvider(): array {
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
	public function componentNameAndClassProvider(): array {
		return [
			'accordion' => [ 'accordion', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Accordion' ],
			'alert'     => [ 'alert', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Alert' ],
			'badge'     => [ 'badge', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Badge' ],
			'button'    => [ 'button', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Button' ],
			'card'      => [ 'card', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Card' ],
			'carousel'  => [ 'carousel', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Carousel' ],
			'collapse'  => [ 'collapse', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Collapse' ],
			'jumbotron' => [ 'jumbotron', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Jumbotron' ],
			'modal'     => [ 'modal', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Modal' ],
			'popover'   => [ 'popover', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Popover' ],
			'tooltip'   => [ 'tooltip', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Tooltip' ],
			'label'     => [ 'badge', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Badge' ],
			'panel'     => [ 'card', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Card' ],
			'well'      => [ 'card', 'MediaWiki\\Extension\\BootstrapComponents\\Components\\Card' ],
		];
	}

	/**
	 * @return array
	 */
	public function componentAliasesProvider(): array {
		return [
			'alert' => [ 'alert', [] ],
			'button' => [ 'button', [] ],
			'card' => [ 'card', [ 'footing' => 'footer', 'heading' => 'header', 'title' => 'header', 'footerimage' => 'footer-image', 'headerimage' => 'header-image', ], ],
			'panel' => [ 'panel', [ 'footing' => 'footer', 'heading' => 'header', 'title' => 'header', 'footerimage' => 'footer-image', 'headerimage' => 'header-image', ], ],
		];
	}

	/**
	 * @return array
	 */
	public function componentAttributesProvider(): array {
		return [
			'accordion' => [ 'accordion', [ 'class', 'id', 'style' ] ],
			'alert'     => [ 'alert', [ 'color', 'dismissible', 'class', 'id', 'style' ] ],
			'modal'     => [ 'modal', [ 'color', 'footer', 'header', 'size', 'text', 'class', 'id', 'style' ] ],
		];
	}

	/**
	 * @return array[]
	 */
	public function exceptionThrowingMethodsProvider(): array {
		return [
			'getAttributesFor' => [ 'getAttributesFor', 'FooBar' ],
			'getClassFor'      => [ 'getClassFor', 'FooBar' ],
			'getNameFor'       => [ 'getNameFor', 'FooBar' ],
		];
	}

	/**
	 * @return array
	 */
	public function handlerTypeProvider(): array {
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
	public function modulesForComponentsProvider(): array {
		return [
			'badge'           => [
				'badge',
				null,
				[]
			],
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
				[ 'ext.bootstrapComponents.button.fix', 'ext.bootstrapComponents.popover.fix' ],
			],
			'popover_vector'  => [
				'popover',
				'vector',
				[ 'ext.bootstrapComponents.button.fix', 'ext.bootstrapComponents.popover.fix', 'ext.bootstrapComponents.popover.vector-fix', ],
			],
			'tooltip'         => [
				'tooltip',
				null,
				[ 'ext.bootstrapComponents.tooltip.fix' ],
			],
			'tooltip_vector'  => [
				'tooltip',
				'vector',
				[ 'ext.bootstrapComponents.tooltip.fix' ],
			],
		];
	}

	/**
	 * @return array
	 */
	public function whiteListProvider(): array {
		$allKeys = array_keys( $this->componentNameAndClassProvider() );
		sort( $allKeys );
		return [
			'true' => [
				true, $allKeys,
			],
			'false' => [
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
