<?php

namespace BootstrapComponents\Tests\Unit;

/**
 * @covers \BootstrapComponents\LuaLibrary
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
class LuaLibraryParseTest extends LuaLibraryTestBase {

	/**
	 * Lua test module
	 * @var string
	 */
	protected static $moduleName = self::class;

	/**
	 * LuaLibraryTestBase::getTestModules
	 */
	public function getTestModules() {
		return parent::getTestModules() + array(
			self::$moduleName => __DIR__ . '/' . 'mw.bootstrap.parse.tests.lua',
		);
	}


	/**
	 * Tests method parse
	 */
	public function testParse() {
		$this->assertArrayHasKey(
			0,
			$this->getLuaLibrary()->parse( '', '', [] )
		);
		$this->assertEquals(
			'No component name provided for mw.bootstrap.parse.',
			$this->getLuaLibrary()->parse( '', '', [] )[0]
		);
		$this->assertEquals(
			'Invalid component name passed to mw.bootstrap.parse: foobar.',
			$this->getLuaLibrary()->parse( 'foobar', '', [] )[0]
		);
		$this->assertEquals(
			'<span class="glyphicon glyphicon-asterisk"></span>',
			$this->getLuaLibrary()->parse( 'icon', 'asterisk', [] )[0]
		);
	}
}