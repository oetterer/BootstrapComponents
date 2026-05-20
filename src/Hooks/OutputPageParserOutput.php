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
 * @see   https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
 *
 * @since 1.2
 */
class OutputPageParserOutput {

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
	 *
	 * @deprecated unused since the inline-emission modal fix; will be removed in the next major release.
	 */
	private ParserOutput $parserOutput;

	/**
	 * OutputPageParserOutput constructor.
	 *
	 * @param OutputPage $outputPage
	 * @param ParserOutput $parserOutput @deprecated unused since the inline-emission modal fix; will be removed in the next major release.
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
		if ( $this->getBootstrapComponentsService()->vectorSkinInUse() ) {
			$this->getOutputPage()->addModules( [ 'ext.bootstrapComponents.vector-fix' ] );
		}
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
	 * @deprecated unused since the inline-emission modal fix; will be removed in the next major release.
	 *
	 * @return ParserOutput
	 */
	protected function getParserOutput(): ParserOutput {
		return $this->parserOutput;
	}
}
