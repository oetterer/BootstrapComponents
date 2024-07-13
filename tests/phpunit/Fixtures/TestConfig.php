<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests\Fixtures;

use ArrayIterator;
use Config;
use ConfigException;
use MediaWiki\Config\IterableConfig;
use MutableConfig;
use Traversable;

class TestConfig implements Config, MutableConfig, IterableConfig
{
	/**
	 * @var array
	 */
	private array $settings;

	/**
	 * @var array
	 */
	private array $originalSetting;

	public function __construct( $prefix = 'wg' ) {
		$this->originalSetting = $this->prepareConfig( $prefix );
		$this->settings = $this->originalSetting;
	}

	public function get( $name ) {
		if ( !$this->has( $name ) ) {
			throw new ConfigException( __METHOD__ . ": undefined option: '$name'" );
		}

		return $this->settings[$name];
	}

	public function getIterator(): Traversable {
		return new ArrayIterator( $this->settings );
	}

	public function getNames(): array {
		return array_keys( $this->settings );
	}

	public function has( $name ): bool {
		return array_key_exists( $name, $this->settings );
	}

	public function reset(): array {
		return $this->settings = $this->originalSetting;
	}

	/**
	 * @see MutableConfig::set
	 * @param string $name
	 * @param mixed $value
	 */
	public function set( $name, $value ): void {
		$this->settings[$name] = $value;
	}

	protected function prepareConfig( string $prefix): array {
		$config = [];
		foreach ( $GLOBALS as $key => $value ) {
			$matches = [];
			if ( preg_match( '/^' . $prefix . '(.+)$/', $key, $matches ) ) {
				$config[$matches[1]] = $value;
			}
		}
		return $config;
	}
}
