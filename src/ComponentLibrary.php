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

namespace MediaWiki\Extension\BootstrapComponents;

use MediaWiki\MediaWikiServices;
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
	 * File, that holds the component definition list.
	 *
	 * @var string
	 */
	const DEFINITIONS_FILE = __DIR__ . DIRECTORY_SEPARATOR . 'ComponentsDefinition.json';

	/**
	 * @var string
	 */
	const HANDLER_TYPE_PARSER_FUNCTION = 'function';

	/**
	 * @var string
	 */
	const HANDLER_TYPE_TAG_EXTENSION = 'tag';

	/**
	 * @var string
	 */
	const PARSER_HOOK_PREFIX = 'bootstrap_';

	/**
	 * This array holds all the data for all known components, whether they are registered or not.
	 *
	 * Array has form
	 * <pre>
	 *  "componentIdentifier" => [
	 *      "class" => <className>,
	 *      "name" => <componentName>
	 *      "handlerType" => <handlerType>,
	 *      "attributes" => [ "attr1", "attr2", ... ],
	 *      "aliases" => [ "alias" => "attribute", ... ]
	 *      "modules" => [
	 *          "default" => [ "module1", "module2", ... ],
	 *          "<skin>" => [ "module1", "module2", ... ],
	 *      ]
	 *  ]
	 * </pre>
	 *
	 * @var array $componentDataStore
	 */
	private array $componentDataStore;

	/**
	 * The list of registered/allowed bootstrap components, name or alias
	 *
	 * @var string[] $registeredComponents
	 */
	private array $registeredComponents;

	/**
	 * @param string $componentName
	 *
	 * @return string
	 */
	public static function compileParserHookStringFor( string $componentName ): string {
		return self::PARSER_HOOK_PREFIX . strtolower( $componentName );
	}

	/**
	 * ComponentLibrary constructor.
	 *
	 * Do not instantiate directly, but use
	 * MediaWikiService::getInstance->get('BootstrapComponents.ComponentLibrary') instead.
	 *
	 * @param bool|array $componentWhiteList (see {@see ComponentLibrary::$componentWhiteList})
	 */
	public function __construct( bool|array $componentWhiteList = true ) {

		$this->registeredComponents = $this->processWhitelist( $componentWhiteList );
	}

	/**
	 * Compiles an array for all bootstrap component parser functions to be uses in the BootstrapComponents.magic.php file.
	 *
	 * @return array
	 */
	public function compileMagicWordsArray(): array {
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
	 * Returns the defined/allowed attribute aliases for component/alias $componentIdentifier.
	 *
	 * @param string $componentIdentifier
	 *
	 * @return array
	 * @throws MWException provided component is not known
	 */
	public function getAliasesFor( string $componentIdentifier ): array {
		return $this->accessComponentDataStore( $componentIdentifier, 'aliases' );
	}

	/**
	 * Returns the defined/allowed attributes for component/alias $componentIdentifier.
	 *
	 * @param string $componentIdentifier
	 *
	 * @return array
	 * @throws MWException provided component is not known
	 */
	public function getAttributesFor( string $componentIdentifier ): array {
		return $this->accessComponentDataStore( $componentIdentifier, 'attributes' );
	}

	/**
	 * Returns class name for a registered component/alias.
	 *
	 * @param string $componentIdentifier
	 *
	 * @return string
	 * @throws MWException provided component is not known
	 */
	public function getClassFor( string $componentIdentifier ): string {
		return $this->accessComponentDataStore( $componentIdentifier, 'class' );
	}

	/**
	 * Returns handler type for a registered component/alias. 'UNKNOWN' if unknown component.
	 *
	 * @param string $componentIdentifier
	 *
	 * @return string
	 *
	 * @see ComponentLibrary::HANDLER_TYPE_PARSER_FUNCTION
	 * @see ComponentLibrary::HANDLER_TYPE_TAG_EXTENSION
	 *
	 */
	public function getHandlerTypeFor( string $componentIdentifier ): string {
		try {
			return $this->accessComponentDataStore( $componentIdentifier, 'handlerType' );
		} catch ( MWException $e ) {
			return 'UNKNOWN';
		}
	}

	/**
	 * Returns an array of all the known components' names, _excluding_ aliases,
	 *
	 * @return array
	 */
	public function getKnownComponents(): array {
		return array_keys( $this->getComponentDataStore() );
	}

	/**
	 * Returns all the needed modules for a component/alias. False, if there are none.
	 * If skin is set, returns all modules especially registered for that skin as well
	 *
	 * @param string $componentIdentifier
	 * @param string|null $skin
	 *
	 * @return array
	 */
	public function getModulesFor( string $componentIdentifier, ?string $skin = null ): array {
		if ( !$this->isKnown( $componentIdentifier ) ) {
			// this prevents us from running into a MWException in the next call.
			return [];
		}
		$allModules = $this->accessComponentDataStore( $componentIdentifier, 'modules' );

		$modules = isset( $allModules['default'] )
			? (array) $allModules['default']
			: [];
		if ( $skin === null || !isset( $allModules[$skin] ) ) {
			return $modules;
		}
		return array_merge(
			$modules,
			(array) $allModules[$skin]
		);
	}

	/**
	 * Returns the component name for a given class.
	 *
	 * @param string $componentClass
	 *
	 * @throws MWException if supplied class is not registered
	 *
	 * @return string
	 */
	public function getNameFor( string $componentClass ): string {
		$component = null;

		foreach ( $this->getComponentDataStore() as $componentIdentifier => $componentData ) {
			if ( isset( $componentData['class'] ) && ( $componentData['class'] == $componentClass ) ) {
				$component = $componentIdentifier;
				break;
			}
		}
		if ( is_null( $component ) ) {
			throw new MWException( 'Trying to get a component name for unregistered class "' . (string) $componentClass . '"!' );
		}
		return $this->accessComponentDataStore( $component, 'name' );
	}

	/**
	 * Returns an array of all the registered component's names. Including aliases.
	 *
	 * @return string[]
	 */
	public function getRegisteredComponents(): array {
		return $this->registeredComponents;
	}

	/**
	 * @param string $componentIdentifier
	 *
	 * @return bool
	 */
	public function isKnown( string $componentIdentifier ): bool {
		return in_array( $componentIdentifier, $this->getKnownComponents() );
	}

	/**
	 * True, if referenced component is registered as parser function.
	 *
	 * @param string $componentIdentifier
	 *
	 * @return bool
	 */
	public function isParserFunction( string $componentIdentifier ): string {
		return $this->getHandlerTypeFor( $componentIdentifier ) == self::HANDLER_TYPE_PARSER_FUNCTION;
	}

	/**
	 * Checks, if component $componentIdentifier is registered
	 *
	 * @param string $componentIdentifier
	 *
	 * @return bool
	 */
	public function isRegistered( string $componentIdentifier ): bool {
		return in_array( $componentIdentifier, $this->registeredComponents, true );
	}

	/**
	 * True, if referenced component is registered as tag extension.
	 *
	 * @param string $componentName
	 *
	 * @return bool
	 */
	public function isTagExtension( string $componentName ): bool {
		return $this->getHandlerTypeFor( $componentName ) == self::HANDLER_TYPE_TAG_EXTENSION;
	}

	/**
	 * @param string $componentIdentifier
	 * @param string $field
	 *
	 * @throws MWException on non-existing $componentIdentifier or $field
	 *
	 * @return mixed
	 */
	protected function accessComponentDataStore( string $componentIdentifier, string $field ): mixed {
		if ( !isset( $this->getComponentDataStore()[$componentIdentifier][$field] ) ) {
			throw new MWException(
				'Trying to access undefined field \'' . $field . '\' of component \'' . $componentIdentifier . '\'. Aborting'
			);
		}
		return $this->getComponentDataStore()[$componentIdentifier][$field];
	}

	protected function hasFieldInDataStore( string $componentIdentifier, string $field ): bool {
		return $this->isRegistered( $componentIdentifier )
			&& isset( $this->getComponentDataStore()[$componentIdentifier][$field] );
	}

	/**
	 * This adds the default attributes to the attribute list.
	 *
	 * @param array $componentAttributes
	 *
	 * @return array
	 */
	private function normalizeAttributes( array $componentAttributes ): array {
		return array_unique(
			array_merge(
				$componentAttributes,
				self::DEFAULT_ATTRIBUTES
			)
		);
	}

	/**
	 * Raw library data used in registration process.
	 *
	 * @return array
	 */
	private function getComponentDataStore(): array {
		if ( !empty( $this->componentDataStore ) ) {
			return $this->componentDataStore;
		}
		$rawData = json_decode( file_get_contents( self::DEFINITIONS_FILE ), JSON_OBJECT_AS_ARRAY );

		$componentAliases = [];
		$componentDataStore = [];
		foreach ( $rawData as $componentName => $componentData ) {

			if ( !is_array( $componentData ) ) {
				$componentAliases[$componentName] = trim( (string) $componentData );
				continue;
			}

			$componentData['name'] = $componentName;
			$componentData['attributes'] = $this->normalizeAttributes( ($componentData['attributes'] ?? []) );
			$componentData['aliases'] = $componentData['aliases'] ?? [];
			$componentData['modules'] = $componentData['modules'] ?? [];
			$componentDataStore[$componentName] = $componentData;
		}

		foreach ( $componentAliases as $alias => $componentName ) {
			if ( isset( $componentDataStore[$componentName] ) ) {
				$componentDataStore[$alias] = $componentDataStore[$componentName];
			}
		}

		return $this->componentDataStore = $componentDataStore;
	}

	/**
	 * If whitelist is a bool, this returns either an empty array or an array, containing all
	 * identifiers from the componentDataStore.
	 *
	 * If the whileList is a non-empty array, this trims and lowercases its values.
	 *
	 * @param bool|array $componentWhiteList
	 *
	 * @return array
	 */
	private function processWhitelist( null|bool|array $componentWhiteList ): array {
		if ( !is_array( $componentWhiteList ) ) {
			if ( !$componentWhiteList ) {
				return [];
			}
			$componentWhiteList = $this->getKnownComponents();
			sort( $componentWhiteList );
			return $componentWhiteList;
		}
		$newWhiteList = [];
		foreach ( $componentWhiteList as $element ) {
			$newWhiteList[] = strtolower( trim( $element ) );
		}
		$newWhiteList = array_intersect( $newWhiteList, $this->getKnownComponents() );
		sort( $newWhiteList );
		return $newWhiteList;
	}
}
