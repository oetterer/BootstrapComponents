<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests;

use \GitInfo;

/**
 * @private
 *
 * @license GNU GPL v2+
 * @since   3.0
 *
 * @author  mwjames
 */
class PHPUnitEnvironment {

	/**
	 * @var array
	 */
	const EXTENSION_ALIASES = [
		'bs' => 'Bootstrap',
		'bsc' => 'BootstrapComponents',
		'mw' => 'MediaWiki',
		'smw' => 'SemanticMediaWiki',
	];

	/**
	 * @var array
	 */
	private $extensionRegistryData = [];

	/**
	 * @var int
	 */
	private $firstColumnWidth = PHPUNIT_FIRST_COLUMN_WIDTH;

	/**
	 * @param array $args
	 *
	 * @return boolean
	 */
	public function hasDebugRequest( $args ) {
		return array_search( '--debug', $args ) || array_search( '--debug-tests', $args );
	}

	public function emptyDebugVars() {
		$GLOBALS['wgDebugLogGroups'] = [];
		$GLOBALS['wgDebugLogFile'] = '';
	}

	/**
	 * @return boolean
	 */
	public function enabledDebugLogs(): bool {
		return $GLOBALS['wgDebugLogGroups'] !== [] || $GLOBALS['wgDebugLogFile'] !== '';
	}

	/**
	 * @return boolean|integer
	 */
	public function getXdebugInfo() {

		if ( extension_loaded( 'xdebug' ) &&
			( function_exists( 'xdebug_is_enabled' ) || function_exists( 'xdebug_info' ) ) ) {
			return phpversion( 'xdebug' );
		}

		return false;
	}

	/**
	 * @return boolean|string
	 */
	public function getIntlInfo(): string {

		if ( extension_loaded( 'intl' ) ) {
			return phpversion( 'intl' ) . ' / ' . INTL_ICU_VERSION;
		}

		return "unknown";
	}

	/**
	 * @return string
	 */
	public function getPcreInfo(): string {
		return defined( 'PCRE_VERSION' ) ? PCRE_VERSION : "unknown";
	}

	public function getPhpUnitVersion(): string {
		return \PHPUnit\Runner\Version::id();
	}

	/**
	 * @return string
	 */
	public function getDbType(): string {
		return $GLOBALS['wgDBtype'];
	}

	/**
	 * @return string
	 */
	public function getSiteLanguageCode(): string {
		return $GLOBALS['wgLanguageCode'];
	}

	/**
	 * @return string
	 * @throws \Exception
	 */
	public function executionTime(): string {
		$dateTimeUtc = new \DateTime( 'now', new \DateTimeZone( 'UTC' ) );

		return $dateTimeUtc->format( 'Y-m-d h:i' );
	}

	/**
	 * Provides information (version, git commit) about software $name
	 *
	 * @param string $name can be "mediawiki" or extension; accepts aliases
	 * @return string
	 */
	public function getSoftwareInfo( string $name ): string {
		$resolvedName = $this->resolveExtensionAlias( $name );
		$extensionInfo = [];

		if ( $resolvedName == 'MediaWiki' ) {
			$extensionInfo[] = MW_VERSION;
		} elseif ( $resolvedName == 'SemanticMediaWiki' ) {
			if ( defined( 'SMW_VERSION' ) ) {
				$extensionInfo[] = SMW_VERSION;
			} else {
				$extensionInfo[] = 'N/A';
			}
		} else {
			$extensionInfo[] = $this->getExtensionVersionFromRegistry( $resolvedName );
		}

		$extensionInfo[] = 'git: ' . $this->getGitInfo( $resolvedName );

		return implode( ', ', $extensionInfo );
	}

	/**
	 * @param string $extension
	 *
	 * @return string
	 */
	protected function getExtensionVersionFromRegistry( string $extension ): string {
		if ( empty( $this->extensionRegistryData ) ) {
			$this->extensionRegistryData = \ExtensionRegistry::getInstance()->getAllThings();
		}
		if ( !empty( $this->extensionRegistryData[$extension]['version'] ) ) {
			return $this->extensionRegistryData[$extension]['version'];
		}

		return 'unknown';
	}

	protected function getGitInfo( string $item ): string {
		$hash = false;
		if ( $item == 'MediaWiki' ) {
			if ( class_exists( 'GitInfo' ) ) {
				$hash = GitInfo::headSHA1();
			}
		} elseif ( is_dir( __DIR__ . '/../../' . $item ) ) {
			$gitInfo = new GitInfo( __DIR__ . '/../../' . $item );
			$hash = $gitInfo->getHeadSHA1();
		}
		if ( $hash ) {
			return substr( $hash, 0, 7 );
		}

		return 'N/A';
	}

	protected function resolveExtensionAlias( $alias ) {
		$aliases = self::EXTENSION_ALIASES;
		if ( empty( $aliases[$alias] ) ) {
			return $alias;
		}

		return $aliases[$alias];
	}

	private function command_exists( $command ): bool {
		$isWin = strtolower( substr( PHP_OS, 0, 3 ) ) === 'win';

		$spec = [
			[ "pipe", "r" ],
			[ "pipe", "w" ],
			[ "pipe", "w" ],
		];

		$proc = proc_open( ( $isWin ? 'where' : 'which' ) . " $command", $spec, $pipes );

		if ( is_resource( $proc ) ) {
			$stdout = stream_get_contents( $pipes[1] );
			$stderr = stream_get_contents( $pipes[2] );

			fclose( $pipes[1] );
			fclose( $pipes[2] );

			proc_close( $proc );

			return $stdout != '';
		}

		return false;
	}
}
