<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

/**
 * @covers  \MediaWiki\Extension\BootstrapComponents\LuaLibrary
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
class LuaLibraryGetSkinTest extends LuaLibraryTestBase {

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
			self::$moduleName => __DIR__ . '/' . 'mw.bootstrap.getSkin.tests.lua',
		);
	}


	/**
	 * Tests method parse
	 */
	public function testParse() {
		$this->assertArrayHasKey(
			0,
			$this->getLuaLibrary()->getSkin()
		);
		$this->assertEquals(
			'vector',
			$this->getLuaLibrary()->getSkin()[0]
		);
	}
}
