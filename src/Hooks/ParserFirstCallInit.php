<?php
/**
 * Contains the class creating the ParserFirstCallInit hook callback.
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

use \BootstrapComponents\ApplicationFactory;
use \BootstrapComponents\ComponentLibrary;
use \BootstrapComponents\NestingController;
use \BootstrapComponents\ParserOutputHelper;
use \Parser;
use \ReflectionClass;

/**
 * Class ParserFirstCallInit
 *
 * Provides the operations for the ParserFirstCallInit hook call. Called when the parser initializes for the first time.
 *
 * @see https://www.mediawiki.org/wiki/Manual:Hooks/ParserFirstCallInit
 *
 * @since 1.2
 */
class ParserFirstCallInit {

	/**
	 * @var ComponentLibrary $componentLibrary
	 */
	private $componentLibrary;

	/**
	 * @var NestingController $nestingController
	 */
	private $nestingController;

	/**
	 * @var Parser $parser
	 */
	private $parser;

	/**
	 * @var ParserOutputHelper $parserOutputHelper
	 */
	private $parserOutputHelper;

	/**
	 * ParserFirstCallInit constructor.
	 *
	 * @param Parser            $parser
	 * @param ComponentLibrary  $componentLibrary
	 * @param NestingController $nestingController
	 *
	 * @throws \MWException  cascading {@see \BootstrapComponents\ApplicationFactory::getParserOutputHelper}
	 */
	public function __construct( $parser, $componentLibrary, $nestingController ) {
		$this->componentLibrary = $componentLibrary;
		$this->nestingController = $nestingController;
		$this->parser = $parser;
		$this->parserOutputHelper = ApplicationFactory::getInstance()->getParserOutputHelper( $parser );
	}

	/**
	 * @throws \MWException  cascading {@see \Parser::setFunctionHook} and {@see Parser::setHook}
	 *
	 * @return bool
	 */
	public function process() {

		foreach ( $this->getComponentLibrary()->getRegisteredComponents() as $componentName ) {

			$parserHookString = ComponentLibrary::compileParserHookStringFor( $componentName );
			$callback = $this->createParserHookCallbackFor( $componentName );

			if ( $this->getComponentLibrary()->isParserFunction( $componentName ) ) {
				$this->getParser()->setFunctionHook( $parserHookString, $callback );
			} elseif ( $this->getComponentLibrary()->isTagExtension( $componentName ) ) {
				$this->getParser()->setHook( $parserHookString, $callback );
			} else {
				wfDebugLog(
					'BootstrapComponents',
					'Unknown handler type (' . $this->getComponentLibrary()->getHandlerTypeFor( $componentName )
					. ') detected for component ' . $parserHookString
				);
			}
		}
		return true;
	}

	/**
	 * @return ComponentLibrary
	 */
	protected function getComponentLibrary() {
		return $this->componentLibrary;
	}

	/**
	 * @return NestingController
	 */
	protected function getNestingController() {
		return $this->nestingController;
	}

	/**
	 * @return Parser
	 */
	protected function getParser() {
		return $this->parser;
	}

	/**
	 * @return ParserOutputHelper
	 */
	protected function getParserOutputHelper() {
		return $this->parserOutputHelper;
	}

	/**
	 * Creates the callback to be registered with {@see \Parser::setFunctionHook} or {@see \Parser::setHook}.
	 *
	 * @param string $componentName
	 *
	 * @return \Closure
	 */
	private function createParserHookCallbackFor( $componentName ) {

		$componentLibrary = $this->getComponentLibrary();
		$nestingController = $this->getNestingController();
		$parserOutputHelper = $this->getParserOutputHelper();

		return function() use ( $componentName, $componentLibrary, $nestingController, $parserOutputHelper ) {

			$componentClass = $componentLibrary->getClassFor( $componentName );
			$objectReflection = new ReflectionClass( $componentClass );
			$object = $objectReflection->newInstanceArgs( [ $componentLibrary, $parserOutputHelper, $nestingController ] );

			$parserRequest = ApplicationFactory::getInstance()->getNewParserRequest(
				func_get_args(),
				$componentLibrary->isParserFunction( $componentName ),
				$componentName
			);
			/** @var \BootstrapComponents\AbstractComponent $object */
			return $object->parseComponent( $parserRequest );
		};
	}
}
