<?php
/**
 * Contains base class for all components.
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

use \MWException;

/**
 * Class AbstractComponent
 *
 * Abstract class for all component classes
 *
 * @since 1.0
 */
abstract class AbstractComponent implements NestableInterface {
	/**
	 * Holds a reference of the component's attribute manger.
	 *
	 * @var AttributeManager $attributeManager
	 */
	private $attributeManager;

	/**
	 * @var ComponentLibrary $componentLibrary
	 */
	private $componentLibrary;

	/**
	 * The (html) id of this component. Not available before the component was opened.
	 *
	 * @var string $id
	 */
	private $id;

	/**
	 * Name of the component
	 *
	 * @var string $name
	 */
	private $name;

	/**
	 * @var NestingController $nestingController
	 */
	private $nestingController;

	/**
	 * @var NestableInterface|false $parentComponent
	 */
	private $parentComponent;

	/**
	 * @var ParserOutputHelper $parserOutputHelper
	 */
	private $parserOutputHelper;

	/**
	 * @var ParserRequest $parserRequest
	 */
	private $parserRequest;

	/**
	 * For every of my registered attributes holds a value. false, if not valid in supplied
	 * parserRequest.
	 *
	 * @var array $sanitizedAttributes
	 */
	private $sanitizedAttributes;

	/**
	 * Does the actual work in the individual components.
	 *
	 * @param string $input
	 *
	 * @return string|array
	 */
	abstract protected function placeMe( $input );

	/**
	 * Component constructor.
	 *
	 * @param ComponentLibrary   $componentLibrary
	 * @param ParserOutputHelper $parserOutputHelper
	 * @param NestingController  $nestingController
	 *
	 * @throws MWException cascading {@see ComponentLibrary::getNameFor} or {@see Component::extractAttribute}
	 */
	public function __construct( $componentLibrary, $parserOutputHelper, $nestingController ) {
		$this->componentLibrary = $componentLibrary;
		$this->parserOutputHelper = $parserOutputHelper;
		$this->nestingController = $nestingController;
		$this->name = $componentLibrary->getNameFor(
			get_class( $this )
		);
		$this->attributeManager = ApplicationFactory::getInstance()->getNewAttributeManager(
			$this->componentLibrary->getAttributesFor( $this->getComponentName() ),
			$this->componentLibrary->getAliasesFor( $this->getComponentName() )
		);
		$this->parentComponent = $this->getNestingController()->getCurrentElement();
	}

	/**
	 * Returns the name of the component.
	 *
	 * @return string
	 */
	public function getComponentName() {
		return $this->name;
	}

	/**
	 * Note that id is only present after {@see AbstractComponent::parseComponent} starts execution.
	 *
	 * @return string
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param ParserRequest $parserRequest ;
	 *
	 * @throws MWException self or cascading from {@see Component::processArguments}
	 *                      or {@see NestingController::close}
	 *
	 * @return string|array
	 */
	public function parseComponent( $parserRequest ) {
		if ( !is_a( $parserRequest, ParserRequest::class ) ) {
			throw new MWException( 'Invalid ParserRequest supplied to component ' . $this->getComponentName() . '!' );
		}
		$this->getNestingController()->open( $this );
		$this->initComponentData( $parserRequest );

		$input = $this->prepareInput( $parserRequest );

		$ret = $this->placeMe( $input );
		$this->getNestingController()->close( $this->getId() );
		return $ret;
	}

	/**
	 * Converts the input array to a string using glue. Removes invalid (false) entries beforehand.
	 *
	 * @param array|false $array
	 * @param string      $glue
	 *
	 * @return false|string returns false on empty array, string otherwise
	 */
	protected function arrayToString( $array, $glue ) {
		if ( empty( $array ) ) {
			return false;
		}
		foreach ( (array) $array as $key => $item ) {
			if ( $item === false || $item === '' ) {
				unset( $array[$key] );
			}
		}
		return count( $array ) ? implode( $glue, $array ) : false;
	}

