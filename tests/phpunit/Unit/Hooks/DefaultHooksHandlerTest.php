<?php

namespace BootstrapComponents\Tests\Unit\Hooks;

use BootstrapComponents\Hooks\DefaultHooksHandler;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\Hooks\DefaultHooksHandler
 *
 * @ingroup Test
 *
 * @group   extension-bootstrap-components
 * @group   mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   1.2
 * @author  Tobias Oetterer
 */
class DefaultHooksHandlerTest extends PHPUnit_Framework_TestCase {

	/**
	 * @throws \ConfigException
	 */
	public function testOnScribuntoExternalLibraries() {
		$libraries = [];
		$this->assertTrue(
			DefaultHooksHandler::onScribuntoExternalLibraries( '', $libraries )
		);
		$this->assertEquals(
			[],
			$libraries
		);
		$this->assertTrue(
			DefaultHooksHandler::onScribuntoExternalLibraries( 'lua', $libraries )
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
}
