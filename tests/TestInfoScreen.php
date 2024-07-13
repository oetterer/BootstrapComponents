<?php

namespace MediaWiki\Extension\BootstrapComponents\Tests;

/**
 * @private
 *
 * @license GNU GPL v2+
 * @since 4.0
 *
 * @author oetterer
 */
class TestInfoScreen {
	private $currentBlock = [];
	private $infoBlocks = [];
	private $screenTab = 20;

	public function __construct( int $tabulatorWidth = 0 ) {
		if ( $tabulatorWidth ) {
			$this->screenTab = $tabulatorWidth;
		}
	}

	/**
	 * Adds a line to the current info block
	 *
	 * @param string $name
	 * @param string $value
	 * @return bool
	 */
	public function addInfoToBlock( string $name, string $value ): bool {
		if ( !empty( $name ) ) {
			$this->currentBlock[] = [ $name, $value ];

			return true;
		}

		return false;
	}

	/**
	 * ends the current info block and starts a new one
	 * @return bool
	 */
	public function newBlock(): bool {
		if ( !empty( $this->currentBlock ) ) {
			$this->infoBlocks[] = $this->currentBlock;
			$this->currentBlock = [];

			return true;
		}

		return false;
	}

	/**
	 * outputs all the registered info blocks
	 */
	public function printScreen() {
		if ( !empty( $this->currentBlock ) ) {
			$this->newBlock();
		}
		$this->writeHeader();
		foreach ( $this->infoBlocks as $infoBlock ) {
			foreach ( $infoBlock as $lineData ) {
				$this->writeLine( $lineData );
			}
			$this->writeNewLine();
		}
	}

	/**
	 * Outputs the test info screen header
	 */
	protected function writeHeader() {
		$this->writeNewLine();
		print "   *************************************\n";
		print "   *                                   *\n";
		print "   *   Unit test summary information   *\n";
		print "   *                                   *\n";
		print "   *************************************\n";
		$this->writeNewLine();
	}

	/**
	 * used to write a line on screen, takes elements from array $data
	 *
	 * @param array $data
	 */
	protected function writeLine( array $data ) {
		vprintf( "%-{$this->screenTab}s%s\n", $data );
	}

	/**
	 * outputs a new line
	 */
	protected function writeNewLine() {
		print  "\n";
	}
}
