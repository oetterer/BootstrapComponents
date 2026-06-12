<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Unit;

use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
use MediaWiki\Extension\BootstrapComponents\ComponentLibrary;
use MediaWiki\Extension\BootstrapComponents\NestingController;
use MediaWiki\MediaWikiServices;
use PHPUnit\Framework\TestCase;

/**
 * @ingroup Test
 *
 * @group   extension-bootstrap-components
 * @group   mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   5.2
 * @author  Tobias Oetterer
 */
class ServiceWiringTest extends TestCase {


	/**
	 * @covers  \MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService
	 */
	public function testCanConstructBootstrapComponentsService()	{
		$this->assertInstanceOf(
			BootstrapComponentsService::class,
			MediaWikiServices::getInstance()->getService('BootstrapComponentsService')
		);
	}

	/**
	 * @covers  \MediaWiki\Extension\BootstrapComponents\ComponentLibrary
	 */
	public function testCanConstructComponentLibrary()	{
		$this->assertInstanceOf(
			ComponentLibrary::class,
			MediaWikiServices::getInstance()->getService('BootstrapComponents.ComponentLibrary')
		);
	}

	/**
	 * @covers  \MediaWiki\Extension\BootstrapComponents\NestingController
	 */
	public function testCanConstructNestingController()	{
		$this->assertInstanceOf(
			NestingController::class,
			MediaWikiServices::getInstance()->getService('BootstrapComponents.NestingController')
		);
	}
}
