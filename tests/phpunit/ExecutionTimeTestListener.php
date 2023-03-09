<?php

namespace BootstrapComponents\Tests;

use PHPUnit_Framework_AssertionFailedError;
use PHPUnit_Framework_Test;
use PHPUnit_Framework_TestListener;
use PHPUnit_Framework_TestSuite;
use PHPUnit_Framework_Warning;
use Throwable;

/**
 * Class ExecutionTimeTestListener
 *
 * @since   1.1
 *
 * @package BootstrapComponents\Tests
 */
class ExecutionTimeTestListener implements PHPUnit_Framework_TestListener {

	protected $testCollector = [];
	protected $executionTimeThresholdInSeconds = 10;
	protected $isEnabledToListen = true;

	/**
	 * ExecutionTimeTestListener constructor.
	 *
	 * @param bool $isEnabledToListen
	 * @param int  $executionTimeThresholdInSeconds
	 */
	public function __construct( $isEnabledToListen, $executionTimeThresholdInSeconds ) {
		$this->isEnabledToListen = $isEnabledToListen;
		$this->executionTimeThresholdInSeconds = $executionTimeThresholdInSeconds;
	}

	/**
	 * @see PHPUnit_Framework_TestListener::startTest
	 */
	public function startTest( PHPUnit_Framework_Test $test ): void {
	}

	/**
	 * @see PHPUnit_Framework_TestListener::endTest
	 */
	public function endTest( PHPUnit_Framework_Test $test, float $length ): void {
		if ( $this->isEnabledToListen && ($length > $this->executionTimeThresholdInSeconds) ) {
			$this->testCollector[$test->getName()] = round( $length, 3 );
		}
	}

	/**
	 * @see PHPUnit_Framework_TestListener::addError
	 */
	public function addError( PHPUnit_Framework_Test $test, Throwable $e, float $time ): void {
	}

	/**
	 * @see PHPUnit_Framework_TestListener::addFailure
	 */
	public function addFailure( PHPUnit_Framework_Test $test, PHPUnit_Framework_AssertionFailedError $e, float $time ): void {
	}

	/**
	 * @see PHPUnit_Framework_TestListener::addError
	 */
	public function addIncompleteTest( PHPUnit_Framework_Test $test, Throwable $e, float $time ): void {
	}

	/**
	 * @see   PHPUnit_Framework_TestListener::addRiskyTest
	 * @since 4.0.0
	 */
	public function addRiskyTest( PHPUnit_Framework_Test $test, Throwable $e, float $time ): void {
	}

	/**
	 * @see PHPUnit_Framework_TestListener::addSkippedTest
	 */
	public function addSkippedTest( PHPUnit_Framework_Test $test, Throwable $e, float $time ): void {
	}

	/**
	 * @see   PHPUnit_Framework_TestListener::addWarning
	 * @since 6.0.0
	 */
	public function addWarning( PHPUnit_Framework_Test $test, PHPUnit_Framework_Warning $e, float $time ): void {
	}

	/**
	 * @see PHPUnit_Framework_TestListener::startTestSuite
	 */
	public function startTestSuite( PHPUnit_Framework_TestSuite $suite ): void {
	}

	/**
	 * @see PHPUnit_Framework_TestListener::endTestSuite
	 */
	public function endTestSuite( PHPUnit_Framework_TestSuite $suite ): void {
		foreach ( $this->testCollector as $name => $length ) {
			print ("\n" . $suite->getName() . " {$name} ran for {$length} seconds" . "\n");
			unset( $this->testCollector[$name] );
		}
	}

}
