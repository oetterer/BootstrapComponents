<?php
/**
 * Contains class holding and distributing information about all available components.
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

use \MediaWiki\MediaWikiServices;
use \MWException;

/**
 * Class ComponentLibrary
 *
 * Holds information about all registered components
 *
 * @since 1.0
 */
class ComponentLibrary {
	/**
	 * @var array
	 */
	const DEFAULT_ATTRIBUTES = [ 'class', 'id', 'style' ];

	/**
	 * @var string
	 */
	const HANDLER_TYPE_PARSER_FUNCTION = 'ParserFunction';

	/**
	 * @var string
	 */
	const HANDLER_TYPE_TAG_EXTENSION = 'TagExtension';

	/**
	 * @var string
	 */
	const PARSER_HOOK_PREFIX = 'bootstrap_';

	/**
	 * This array holds all the data for all known components, whether they are registered or not.
	 *
	 * Array has form
	 * <pre>
	 *  "componentName" => [
	 *      "class" => <className>,
	 *      "handlerType" => <handlerType>,
	 *      "attributes" => [ "attr1", "attr2", ... ],
	 *      "modules" => [
	 *          "default" => [ "module1", "module2", ... ],
	 *          "<skin>" => [ "module1", "module2", ... ],
	 *      ]
	 *  ]
	 * </pre>
	 *
	 * @var array $componentDataStore
	 */
	private $componentDataStore;

	/**
	 * Array that maps a class name to the corresponding component name
	 *
	 * @var array $componentNamesByClass
	 */
	private $componentNamesByClass;

	/**
	 * The list of available bootstrap components
	 *
	 * @var string[] $registeredComponents
	 */
	private $registeredComponents;

	/**
	 * @param string $componentName
	 *
	 * @return string
	 */
	public static function compileParserHookStringFor( $componentName ) {
		return self::PARSER_HOOK_PREFIX . strtolower( $componentName );
	}

	/**
	 * ComponentLibrary constructor.
	 *
	 * Do not instantiate directly, but use {@see ApplicationFactory::getComponentLibrary} instead.
	 *
	 * @param bool|array $componentWhiteList (see {@see \BootstrapComponents\ComponentLibrary::$componentWhiteList})
	 *
	 * @see ApplicationFactory::getComponentLibrary
	 *
	 * @throws \ConfigException cascading {@see \ConfigFactory::makeConfig} and
	 */
	public function __construct( $componentWhiteList = null ) {

		if ( is_null( $componentWhiteList ) ) {
			$myConfig = MediaWikiServices::getInstance()->getConfigFactory()->makeConfig( 'BootstrapComponents' );

			$componentWhiteList = $myConfig->has( 'BootstrapComponentsWhitelist' )
				? $myConfig->get( 'BootstrapComponentsWhitelist' )
				: true;
		}
		$componentWhiteList = $this->mangle( $componentWhiteList );
		list ( $this->registeredComponents, $this->componentNamesByClass, $this->componentDataStore )
			= $this->registerComponents( $componentWhiteList );
	}

	/**
	 * Compiles an array for all bootstrap component parser functions to be uses in the BootstrapComponents.magic.php file.
	 *
	 * @return array
	 */
	public function compileMagicWordsArray() {
		$magicWords = [];
		foreach ( $this->getRegisteredComponents() as $componentName ) {
			if ( $this->isParserFunction( $componentName ) ) {
				$magicWords[self::compileParserHookStringFor( $componentName )]
					= [ 0, self::compileParserHookStringFor( $componentName ) ];
			}
		}
		return $magicWords;
	}

	/**
	 * Checks, if component $component is registered with the tag manager
	 *
	 * @param string $component
	 *
	 * @return bool
	 */
	public function componentIsRegistered( $component ) {
		return in_array( $component, $this->registeredComponents );
	}

	/**
	 * @param string $component
	 *
	 * @return array
	 * @throws MWException provided component is not known
	 */
	public function getAttributesFor( $component ) {
		if ( !isset( $this->componentDataStore[$component] ) ) {
			throw new MWException( 'Trying to get attribute list for unknown component "' . (string) $component . '"!' );
		}
		return $this->componentDataStore[$component]['attributes'];
	}

	/**
	 * Returns class name for a registered component
	 *
	 * @param string $componentName
	 *
	 * @throws MWException provided component is not registered
	 * @return string
	 */
	public function getClassFor( $componentName ) {
		if ( !$this->componentIsRegistered( $componentName ) ) {
			throw new MWException( 'Trying to get a class for an unregistered component "' . (string) $componentName . '"!' );
		}
		return $this->componentDataStore[$componentName]['class'];
	}

