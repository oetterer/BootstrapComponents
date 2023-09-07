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

namespace BootstrapComponents\Hooks;

use BootstrapComponents\ApplicationFactory;
use BootstrapComponents\ParserOutputHelper;
use MWException;
use OutputPage;
use ParserOutput;

/**
 * Class OutputPageParserOutput
 *
 * Called after parse, before the HTML is added to the output.
 *
 * Method delegated to separate class to fix missing (deferred) content in
 * {@see \BootstrapComponents\Tests\Integration\BootstrapComponentsJsonTestCaseScriptRunnerTest::assertParserOutputForCase}
 *
 * @see   https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
 *
 * @since 1.2
 */
class OutputPageParserOutput {

	/**
	 * @var OutputPage $outputPage
	 */
	private $outputPage;

	/**
	 * @var ParserOutput $parserOutput
	 */
	private $parserOutput;

	/**
	 * @var ParserOutputHelper $parserOutputHelper
	 */
	private $parserOutputHelper;

	/**
	 * OutputPageParserOutput constructor.
	 *
	 * @param OutputPage $outputPage
	 * @param ParserOutput $parserOutput
	 * @param ParserOutputHelper|null $parserOutputHelper
	 *
	 * @throws MWException
	 */
	public function __construct(
		OutputPage &$outputPage, ParserOutput $parserOutput, ?ParserOutputHelper &$parserOutputHelper = null
	) {
		$this->outputPage = $outputPage;
		$this->parserOutput = $parserOutput;
		if ( is_null( $parserOutputHelper ) ) {
			$parserOutputHelper = ApplicationFactory::getInstance()->getParserOutputHelper();
		}

		$this->parserOutputHelper = $parserOutputHelper;
	}

	/**
	 * @return bool
	 */
	public function process(): bool
	{
		$deferredText = $this->getParserOutputHelper()->getContentForLaterInjection(
			$this->getParserOutput()
		);
		if ( !empty( $deferredText ) ) {
			$this->getOutputPage()->addHTML( $deferredText );
		}

		if ( $this->getParserOutputHelper()->vectorSkinInUse() ) {
			$this->getOutputPage()->addModules( [ 'ext.bootstrapComponents.vector-fix' ] );
		}

		return true;
	}

	/**
	 * @return \OutputPage
	 */
	protected function getOutputPage(): OutputPage
	{
		return $this->outputPage;
	}

	/**
	 * @return \ParserOutput
	 */
	protected function getParserOutput(): ParserOutput
	{
		return $this->parserOutput;
	}

	/**
	 * @return ParserOutputHelper
	 */
	protected function getParserOutputHelper(): ?ParserOutputHelper
	{
		return $this->parserOutputHelper;
	}
}
