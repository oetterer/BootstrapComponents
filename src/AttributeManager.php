<?php
/**
 * Contains the class for handling component attributes/parameters.
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

/**
 * Class AttributeManager
 *
 * @since 1.0
 */
class AttributeManager {

	/**
	 * For attributes that take any value.
	 *
	 * @var int
	 */
	const ANY_VALUE = 1;

	/**
	 * For attributes that can be set to false by supplying one of certain values.
	 * Usually uses for flag-attributes like "active", "collapsible", etc.
	 *
	 * @see \BootstrapComponents\AttributeManager::$noValues
	 *
	 * @var int
	 */
	const NO_FALSE_VALUE = 0;

	/**
	 * Holds all values indicating a "no". Can be used to ignore "enable"-fields.
	 *
	 * @var array $noValues
	 */
	private $noValues;

	/**
	 * The list of attributes that are considered valid; holds alias relation 'name|alias' => 'real name'
	 *
	 * @var string[] $validAttributeNameMapping
	 */
	private $validAttributeNameMapping;

	#@todo need a method: attributeIsSupplied(). for components must be able to check, if "header" was supplied and don't know about aliases

	/**
	 * AttributeManager constructor.
	 *
	 * Do not instantiate directly, but use {@see ApplicationFactory::getNewAttributeManager}
	 * instead.
	 *
	 * @param string[] $componentAttributes the list of attributes, this manager deems valid.
	 * @param string[] $aliases             the list of aliases and their corresponding attribute
	 *
	 * @see ApplicationFactory::getNewAttributeManager
	 */
	public function __construct( $componentAttributes, $aliases ) {
		$this->noValues = [ false, 0, '0', 'no', 'false', 'off', 'disabled', 'ignored' ];
		$this->noValues[] = strtolower( wfMessage( 'confirmable-no' )->text() );
		$this->validAttributeNameMapping = $this->calculateValidAttributeNames( $componentAttributes, $aliases );
	}

	/**
	 * Returns the list of all defined attributes, including those that are invalid for the current component.
	 *
	 * @return string[]
	 */
	public function getAllKnownAttributes() {
		return array_keys( $this->getAttributeRegister() );
	}

	/**
	 * Checks if given $attribute is registered with the manager. Note that an alias is deemed valid.
	 *
	 * @param string $attribute
	 *
	 * @return bool
	 */
	public function isValid( $attribute ) {
		return isset( $this->validAttributeNameMapping[$attribute] );
	}

