<?php

namespace MediaWiki\Extension\BootstrapComponents;

use MediaWiki\Config\Config;
use MediaWiki\Context\RequestContext;

class BootstrapComponentsService
{

	/**
	 * List of active components on the page
	 */
	private array $activeComponents = [];

	private bool $modalsSuppressedByMagicWord = false;

	/**
	 * Holds the name of the skin we use (or false, if there is no skin).
	 */
	private string $nameOfActiveSkin;

	public function __construct( private readonly Config $mainConfig ) {
	}


	/**
	 * @return bool
	 */
	public function areModalsSuppressedByMagicWord(): bool {
		return $this->modalsSuppressedByMagicWord;
	}

	/**
	 * @return string
	 */
	public function getNameOfActiveSkin(): string {
		if ( empty( $this->nameOfActiveSkin ) ) {
			$this->nameOfActiveSkin = $this->detectSkinInUse(
				defined( 'MW_NO_SESSION' )
			);
		}
		return $this->nameOfActiveSkin;
	}

	public function getActiveComponents(): array {
		return array_keys( $this->activeComponents );
	}

	/**
	 * Registers a component type as active on the current page. Will be used to calculate the
	 * required modules for the page later on.
	 *
	 * @param string $componentName
	 *
	 * @return void
	 */
	public function registerComponentAsActive( string $componentName ): void {
		$this->activeComponents[$componentName] = true;
	}

	/**
	 * @param bool $modalsSuppressedByMagicWord
	 * @return void
	 */
	public function setModalsSuppressedByMagicWord( bool $modalsSuppressedByMagicWord ): void {
		$this->modalsSuppressedByMagicWord = $modalsSuppressedByMagicWord;
	}

	/**
	 * Returns true, if active skin is vector
	 *
	 * @return bool
	 */
	public function vectorSkinInUse(): bool {
		return in_array( strtolower( $this->getNameOfActiveSkin() ), [ 'vector', 'vector-2022' ] ) ;
	}

	/**
	 * Whether the given skin ships its own Bootstrap JavaScript.
	 */
	public function skinProvidesBootstrapScripts( string $skin ): bool {
		return $this->getBootstrapScriptsModule( $skin ) !== 'ext.bootstrap.scripts';
	}

	/**
	 * The ResourceLoader script module carrying the Bootstrap this skin should use: the skin's own on
	 * skins that ship Bootstrap, Extension:Bootstrap's `ext.bootstrap.scripts` otherwise. The
	 * component initialization waits on this module.
	 */
	public function getBootstrapScriptsModule( string $skin ): string {
		return match ( strtolower( $skin ) ) {
			'medik' => 'skins.medik.js',
			'tweeki' => $this->getTweekiScriptModule(),
			default => 'ext.bootstrap.scripts',
		};
	}

	/**
	 * Mirrors Tweeki's own script-module selection ({@see \SkinTweeki::initPage}), so we depend on
	 * whichever module the running wiki actually loads Bootstrap through.
	 */
	private function getTweekiScriptModule(): string {
		if ( $this->mainConfig->has( 'TweekiSkinCustomScriptModule' )
			&& $this->mainConfig->get( 'TweekiSkinCustomScriptModule' )
		) {
			return $this->mainConfig->get( 'TweekiSkinCustomScriptModule' );
		}
		if ( $this->mainConfig->has( 'TweekiSkinUseCustomFiles' )
			&& $this->mainConfig->get( 'TweekiSkinUseCustomFiles' )
		) {
			return 'skins.tweeki.custom.scripts';
		}
		return 'skins.tweeki.scripts';
	}

	public function getBootstrapStylesModule( string $skin ): ?string {
		return $this->skinProvidesBootstrapStyles( $skin ) ? null : 'ext.bootstrap.styles';
	}

	/**
	 * Skins that put the Bootstrap stylesheet on the page themselves. Chameleon belongs here but
	 * not in the scripts map: it registers Extension:Bootstrap's stylesheet under its own module
	 * name (zzz.ext.bootstrap.styles), which ResourceLoader cannot deduplicate against
	 * ext.bootstrap.styles, while its Bootstrap JavaScript is ext.bootstrap.scripts itself.
	 */
	private const SKINS_WITH_OWN_BOOTSTRAP_STYLES = [ 'chameleon', 'medik', 'tweeki' ];

	public function skinProvidesBootstrapStyles( string $skin ): bool {
		return in_array( strtolower( $skin ), self::SKINS_WITH_OWN_BOOTSTRAP_STYLES, true );
	}

	/**
	 * @param bool $useConfig   set this to true, if we can't rely on {@see \RequestContext::getSkin}
	 *
	 * @return string
	 */
	protected function detectSkinInUse( bool $useConfig = false ): string {
		if ( !$useConfig ) {
			$skin = RequestContext::getMain()->getSkin();
			if ( !empty( $skin ) && is_a( $skin, 'Skin' ) ) {
				return $skin->getSkinName();
			}
		}
		return $this->mainConfig->has( 'DefaultSkin' )
			? strtolower( $this->mainConfig->get( 'DefaultSkin' ) ) : 'unknown';
	}
}
