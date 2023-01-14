<?php
/**
 * Contains the class controlling the references and creating necessary helper objects.
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

use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MWException;
use ReflectionClass;

/**
 * Class ApplicationFactory
 *
 * Manages access to application classes.
 *
 * @since 1.0
 */
class ApplicationFactory {

	/**
	 * @var ApplicationFactory $instance
	 */
	private static $instance = null;

	/**
	 * Holds the application singletons
	 *
	 * @var array $applicationStore
	 */
	private $applicationStore;

	/**
	 * Library, that tells the ApplicationFactory, which class to use to instantiate which application
	 *
	 * @var array $applicationClassRegister
	 */
	private $applicationClassRegister;

	/**
	 * @var \Psr\Log\LoggerInterface $logger
	 */
	private $logger;

	/**
	 * Returns the singleton instance
	 *
	 * @return ApplicationFactory
	 */
	public static function getInstance() {
		if ( self::$instance !== null ) {
			return self::$instance;
		}

		return self::$instance = new self();
	}

	/**
	 * ApplicationFactory constructor.
	 *
	 * Do not instantiate directly, but use {@see ApplicationFactory::getInstance}
	 * instead.
	 *
	 * @see ApplicationFactory::getInstance
	 */
	public function __construct() {
		$this->applicationStore = [];
		$this->applicationClassRegister = $this->getApplicationClassRegister();
		$this->getLogger()->info( 'ApplicationFactory was build!' );
	}

	/**
	 * @param null|bool|array $componentWhiteList
	 *
	 * @throws MWException  cascading {@see \BootstrapComponents\ApplicationFactory::getApplication}
	 *
	 * @return ComponentLibrary
	 */
	public function getComponentLibrary( $componentWhiteList = null ) {
		return $this->getApplication( 'ComponentLibrary', $componentWhiteList );
	}

	/**
	 * @throws MWException  cascading {@see \BootstrapComponents\ApplicationFactory::getApplication}
	 *
	 * @return NestingController
	 */
	public function getNestingController() {
		return $this->getApplication( 'NestingController' );
	}

	/**
	 * @param string[] $validAttributes
	 * @param string[] $aliases
	 *
	 * @see AttributeManager::__construct
	 *
	 * @return AttributeManager
	 */
	public function getNewAttributeManager( $validAttributes, $aliases ) {
		return new AttributeManager( $validAttributes, $aliases );
	}

	/**
	 * @param string             $id
	 * @param string             $trigger must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 * @param string             $content must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 * @param ParserOutputHelper $parserOutputHelper
	 *
	 * @see ModalBuilder::__construct
	 *
	 * @return ModalBuilder
	 */
	public function getNewModalBuilder( $id, $trigger, $content, $parserOutputHelper ) {
		return new ModalBuilder( $id, $trigger, $content, $parserOutputHelper );
	}

	/**
	 * @param array  $argumentsPassedByParser
	 * @param bool   $isParserFunction
	 * @param string $componentName
	 *
	 * @see ParserRequest::__construct
	 *
	 * @throws \MWException cascading {@see ParserRequest::__construct}
	 *
	 * @return ParserRequest
	 */
	public function getNewParserRequest( $argumentsPassedByParser, $isParserFunction, $componentName = 'unknown' ) {
		return new ParserRequest( $argumentsPassedByParser, $isParserFunction, $componentName );
	}

	/**
	 * @param \Parser $parser
	 *
	 * @see ParserOutputHelper
	 *
	 * @throws MWException  cascading {@see ApplicationFactory::getApplication}
	 *
	 * @return ParserOutputHelper
	 */
	public function getParserOutputHelper( $parser = null ) {
		if ( $parser === null ) {
			$parser = MediaWikiServices::getInstance()->getParser();
		}
		return $this->getApplication( 'ParserOutputHelper', $parser );
	}

	/**
	 * Registers an application with the ApplicationFactory.
	 *
	 * @param string $name
	 * @param string $class
	 *
	 * @throws MWException when class to register does not exist
	 *
	 * @return bool
	 */
	public function registerApplication( $name, $class ) {
		$application = trim( $name );
		$applicationClass = trim( $class );
		if ( $application != '' && class_exists( $applicationClass ) ) {
			$this->applicationClassRegister[$application] = $applicationClass;
			return true;
		} elseif ( $application != '' ) {
			throw new MWException( 'ApplicationFactory was requested to register non existing class "' . $applicationClass . '"!' );
		}
		$this->getLogger()->error( 'ApplicationFactory was requested to register invalid application for class ' . $applicationClass . '!' );
		return false;
	}

	/**
	 * Resets the application $application (or all, if $application is null), so that the next call to
	 * {@see ApplicationFactory::getApplication} will create a new object.
	 *
	 * @param null|string $application
	 *
	 * @return bool
	 */
	public function resetLookup( $application = null ) {
		if ( is_null( $application ) ) {
			$this->applicationStore = [];
			return true;
		} elseif ( isset( $this->applicationStore[$application] ) ) {
			unset( $this->applicationStore[$application] );
			return true;
		}
		return false;
	}

	/**
	 * This returns the application $name. Creates a new instance and stores the singleton, if not already in store.
	 * You can supply any number of additional arguments to this function, they will be passed to the constructor.
	 *
	 * @param string $name
	 *
	 * @throws MWException  when no class is registered for the requested application or the creation of the object fails.
	 *
	 * @return mixed|object
	 */
	protected function getApplication( $name ) {
		if ( isset( $this->applicationStore[$name] ) ) {
			return $this->applicationStore[$name];
		}
		if ( !isset( $this->applicationClassRegister[$name] ) ) {
			throw new MWException( 'ApplicationFactory was requested to return application "' . $name . '". No appropriate class registered!' );
		}
		$args = func_get_args();
		array_shift( $args ); # because, we already used the first argument $name

		try {
			$objectReflection = new ReflectionClass( $this->applicationClassRegister[$name] );
		} catch ( \ReflectionException $e ) {
			throw new MWException( 'Error while trying to build application "' . $name . '" with class ' . $this->applicationClassRegister[$name] );
		}
		$this->getLogger()->info( 'ApplicationFactory successfully build application ' . $name );
		return $this->applicationStore[$name] = $objectReflection->newInstanceArgs( $args );
	}

	/**
	 * @return array
	 */
	protected function getApplicationClassRegister() {
		return [
			'ComponentLibrary'   => 'BootstrapComponents\\ComponentLibrary',
			'NestingController'  => 'BootstrapComponents\\NestingController',
			'ParserOutputHelper' => 'BootstrapComponents\\ParserOutputHelper',
		];
	}

	/**
	 * @return \Psr\Log\LoggerInterface
	 */
	protected function getLogger() {
		if ( !empty( $this->logger ) ) {
			return $this->logger;
		}
		return $this->logger = LoggerFactory::getInstance( 'BootstrapComponents' );
	}
}