	/**
	 * Returns handler type for a registered component. 'UNKNOWN' for unknown components.
	 *
	 * @see \BootstrapComponents\ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION, \BootstrapComponents\ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION
	 *
	 * @param string $component
	 *
	 * @return string
	 */
	public function getHandlerTypeFor( $component ) {
		if ( !isset( $this->componentDataStore[$component] ) ) {
			return 'UNKNOWN';
		}
		return $this->componentDataStore[$component]['handlerType'];
	}

	/**
	 * Returns an array of all the known components' names.
	 *
	 * @return array
	 */
	public function getKnownComponents() {
		return array_keys( $this->componentDataStore );
	}

	/**
	 * Returns all the needed modules for a registered component. False, if there are none.
	 * If skin is set, returns all modules especially registered for that skin as well
	 *
	 * @param string $componentName
	 * @param string $skin
	 *
	 * @return array
	 */
	public function getModulesFor( $componentName, $skin = null ) {
		$modules = isset( $this->componentDataStore[$componentName]['modules']['default'] )
			? (array) $this->componentDataStore[$componentName]['modules']['default']
			: [];
		if ( $skin === null || !isset( $this->componentDataStore[$componentName]['modules'][$skin] ) ) {
			return $modules;
		}
		return (array) array_merge(
			$modules,
			(array) $this->componentDataStore[$componentName]['modules'][$skin]
		);
	}

	/**
	 * Returns the component name for a given class.
	 *
	 * @param string $componentClass
	 *
	 * @throws MWException if supplied class is not registered
	 * @return string
	 */
	public function getNameFor( $componentClass ) {
		if ( !isset( $this->componentNamesByClass[$componentClass] ) ) {
			throw new MWException( 'Trying to get a component name for unregistered class "' . (string) $componentClass . '"!' );
		}
		return $this->componentNamesByClass[$componentClass];
	}

	/**
	 * Returns an array of all the registered component's names.
	 *
	 * @return string[]
	 */
	public function getRegisteredComponents() {
		return $this->registeredComponents;
	}

	/**
	 * True, if referenced component is registered as parser function.
	 *
	 * @param string $componentName
	 *
	 * @return bool
	 */
	public function isParserFunction( $componentName ) {
		return $this->getHandlerTypeFor( $componentName ) == self::HANDLER_TYPE_PARSER_FUNCTION;
	}

	/**
	 * True, if referenced component is registered as tag extension.
	 *
	 * @param string $componentName
	 *
	 * @return bool
	 */
	public function isTagExtension( $componentName ) {
		return $this->getHandlerTypeFor( $componentName ) == self::HANDLER_TYPE_TAG_EXTENSION;
	}

	/**
	 * Sees to it, that the whitelist (if it is an array) contains only lowercase strings.
	 *
	 * @param bool|array $componentWhiteList
	 *
	 * @return bool|array
	 */
	private function mangle( $componentWhiteList ) {
		if ( !is_array( $componentWhiteList ) ) {
			return $componentWhiteList;
		}
		$newWhiteList = [];
		foreach ( $componentWhiteList as $element ) {
			$newWhiteList[] = strtolower( $element );
		}
		return $newWhiteList;
	}

	/**
	 * The attribute array in the register can contain an `default => true` entry. This adds the
	 * appropriate default attributes.
	 *
	 * @param array $componentAttributes
	 *
	 * @return array
	 */
	private function normalizeAttributes( $componentAttributes ) {
		$componentAttributes = (array) $componentAttributes;
		if ( $componentAttributes['default'] ) {
			$componentAttributes = array_unique(
				array_merge(
					$componentAttributes,
					self::DEFAULT_ATTRIBUTES
				)
			);
		}
		unset( $componentAttributes['default'] );
		return $componentAttributes;
	}

	/**
	 * Generates the array for registered components containing all whitelisted components and the two supporting data arrays.
	 *
	 * @param bool|array $componentWhiteList
	 *
	 * @return array[] $registeredComponents, $componentNamesByClass, $componentDataStore
	 */
	private function registerComponents( $componentWhiteList ) {
		$componentDataStore = [];
		$componentNamesByClass = [];
		$registeredComponents = [];
		foreach ( $this->rawComponentsDefinition() as $componentName => $componentData ) {

			$componentData['attributes'] = $this->normalizeAttributes( $componentData['attributes'] );
			$componentDataStore[$componentName] = $componentData;

			if ( !$componentWhiteList || (is_array( $componentWhiteList ) && !in_array( $componentName, $componentWhiteList )) ) {
				// if $componentWhiteList is false, or and array and does not contain the componentName, we will not register it
				continue;
			}

			$registeredComponents[] = $componentName;
			$componentNamesByClass[$componentData['class']] = $componentName;
		}

		return [ $registeredComponents, $componentNamesByClass, $componentDataStore ];
	}

