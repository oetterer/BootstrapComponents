<?php

namespace MediaWiki\Extension\BootstrapComponents;

use Config;
use RequestContext;

class BootstrapComponentsService
{

	/**
	 * List of active components on the page
	 *
	 * @var array $activeComponents
	 */
	private array $activeComponents;

	/**
	 * @var Config
	 */
	private Config $mainConfig;


	/**
	 * Holds the name of the skin we use (or false, if there is no skin).
	 *
	 * @var string $nameOfActiveSkin
	 */
	private string $nameOfActiveSkin;

	public function __construct( Config $mainConfig ) {
		$this->mainConfig = $mainConfig;
		$this->activeComponents = [];
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
	 * Returns true, if active skin is vector
	 *
	 * @return bool
	 */
	public function vectorSkinInUse(): bool {
		return in_array( strtolower( $this->getNameOfActiveSkin() ), [ 'vector', 'vector-2022' ] ) ;
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
