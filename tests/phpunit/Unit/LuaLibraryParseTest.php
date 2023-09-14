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
 * @since   1.1
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
	public function getTestModules(): array
	{
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
			'<div class="card border-danger"><div id="FooBar"><div class="card-body text-danger">Lorem Ipsum</div></div></div>',
			$this->getLuaLibrary()->parse( 'card', 'Lorem Ipsum', [ 'color' => 'danger', 'id' => 'FooBar' ], true )[0]
		);
	}
}