	/**
	 * Raw library data used in registration process.
	 *
	 * @return array
	 */
	private function rawComponentsDefinition() {
		return [
			'accordion' => [
				'class'       => 'BootstrapComponents\\Components\\Accordion',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
				],
			],
			'alert'     => [
				'class'       => 'BootstrapComponents\\Components\\Alert',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
					'color',
					'dismissible',
				],
				'modules'     => [
					'default' => 'ext.bootstrapComponents.alert.fix',
				],
			],
			'badge'     => [
				'class'       => 'BootstrapComponents\\Components\\Badge',
				'handlerType' => self::HANDLER_TYPE_PARSER_FUNCTION,
				'attributes'  => [
					'default' => true,
					'color',
					'pill'
				],
			],
			'button'    => [
				'class'       => 'BootstrapComponents\\Components\\Button',
				'handlerType' => self::HANDLER_TYPE_PARSER_FUNCTION,
				'attributes'  => [
					'default' => true,
					'active',
					'color',
					'disabled',
					'size',
					'text',
				],
				'modules'     => [
					'default' => 'ext.bootstrapComponents.button.fix',
				],
			],
			'carousel'  => [
				'class'       => 'BootstrapComponents\\Components\\Carousel',
				'handlerType' => self::HANDLER_TYPE_PARSER_FUNCTION,
				'attributes'  => [
					'default' => true,
				],
				'modules'     => [
					'default' => 'ext.bootstrapComponents.carousel.fix',
				],
			],
			'collapse'  => [
				'class'       => 'BootstrapComponents\\Components\\Collapse',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
					'active',
					'color',
					'disabled',
					'size',
					'text',
				],
				'modules'     => [
					'default' => 'ext.bootstrapComponents.button.fix',
				],
			],
			'icon'      => [
				'class'       => 'BootstrapComponents\\Components\\Icon',
				'handlerType' => self::HANDLER_TYPE_PARSER_FUNCTION,
				'attributes'  => [
					'default' => false,
				],
			],
			'jumbotron' => [
				'class'       => 'BootstrapComponents\\Components\\Jumbotron',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
				],
			],
			'label'     => [
				'class'       => 'BootstrapComponents\\Components\\Label',
				'handlerType' => self::HANDLER_TYPE_PARSER_FUNCTION,
				'attributes'  => [
					'default' => true,
					'color',
				],
			],
			'modal'     => [
				'class'       => 'BootstrapComponents\\Components\\Modal',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
					'color',
					'footer',
					'heading',
					'size',
					'text',
				],
				'modules'     => [
					'default' => [
						'ext.bootstrapComponents.button.fix',
						'ext.bootstrapComponents.modal.fix',
					],
					'vector'  => [
						'ext.bootstrapComponents.modal.vector-fix',
					],
				],
			],
			'panel'     => [
				'class'       => 'BootstrapComponents\\Components\\Panel',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
					'active',
					'collapsible',
					'color',
					'footer',
					'heading',
				],
			],
			'popover'   => [
				'class'       => 'BootstrapComponents\\Components\\Popover',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
					'color',
					'heading',
					'placement',
					'size',
					'text',
					'trigger',
				],
				'modules'     => [
					'default' => [
						'ext.bootstrapComponents.button.fix',
						'ext.bootstrapComponents.popover',
					],
					'vector'  => [
						'ext.bootstrapComponents.popover.vector-fix',
					],
				],
			],
			'tooltip'   => [
				'class'       => 'BootstrapComponents\\Components\\Tooltip',
				'handlerType' => self::HANDLER_TYPE_PARSER_FUNCTION,
				'attributes'  => [
					'default' => true,
					'placement',
					'text',
				],
				'modules'     => [
					'default' => 'ext.bootstrapComponents.tooltip',
				],
			],
			'well'      => [
				'class'       => 'BootstrapComponents\\Components\\Well',
				'handlerType' => self::HANDLER_TYPE_TAG_EXTENSION,
				'attributes'  => [
					'default' => true,
					'size',
				],
			],
		];
	}
}
