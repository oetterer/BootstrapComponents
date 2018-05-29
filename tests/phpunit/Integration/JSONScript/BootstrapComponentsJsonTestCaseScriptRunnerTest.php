<?php

namespace BootstrapComponents\Tests\Integration;

use BootstrapComponents\ApplicationFactory;
use BootstrapComponents\Hooks\OutputPageParserOutput;
use BootstrapComponents\Setup;
use SMW\DIWikiPage;
use SMW\Tests\JsonTestCaseFileHandler;
use SMW\Tests\JsonTestCaseScriptRunner;
use SMW\Tests\Utils\Validators\StringValidator;


/**
 * @see https://github.com/SemanticMediaWiki/SemanticMediaWiki/tree/master/tests#write-integration-tests-using-json-script
 *
 * `JsonTestCaseScriptRunner` provisioned by SMW is a base class allowing to use a JSON
 * format to create test definitions with the objective to compose "real" content
 * and test integration with MediaWiki, Semantic MediaWiki, and Scribunto.
 *
 * The focus is on describing test definitions with its content and specify assertions
 * to control the expected base line.
 *
 * `JsonTestCaseScriptRunner` will handle the tearDown process and ensures that no test
 * data are leaked into a production system but requires an active DB connection.
 *
 * @group extension-bootstrap-components
 * @group medium
 *
 * @license GNU GPL v3+
 * @since 1.0
 *
 * @author mwjames
 * @author Tobias Oetterer
 */
class BootstrapComponentsJsonTestCaseScriptRunnerTest extends JsonTestCaseScriptRunner {

	/**
	 * @var StringValidator
	 */
	private $stringValidator;

	/**
	 * @var Setup
	 */
	private $setup;

	/**
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	protected function setUp() {
		wfDebugLog( 'BootstrapComponents', 'Running the JsonTestCaseScriptRunnerTest setup.' );
		parent::setUp();

		$validatorFactory = $this->testEnvironment->getUtilityFactory()->newValidatorFactory();

		$this->stringValidator = $validatorFactory->newStringValidator();

		// at this point, I normally would need to reset the complete lookup in the ApplicationFactory.
		// Unfortunately, this causes problems with the re-registering of the parser function hooks and
		// passing them the correct new NestingController
		// here's to hoping, this is only botched in testing environment.
		ApplicationFactory::getInstance()->resetLookup( 'ParserOutputHelper' );

		#@fixme this is foobar to make modals work in integration tests. find a better solution
		# see also \BootstrapComponents\AbstractComponent::getParserOutputHelper
		if ( !defined( 'BSC_INTEGRATION_TEST' ) ) {
			define( 'BSC_INTEGRATION_TEST', true );
		}

		$this->setup = new Setup( [] );
		$this->setup->clear();
		$hookCallbackList = $this->setup->buildHookCallbackListFor(
			Setup::AVAILABLE_HOOKS
		);
		$this->setup->register( $hookCallbackList );
	}

	/**
	 * @see JsonTestCaseScriptRunner::getRequiredJsonTestCaseMinVersion
	 * @return string
	 */
	protected function getRequiredJsonTestCaseMinVersion() {
		return '1';
	}

	/**
	 * @see JsonTestCaseScriptRunner::getTestCaseLocation
	 * @return string
	 */
	protected function getTestCaseLocation() {
		return __DIR__ . '/TestCases';
	}

	/**
	 * Returns a list of files, an empty list is a sign to run all registered
	 * tests.
	 *
	 * @see JsonTestCaseScriptRunner::getListOfAllowedTestCaseFiles
	 */
	protected function getAllowedTestCaseFiles() {
		return array();
	}

	/**
	 * @see JsonTestCaseScriptRunner::runTestCaseFile
	 *
	 * @param JsonTestCaseFileHandler $jsonTestCaseFileHandler
	 */
	protected function runTestCaseFile( JsonTestCaseFileHandler $jsonTestCaseFileHandler ) {

		$this->checkEnvironmentToSkipCurrentTest( $jsonTestCaseFileHandler );

		// Setup
		$this->prepareTest( $jsonTestCaseFileHandler );

		// Run test cases
		$this->doRunParserTests( $jsonTestCaseFileHandler );
	}

	/**
	 * @param JsonTestCaseFileHandler $jsonTestCaseFileHandler
	 */
	private function doRunParserTests( JsonTestCaseFileHandler $jsonTestCaseFileHandler ) {

		foreach ( $jsonTestCaseFileHandler->findTestCasesByType( 'parser' ) as $case ) {

			if ( !isset( $case['subject'] ) ) {
				break;
			}

			// Assert function are defined individually by each TestCaseRunner
			// to ensure a wide range of scenarios can be supported.
			$this->assertParserOutputForCase( $case );
		}
	}

	/**
	 * Prepares the test case: setting of global configuration changes (json section "settings",
	 * creation of defined pages (json section "setup")
	 *
	 * @param JsonTestCaseFileHandler $jsonTestCaseFileHandler
	 */
	private function prepareTest( JsonTestCaseFileHandler $jsonTestCaseFileHandler ) {

		// Defines settings that can be altered during a test run with each test
		// having the possibility to change those values, settings will be reset to
		// the original value (from before the test) after the test has finished.
		$permittedSettings = array(
			'wgLanguageCode',
			'wgContLang',
			'wgLang'
		);

		foreach ( $permittedSettings as $key ) {
			$this->changeGlobalSettingTo(
				$key,
				$jsonTestCaseFileHandler->getSettingsFor( $key )
			);
		}

		$this->createPagesFrom(
			$jsonTestCaseFileHandler->getPageCreationSetupList(),
			NS_MAIN
		);
	}

	/**
	 * Assert the text content if available from the parse process and
	 * accessible using the ParserOutput object.
	 *
	 * ```
	 * "assert-output": {
	 * 	"to-contain": [
	 * 		"Foo"
	 * 	],
	 * 	"not-contain": [
	 * 		"Bar"
	 * 	]
	 * }
	 * ```
	 * @param array $case
	 */
	private function assertParserOutputForCase( array $case ) {

		if ( !isset( $case['assert-output'] ) ) {
			return;
		}

		$subject = DIWikiPage::newFromText(
			$case['subject'],
			isset( $case['namespace'] ) ? constant( $case['namespace'] ) : NS_MAIN
		);

		/** @var \ParserOutput $parserOutput */
		$parserOutput = $this->testEnvironment->getUtilityFactory()->newPageReader()->getEditInfo( $subject->getTitle() )->output;

		try {
			$outputPage = $this->getMockBuilder( 'OutputPage' )
				->disableOriginalConstructor()
				->getMock();
			/** @noinspection PhpParamsInspection */
			$hook = new OutputPageParserOutput( $outputPage, $parserOutput );
			$hook->process();
		} catch ( \Exception $e ) {
			// nothing
		}

		if ( isset( $case['assert-output']['to-contain'] ) ) {
			$this->stringValidator->assertThatStringContains(
				$case['assert-output']['to-contain'],
				$parserOutput->getText(),
				$case['about']
			);
		}

		if ( isset( $case['assert-output']['not-contain'] ) ) {
			$this->stringValidator->assertThatStringNotContains(
				$case['assert-output']['not-contain'],
				$parserOutput->getText(),
				$case['about']
			);
		}
	}
}
