<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use MediaWiki\Extension\BootstrapComponents\LuaLibrary;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\LuaLibrary
 *
 * @group   Database
 *
 * @license GNU GPL v2+
 * @since   1.1
 *
 * @author  Tobias Oetterer
 */
class LuaLibraryTest extends LuaLibraryTestBase {

	/**
	 * Lua test module
	 *
	 * @var string
	 */
	protected static $moduleName = self::class;

	/**
	 * LuaLibraryTestTest::getTestModules
	 */
	public function getTestModules() {
		return parent::getTestModules() + [
				self::$moduleName => __DIR__ . '/' . 'mw.bootstrap.tests.lua',
			];
	}


	public function testCanConstruct() {
		$this->assertInstanceOf(
			LuaLibrary::class,
			$this->getLuaLibrary()
		);
	}

	/**
	 * Test, if all the necessary methods exists. Uses data provider {@see dataProviderFunctionTest}.
	 *
	 * @param string $method name of method to check
	 *
	 * @dataProvider dataProviderFunctionTest
	 */
	public function testMethodsExist( $method ) {
		$this->assertTrue(
			method_exists( $this->getLuaLibrary(), $method ),
			'Class MediaWiki\\Extension\\BootstrapComponents\\LuaLibrary has method \'' . $method . '()\' missing!'
		);
	}

	/**
	 * Data provider for {@see testMethodsExist}
	 *
	 * @see testMethodsExist
	 *
	 * @return array
	 */
	public function dataProviderFunctionTest() {

		return [
			[ 'parse' ],
			[ 'getSkin' ],
		];
	}
}
