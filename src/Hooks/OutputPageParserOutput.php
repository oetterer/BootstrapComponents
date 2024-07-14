<?php
/**
 * Contains the class creating the OutputPageParserOutput hook callback.
 *
 * @copyright (C) 2018, Tobias Oetterer, Paderborn University
 * @license       https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License, version 3 (or later)
 *
 * This file is part of the MediaWiki extension BootstrapComponents.
 * The BootstrapComponents extension is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * The BootstrapComponents extension is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @file
 * @ingroup       BootstrapComponents
 * @author        Tobias Oetterer
 */

namespace MediaWiki\Extension\BootstrapComponents\Hooks;

use MediaWiki\Extension\BootstrapComponents\BootstrapComponents;
use MediaWiki\Extension\BootstrapComponents\BootstrapComponentsService;
/*
 * TODO switch to these, wehen we drop support for mw < 1.40
use MediaWiki\Output\OutputPage;
use MediaWiki\Parser\ParserOutput;
 */
use \OutputPage;
use \ParserOutput;

/**
 * Class OutputPageParserOutput
 *
 * Called after parse, before the HTML is added to the output.
 *
 * Method delegated to separate class to fix missing (deferred) content in
 * {@see \MediaWiki\Extension\BootstrapComponents\Tests\Integration\BootstrapComponentsJSONScriptTestCaseRunnerTest::assertParserOutputForCase}
 *
 * @see   https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
 *
 * @since 1.2
 */
class OutputPageParserOutput {

	/**
	 * @var string
	 */
	const INJECTION_PREFIX = '<!-- injected by Extension:BootstrapComponents -->';

	/**
	 * @var string
	 */
	const INJECTION_SUFFIX = '<!-- /injected by Extension:BootstrapComponents -->';

	/**
	 * @var BootstrapComponentsService
	 */
	private BootstrapComponentsService $bootstrapComponentService;

	/**
	 * @var OutputPage $outputPage
	 */
	private OutputPage $outputPage;

	/**
	 * @var ParserOutput $parserOutput
	 */
	private ParserOutput $parserOutput;

	/**
	 * OutputPageParserOutput constructor.
	 *
	 * @param OutputPage $outputPage
	 * @param ParserOutput $parserOutput
	 * @param BootstrapComponentsService $service
	 */
	public function __construct(
		OutputPage &$outputPage, ParserOutput $parserOutput, BootstrapComponentsService $service
	) {
		$this->outputPage = $outputPage;
		$this->parserOutput = $parserOutput;
		$this->bootstrapComponentService = $service;
	}

	/**
	 * @return void
	 */
	public function process(): void	{
		$deferredText = $this->getContentForLaterInjection( $this->getParserOutput() );
		if ( !empty( $deferredText ) ) {
			$this->getOutputPage()->addHTML( $deferredText );
		}

		if ( $this->getBootstrapComponentsService()->vectorSkinInUse() ) {
			$this->getOutputPage()->addModules( [ 'ext.bootstrapComponents.vector-fix' ] );
		}
	}

	/**
	 * Returns the raw html that is to be inserted at the end of the page.
	 *
	 * @param ParserOutput $parserOutput
	 *
	 * @return string
	 */
	protected function getContentForLaterInjection( ParserOutput $parserOutput ): string {
		$deferredContent = $parserOutput
			->getExtensionData(BootstrapComponents::EXTENSION_DATA_DEFERRED_CONTENT_KEY );

		if ( empty( $deferredContent ) || !is_array( $deferredContent ) ) {
			return '';
		}

		// clearing extension data for unit and integration tests to work
		$parserOutput->setExtensionData( BootstrapComponents::EXTENSION_DATA_DEFERRED_CONTENT_KEY, null );
		return self::INJECTION_PREFIX . implode( array_values( $deferredContent ) ) . self::INJECTION_SUFFIX;
	}

	protected function getBootstrapComponentsService(): BootstrapComponentsService {
		return $this->bootstrapComponentService;
	}

	/**
	 * @return OutputPage
	 */
	protected function getOutputPage(): OutputPage {
		return $this->outputPage;
	}

	/**
	 * @return ParserOutput
	 */
	protected function getParserOutput(): ParserOutput {
		return $this->parserOutput;
	}
}