	/**
	 * @return AttributeManager
	 */
	protected function getAttributeManager() {
		return $this->attributeManager;
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
	 * @return NestableInterface|false
	 */
	protected function getParentComponent() {
		return $this->parentComponent;
	}

	/**
	 * @return ParserOutputHelper
	 */
	protected function getParserOutputHelper() {
		if ( !defined( 'BSC_INTEGRATION_TEST' ) ) {
			#@fixme this is foobar to make modals work in integration tests. find a better solution
			# see also \BootstrapComponents\Tests\Integration\BootstrapComponentsJsonTestCaseScriptRunnerTest::setUp
			return $this->parserOutputHelper;
		}
		return ApplicationFactory::getInstance()->getParserOutputHelper();
	}

	/**
	 * Returns the original parser request supplied to this component.
	 * Note, that none of the attributes nor the input were parsed with
	 * {@see \Parser::recursiveTagParse}.
	 *
	 * @return ParserRequest
	 */
	protected function getParserRequest() {
		return $this->parserRequest;
	}

	/**
	 * If attribute is registered, this returns the verified and parsed value for it. If not, or the
	 * verified value is false, this returns the fallback.
	 *
	 * @param string      $attribute
	 * @param bool|string $fallback
	 *
	 * @return bool|string
	 */
	protected function getValueFor( $attribute, $fallback = false ) {
		if ( !$this->hasValueFor( $attribute ) ) {
			return $fallback;
		}
		return $this->sanitizedAttributes[$attribute];
	}

	/**
	 * Checks, if component has a value supplied for $attribute. Note, that $value can be empty string or evaluate to false.
	 *
	 * @param string $attribute
	 *
	 * @return bool
	 */
	protected function hasValueFor( $attribute ) {
		return isset( $this->sanitizedAttributes[$attribute] );
	}

	/**
	 * Parses input text from parser request. Does also some fixes to let parser detect paragraphs in content.
	 *
	 * @param ParserRequest $parserRequest
	 * @param bool          $fullParse
	 *
	 * @return string
	 * @since 1.1.0
	 *
	 */
	protected function prepareInput( $parserRequest, $fullParse = false ) {
		$parser = $parserRequest->getParser();
		if ( $fullParse ) {
			$input = $parser->recursiveTagParseFully(
				$parserRequest->getInput(),
				$parserRequest->getFrame()
			);
		} else {
			$input = $parser->recursiveTagParse(
				$parserRequest->getInput(),
				$parserRequest->getFrame()
			);
		}
		if ( $input && preg_match( '/\n\n/', $input ) || preg_match( '/<p/', $input ) ) {
			// if there are paragraph marker we prefix input with a new line so the parser recognizes two paragraphs.
			$input = "\n" . $input . "\n";
		}
		return $input;
	}

	/**
	 * Takes your class and style string and appends them with corresponding data from user (if present)
	 * passed in attributes.
	 *
	 * @param string|array $class
	 * @param string|array $style
	 *
	 * @return array[] containing (array)$class and (array)$style
	 */
	protected function processCss( $class, $style ) {
		$class = (array) $class;
		$style = (array) $style;
		if ( $newClass = $this->getValueFor( 'class' ) ) {
			$class[] = $newClass;
		}
		if ( $newStyle = $this->getValueFor( 'style' ) ) {
			$style[] = $newStyle;
		}
		return [ $class, $style ];
	}

	/**
	 * Performs all the mandatory actions on the parser output for the component class.
	 */
	private function augmentParserOutput() {
		$this->getParserOutputHelper()->addTrackingCategory();
		$this->getParserOutputHelper()->loadBootstrapModules();
		$modules = $this->getComponentLibrary()->getModulesFor(
			$this->getComponentName(),
			$this->getParserOutputHelper()->getNameOfActiveSkin()
		);
		$this->getParserOutputHelper()->addModules( $modules );
	}

	/**
	 * Initializes the attributes, id and stores the original parser request.
	 *
	 * @param ParserRequest $parserRequest
	 */
	private function initComponentData( ParserRequest $parserRequest ) {
		$this->parserRequest = $parserRequest;
		$this->sanitizedAttributes = $this->sanitizeAttributes( $parserRequest );
		$this->id = $this->getValueFor( 'id' ) !== false
			? (string) $this->getValueFor( 'id' )
			: $this->getNestingController()->generateUniqueId( $this->getComponentName() );
		$this->augmentParserOutput();
	}

	/**
	 * For every registered attribute, sanitizes (parses and verifies) the corresponding value in supplied attributes.
	 *
	 * @param ParserRequest $parserRequest
	 *
	 * @return array
	 */
	private function sanitizeAttributes( ParserRequest $parserRequest ): array {
		$parsedAttributes = [];

		foreach ( $parserRequest->getAttributes() as $attribute => $unParsedValue ) {
			if ( !$this->getAttributeManager()->isValid( $attribute ) ) {
				continue;
			}
			list( $attribute, $verifiedValue ) = $this->getAttributeManager()->validateAttributeAndValue(
				$attribute,
				$parserRequest->getParser()->recursiveTagParse( $unParsedValue, $parserRequest->getFrame() )
			);
			if ( !is_null( $verifiedValue ) ) {
				$parsedAttributes[$attribute] = $verifiedValue;
			}
		}
		return $parsedAttributes;
	}
}
