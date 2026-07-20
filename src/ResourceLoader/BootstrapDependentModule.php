<?php

namespace MediaWiki\Extension\BootstrapComponents\ResourceLoader;

use MediaWiki\ResourceLoader\Context;
use MediaWiki\ResourceLoader\FileModule;

/**
 * Adds the active skin's Bootstrap scripts module as a dependency: the skin's own module when it
 * ships Bootstrap, otherwise ext.bootstrap.scripts. It is a dependency, rather than merely present
 * on the page, because the component init scripts call the Bootstrap global and must load after it.
 */
class BootstrapDependentModule extends FileModule {

	private const DEFAULT_BOOTSTRAP_SCRIPTS_MODULE = 'ext.bootstrap.scripts';

	/** Skins that bundle Bootstrap and expose the window.bootstrap global, mapped to their scripts module. */
	private const BOOTSTRAP_SCRIPTS_MODULE_BY_SKIN = [
		'medik' => 'skins.medik.js',
	];

	/** @inheritDoc */
	public function getDependencies( ?Context $context = null ) {
		$skin = $context?->getSkin() ?? '';
		$bootstrap = self::BOOTSTRAP_SCRIPTS_MODULE_BY_SKIN[ $skin ] ?? self::DEFAULT_BOOTSTRAP_SCRIPTS_MODULE;

		return array_merge( parent::getDependencies( $context ), [ $bootstrap ] );
	}
}
