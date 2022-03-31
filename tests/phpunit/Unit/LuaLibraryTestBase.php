<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\LuaLibrary;
use \Scribunto_LuaEngineTestBase;

/**
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
abstract class LuaLibraryTestBase extends Scribunto_LuaEngineTestBase
{
	/**
	 * @var LuaLibrary
	 */
	private $luaLibrary;

	/**
	 * @throws \MWException
	 */
	protected function setUp(): void {
		parent::setUp();

		/** @noinspection PhpParamsInspection */
		$this->luaLibrary = new LuaLibrary(
			$this->getEngine()
		);
	}

	/**
	 * Accesses an instance of class {@see LuaLibrary}
	 *
	 * @return LuaLibrary LuaLibrary
	 */
	public function getLuaLibrary() {
		return $this->luaLibrary;
	}
}
