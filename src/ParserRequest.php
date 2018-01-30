<?php
/**
 * Contains the class handling the parameters passed by the parser to the parser function / tag.
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

namespace BootstrapComponents;

use MWException;

/**
 * Class ParserRequest
 *
 * Class to handle the data passed by the parser to a component.
 *
 * @since 1.0
 */
class ParserRequest {
	/**
	 * @var string[] $attributes
	 */
	private $attributes;

	/**
	 * @var string $input
	 */
	private $input;

	/**
	 * @var \PPFrame $frame
	 */
	private $frame;

	/**
	 * @var \Parser $parser
	 */
	private $parser;

	/**
	 * ParserRequest constructor.
	 *
	 * Do not instantiate directly, but use {@see ApplicationFactory::getNewParserRequest}
	 * instead.
	 *
	 * @param array  $argumentsPassedByParser
	 * @param bool   $isParserFunction
	 * @param string $componentName
	 *
	 * @see ApplicationFactory::getNewParserRequest
	 *
	 * @throws MWException
	 */
	public function __construct( $argumentsPassedByParser, $isParserFunction, $componentName = 'unknown' ) {
		list( $this->input, $attributes, $this->parser, $this->frame ) =
			$this->processArguments( $argumentsPassedByParser, $isParserFunction, $componentName );
		$this->attributes = (array)$attributes;
		if ( !$this->parser || !is_a( $this->parser, 'Parser' ) ) {
			throw new MWException( 'Invalid parser object passed to component ' . $componentName . '!' );
		}
	}

	/**
	 * Returns the tag attributes / parser function options supplies by the parser.
	 *
	 * @return string[] associative array `attribute => value`
	 */
	public function getAttributes() {
		return $this->attributes;
	}

	/**
	 * Gets the input supplied by the parser. For tag extensions this is the string between opening and closing tag. For
	 * parser functions this is the first option after the double colon.
	 *
	 * <code>
	 *  <bootstrap_panel>This is the input</bootstrap_panel>
	 *  {{#bootstrap_icon:This is the input}}
	 * </code>
	 *
	 * @return string
	 */
	public function getInput() {
		return $this->input;
	}

	/**
	 * Tag extensions supply a frame.
	 *
	 * @return \PPFrame
	 */
	public function getFrame() {
		return $this->frame;
	}

	/**
	 * This is the parser object passed to the parser function or the tag extension.
	 *
	 * @return \Parser
	 */
	public function getParser() {
		return $this->parser;
	}

	/**
	 * Converts an array of values in form [0] => "name=value" into a real
	 * associative array in form [name] => value. If no = is provided,
	 * true is assumed like this: [name] => true
	 *
	 * Note: shamelessly copied, see link below
	 *
	 * @see https://www.mediawiki.org/w/index.php?title=Manual:Parser_functions&oldid=2572048
	 *
	 * @param array  $options
	 * @param string $componentName
	 *
	 * @throws MWException
	 * @return array $results
	 */
	private function extractParserFunctionOptions( $options, $componentName ) {
		if ( empty( $options ) ) {
			return [];
		}
		$results = [];
		foreach ( $options as $option ) {
			if ( !is_string( $option ) ) {
				throw new MWException( 'Arguments passed to bootstrap component "' . $componentName . '" are invalid!' );
			}
			list( $key, $value ) = $this->getKeyValuePairFrom( $option );
			if ( strlen( $key ) ) {
				$results[$key] = $value;
			}
		}
		return $results;
	}

	/**
	 * @param string $option
	 *
	 * @return string[]
	 */
	private function getKeyValuePairFrom( $option ) {

		$pair = explode( '=', $option, 2 );

		if ( count( $pair ) === 2 ) {
			$name = trim( $pair[0] );
			$value = trim( $pair[1] );
		} elseif ( count( $pair ) === 1 ) {
			$name = trim( $pair[0] );
			$value = true;
		} else {
			$name = '';
			$value = false;
		}
		return [ $name, $value ];
	}

	/**
	 * Parses the arguments passed to parse() method depending on handler type
	 * (parser function or tag extension).
	 *
	 * @param array  $argumentsPassedByParser
	 * @param bool   $isParserFunction
	 * @param string $componentName
	 *
	 * @throws MWException if argument list does not match handler type or unknown handler type detected
	 * @return array array consisting of (string) $input, (array) $options, (Parser) $parser, and optional (PPFrame) $frame
	 */
	private function processArguments( $argumentsPassedByParser, $isParserFunction, $componentName ) {
		$argumentsPassedByParser = (array)$argumentsPassedByParser;
		if ( $isParserFunction ) {
			$parser = array_shift( $argumentsPassedByParser );
			$input = isset( $argumentsPassedByParser[0] ) ? $argumentsPassedByParser[0] : '';
			unset( $argumentsPassedByParser[0] );

			$attributes = $this->extractParserFunctionOptions( $argumentsPassedByParser, $componentName );

			return [ $input, $attributes, $parser, null ];
		} else {
			if ( count( $argumentsPassedByParser ) != 4 ) {
				throw new MWException( 'Argument list passed to bootstrap tag component "' . $componentName . '" is invalid!' );
			}
			return $argumentsPassedByParser;
		}
	}
}