<?php
/**
 * Contains the component class for rendering a collapsible.
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

namespace BootstrapComponents\Components;

use BootstrapComponents\ApplicationFactory;
use BootstrapComponents\AbstractComponent;
use BootstrapComponents\ParserRequest;
use \Html;
use \MWException;

/**
 * Class Collapse
 *
 * Class for component 'collapse'
 *
 * @see   https://github.com/oetterer/BootstrapComponents/blob/master/docs/components.md#Collapse
 * @since 1.0
 */
class Collapse extends AbstractComponent {
	/**
	 * @inheritdoc
	 *
	 * @param string $input
	 */
	protected function placeMe( $input ) {
		$buttonPrintOut = $this->generateButton(
			clone $this->getParserRequest()
		);

		list ( $class, $style ) = $this->processCss( 'collapse', [] );
		return $buttonPrintOut . Html::rawElement(
				'div',
				[
					'class' => $this->arrayToString( $class, ' ' ),
					'style' => $this->arrayToString( $style, ';' ),
					'id'    => $this->getId(),
				],
				$input
			);
	}


	/**
	 * Spawns the button for our collapse component.
	 *
	 * @param ParserRequest $parserRequest
	 *
	 * @throws MWException cascading {@see \BootstrapComponents\Component::parseComponent}
	 * @return string
	 */
	private function generateButton( ParserRequest $parserRequest ) {
		$button = new Button( $this->getComponentLibrary(), $this->getParserOutputHelper(), $this->getNestingController() );
		$button->injectRawAttributes( [ 'data-toggle' => 'collapse' ] );

		$buttonAttributes = $parserRequest->getAttributes();
		unset( $buttonAttributes['id'] );

		// note that button indeed is a parser function. however, the argument isParserFunction to getNewParserRequest
		// only determines how the first parameter is processed. pretending to be a tag extension is easier to produce.
		$buttonParserRequest = ApplicationFactory::getInstance()->getNewParserRequest(
			[ '#' . $this->getId(), $buttonAttributes, $parserRequest->getParser(), $parserRequest->getFrame() ],
			false,
			'button'
		);

		$buttonPrintOut = $button->parseComponent( $buttonParserRequest );
		if ( is_array( $buttonPrintOut ) ) {
			$buttonPrintOut = $buttonPrintOut[0];
		}
		return $buttonPrintOut;
	}
}