	/**
	 * Checks, if the attribute $neededAttribute or its alias is found in $suppliedAttributes.
	 *
	 * @param string   $neededAttribute
	 * @param string[] $suppliedAttributes
	 *
	 * @return bool
	 */
	public function isSuppliedInRequest( $neededAttribute, $suppliedAttributes ) {
		if ( !is_array( $suppliedAttributes ) || empty( $suppliedAttributes ) ) {
			return false;
		}
		if ( in_array( $neededAttribute, $suppliedAttributes ) ) {
			return true;
		}
		foreach ( $suppliedAttributes as $suppliedAttribute ) {
			if ( $this->resolveAttributeName( $suppliedAttribute ) == $neededAttribute ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Extracts the _verified_ value for attribute from passed attributes. When attribute is an alias, it returns the real attribute name
	 * together with the aliases value.
	 *
	 * Note:
	 *  * To make certain, that $attribute is valid, use {@see \BootstrapComponents\AttributeManager::isValid} beforehand.
	 *  * If there is no data for $attribute in $passedValue, or the value does not pass verification, this returns null as $passedValue.
	 *
	 * @param string $attribute   the attribute|alias, data will be extracted for
	 * @param string $passedValue must be already parsed
	 *
	 * @return array ( (string) $attributeName, (null|string) $passedValue );
	 */
	public function validateAttributeAndValue( $attribute, $passedValue ) {
		$attributeRealName = $this->resolveAttributeName( $attribute );
		$verifiedValue = $this->validateValueForAttribute( $attributeRealName, $passedValue );
		return [ $attributeRealName, $verifiedValue ];
	}

	/**
	 * Registers all valid attribute names and all aliases.
	 *
	 * @param string[] $validAttributes
	 * @param string[] $aliases
	 *
	 * @return array $filteredValidAttributeNameMapping
	 * @see AttributeManager::getAttributeRegister
	 *
	 */
	protected function calculateValidAttributeNames( $validAttributes, $aliases ) {
		$filteredValidAttributeNameMapping = [];
		foreach ( $validAttributes as $validAttribute ) {
			$validAttribute = strtolower( trim( $validAttribute ) );
			if ( $this->isInRegister( $validAttribute ) ) {
				$filteredValidAttributeNameMapping[$validAttribute] = $validAttribute;
			}
		}
		foreach ( $aliases as $alias => $attributeName ) {
			$attributeName = strtolower( trim( $attributeName ) );
			if ( !isset( $filteredValidAttributeNameMapping[$attributeName] ) ) {
				throw new \LogicException(
					'Alias \'' . $alias . '\' points to an invalid attribute \''
					. $attributeName . '\'. Cannot initialize AttributeManager!'
				);
			}
			if ( !isset( $filteredValidAttributeNameMapping[$alias] ) ) {
				$filteredValidAttributeNameMapping[$alias] = $attributeName;
			}
		}
		return $filteredValidAttributeNameMapping;
	}

	/**
	 * Returns the allowed values for a given attribute or NULL if invalid attribute. Note that an alias is deemed "valid".
	 *
	 * Note that allowed values can be an array, {@see AttributeManager::NO_FALSE_VALUE},
	 * or {@see AttributeManager::ANY_VALUE}.
	 *
	 * @param string $attribute
	 *
	 * @return null|array|bool
	 */
	protected function getAllowedValuesFor( $attribute ) {
		if ( !$this->isValid( $attribute ) ) {
			return null;
		}
		return $this->getAttributeRegister()[$this->validAttributeNameMapping[$attribute]];
	}

	/**
	 * @param string $attribute
	 *
	 * @return bool
	 */
	protected function isInRegister( $attribute ) {
		return isset( $this->getAttributeRegister()[$attribute] );
	}

	/**
	 * Resolves aliases to their mapped attribute. If $attributeName is unknown, it is returned.
	 *
	 * @param string $attributeName
	 *
	 * @return string
	 */
	protected function resolveAttributeName( $attributeName ) {
		return isset( $this->validAttributeNameMapping[$attributeName] )
			? $this->validAttributeNameMapping[$attributeName]
			: $attributeName;
	}

	/**
	 * For a given attribute, this verifies, if value is allowed. If verification succeeds, the value will be returned, null otherwise.
	 * If an attribute is registered as NO_FALSE_VALUE and value is the empty string, it gets converted to true.
	 *
	 * Note: an ANY_VALUE attribute can still be the empty string.
	 * Note: that every value for an unregistered attribute fails verification automatically.
	 *
	 * @param string $attribute
	 * @param string $value
	 *
	 * @return null|bool|string
	 */
	protected function validateValueForAttribute( $attribute, $value ) {
		$allowedValues = $this->getAllowedValuesFor( $attribute );
		if ( $allowedValues === self::NO_FALSE_VALUE ) {
			return $this->verifyValueForNoValueAttribute( $value );
		} elseif ( $allowedValues === self::ANY_VALUE ) {
			// here, the component deals with empty strings its own way and we return blindly what we got
			return $value;
		} elseif ( is_array( $allowedValues ) && in_array( strtolower( $value ), $allowedValues, true ) ) {
			return $value;
		}
		return null;
	}

	/**
	 * @return array
	 */
	private function getAttributeRegister() {
		return [
			'active'      => self::NO_FALSE_VALUE,
			'background'  => [ 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'white', ],
			'class'       => self::ANY_VALUE,
			'color'       => [ 'default', 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'white', ],
			'collapsible' => self::NO_FALSE_VALUE,
			'disabled'    => self::NO_FALSE_VALUE,
			'dismissible' => self::NO_FALSE_VALUE,
			'footer'      => self::ANY_VALUE,
			'header'      => self::ANY_VALUE,
			'id'          => self::ANY_VALUE,
			'link'        => self::ANY_VALUE,
			'outline'     => self::NO_FALSE_VALUE,
			'pill'        => self::NO_FALSE_VALUE,
			'placement'   => [ 'top', 'bottom', 'left', 'right' ],
			'size'        => [ 'xs', 'sm', 'md', 'lg' ],
			'style'       => self::ANY_VALUE,
			'text'        => self::ANY_VALUE,
			'trigger'     => [ 'default', 'focus', 'hover' ],
		];

	}

	/**
	 * If $value is a no-value, this returns false. if then $value is empty(), this returns true, $value otherwise.
	 *
	 * @param null|string $value
	 *
	 * @return bool|string
	 */
	private function verifyValueForNoValueAttribute( $value ) {
		if ( is_string( $value ) ) {
			$value = trim( strtolower( $value ) );
		}
		if ( in_array( $value, $this->noValues, true ) ) {
			return false;
		}
		return empty( $value ) ? true : $value;
	}
}
