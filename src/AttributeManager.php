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
 * Class TagManager
 *
 * Manages the execution of the <bootstrap> tag
 *
 * @since 1.0
 */
class AttributeManager {

	/**
	 * This introduces aliases for attributes.
	 *
	 * For instance, if someone adds "header" to its component, it is treated like "heading" if this is not present itself.
	 */
	const ALIASES = [
		'heading' => 'header',
		'footer'  => 'footing'
	];

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
	 * Holds the register for allowed attributes per component
	 *
	 * @var array $allowedValuesForAttribute
	 */
	private $allowedValuesForAttribute;

	/**
	 * Holds all values indicating a "no". Can be used to ignore "enable"-fields.
	 *
	 * @var array $noValues
	 */
	private $noValues;

	/**
	 * The list of attributes that are considered valid
	 *
	 * @var string[] $validAttributes
	 */
	private $validAttributes;

	/**
	 * AttributeManager constructor.
	 *
	 * Do not instantiate directly, but use {@see ApplicationFactory::getAttributeManager}
	 * instead.
	 *
	 * @param string[] $validAttributes the list of attributes, this manager deems valid.
	 *
	 * @see ApplicationFactory::getAttributeManager
	 */
	public function __construct( $validAttributes ) {
		$this->noValues = [ false, 0, '0', 'no', 'false', 'off', 'disabled', 'ignored' ];
		$this->noValues[] = strtolower( wfMessage( 'confirmable-no' )->text() );
		list ( $this->validAttributes, $this->allowedValuesForAttribute ) = $this->registerValidAttributes( $validAttributes );
	}

	/**
	 * Returns the list of all available attributes
	 *
	 * @return string[]
	 */
	public function getAllKnownAttributes() {
		return array_keys( $this->getInitialAttributeRegister() );
	}

	/**
	 * Returns the allowed values for a given attribute or NULL if invalid attribute.
	 *
	 * Note that allowed values can be an array, {@see AttributeManager::NO_FALSE_VALUE},
	 * or {@see AttributeManager::ANY_VALUE}.
	 *
	 * @param string $attribute
	 *
	 * @return null|array|bool
	 */
	public function getAllowedValuesFor( $attribute ) {
		if ( !$this->isRegistered( $attribute ) ) {
			return null;
		}
		return $this->allowedValuesForAttribute[$attribute];
	}

	/**
	 * @return string[]
	 */
	public function getValidAttributes() {
		return $this->validAttributes;
	}

	/**
	 * Checks if given $attribute is registered with the manager.
	 *
	 * @param string $attribute
	 *
	 * @return bool
	 */
	public function isRegistered( $attribute ) {
		return isset( $this->allowedValuesForAttribute[$attribute] );
	}

	/**
	 * Registers $attribute with and its allowed values.
	 *
	 * Notes on attribute registering:
	 * * {@see AttributeManager::ANY_VALUE}: every non empty string is allowed
	 * * {@see AttributeManager::NO_FALSE_VALUE}: as along as the attribute is present and NOT set to a value contained in {@see AttributeManager::$noValues},
	 *      the attribute is considered valid. Note that flag attributes will be set to the empty string by the parser, e.g. having <tag active></tag> will have
	 *      active set to "". {@see AttributeManager::verifyValueForAttribute} returns those as true.
	 * * array: attribute must be present and contain a value in the array to be valid
	 *
	 * Note also, that values will be converted to lower case before checking, you therefore should only put lower case values in your
	 * allowed-values array.
	 *
	 * @param string     $attribute
	 * @param array|int  $allowedValues
	 *
	 * @return bool
	 */
	public function register( $attribute, $allowedValues ) {
		if ( !is_string( $attribute ) || !strlen( trim( $attribute ) ) ) {
			return false;
		}
		if ( !is_int( $allowedValues ) && ( !is_array( $allowedValues ) || !count( $allowedValues ) ) ) {
			return false;
		}
		$this->allowedValuesForAttribute[trim( $attribute )] = $allowedValues;
		return true;
	}

	/**
	 * Takes the attributes/options supplied by parser, removes the ones not registered for this component and
	 * verifies the rest. Note that the result array has an entry for every valid attribute, false if not supplied via parser.
	 *
	 * Valid here means: the attribute is registered with the manager and with the component
	 * Verified: The attributes value has been checked and deemed ok.
	 *
	 * Note that attributes not registered with the manager return with a false value.
	 *
	 * @param string[] $attributes
	 *
	 * @see AttributeManager::verifyValueForAttribute
	 *
	 * @return array
	 */
	public function verifyAttributes( $attributes ) {
		$verifiedAttributes = [];
		foreach ( $this->getValidAttributes() as $validAttribute ) {
			$value = $this->getValueForAttribute( $validAttribute, $attributes );
			if ( is_null( $value ) ) {
				$verifiedAttributes[$validAttribute] = false;
			} else {
				$verifiedAttributes[$validAttribute] = $this->verifyValueForAttribute( $validAttribute, $value );
			}
		}
		return $verifiedAttributes;
	}

	/**
	 * For each supplied valid attribute this registers the attribute together with its valid values.
	 *
	 * Note: Registers only known attributes.
	 *
	 * @param string[] $validAttributes
	 *
	 * @see AttributeManager::getInitialAttributeRegister
	 *
	 * @return array ($filteredValidAttributes, $attributesRegister)
	 */
	protected function registerValidAttributes( $validAttributes ) {
		$allAttributes = $this->getInitialAttributeRegister();
		$filteredValidAttributes = [];
		$attributesRegister = [];
		foreach ( $validAttributes as $validAttribute ) {
			$validAttribute = strtolower( trim( $validAttribute ) );
			if ( isset( $allAttributes[$validAttribute] ) ) {
				$filteredValidAttributes[] = $validAttribute;
				$attributesRegister[$validAttribute] = $allAttributes[$validAttribute];
			}
		}
		return [ $filteredValidAttributes, $attributesRegister ];
	}

	/**
	 * For a given attribute, this verifies, if value is allowed. If verification succeeds, the value will be returned, false otherwise.
	 * If an attribute is registered as NO_FALSE_VALUE and value is the empty string, it gets converted to true.
	 *
	 * Note: an ANY_VALUE attribute can still be the empty string.
	 * Note: that every value for an unregistered attribute fails verification automatically.
	 *
	 * @param string $attribute
	 * @param string $value
	 *
	 * @return bool|string
	 */
	protected function verifyValueForAttribute( $attribute, $value ) {
		$allowedValues = $this->getAllowedValuesFor( $attribute );
		if ( $allowedValues === self::NO_FALSE_VALUE ) {
			return $this->verifyValueForNoValueAttribute( $value );
		} elseif ( $allowedValues === self::ANY_VALUE ) {
			// here, the component deals with empty strings its own way and we return blindly what we got
			return $value;
		} elseif ( is_array( $allowedValues ) && in_array( strtolower( $value ), $allowedValues, true ) ) {
			return $value;
		}
		return false;
	}

	/**
	 * @return array
	 */
	private function getInitialAttributeRegister() {
		return [
			'active'      => self::NO_FALSE_VALUE,
			'class'       => self::ANY_VALUE,
			'color'       => [ 'default', 'primary', 'success', 'info', 'warning', 'danger' ],
			'collapsible' => self::NO_FALSE_VALUE,
			'disabled'    => self::NO_FALSE_VALUE,
			'dismissible' => self::NO_FALSE_VALUE,
			'footer'      => self::ANY_VALUE,
			'heading'     => self::ANY_VALUE,
			'id'          => self::ANY_VALUE,
			'link'        => self::ANY_VALUE,
			'placement'   => [ 'top', 'bottom', 'left', 'right' ],
			'size'        => [ 'xs', 'sm', 'md', 'lg' ],
			'style'       => self::ANY_VALUE,
			'text'        => self::ANY_VALUE,
			'trigger'     => [ 'default', 'focus', 'hover' ],
		];

	}

	/**
	 * Extracts the value for attribute from passed attributes. If attribute itself
	 * is not set, it also looks for aliases.
	 *
	 * @param $attribute
	 * @param $passedAttributes
	 *
	 * @see AttributeManager::ALIASES
	 *
	 * @return null|string
	 */
	private function getValueForAttribute( $attribute, $passedAttributes ) {
		if ( isset ( $passedAttributes[$attribute] ) ) {
			return $passedAttributes[$attribute];
		}
		$definedAliases = self::ALIASES;
		if ( isset( $definedAliases[$attribute] ) ) {
			$aliases = (array)$definedAliases[$attribute];
			foreach ( $aliases as $alias ) {
				if ( isset( $passedAttributes[$alias] ) ) {
					return $passedAttributes[$alias];
				}
			}
		}
		return null;
	}

	/**
	 * @param string $value
	 *
	 * @return bool|string
	 */
	private function verifyValueForNoValueAttribute( $value ) {
		if ( in_array( strtolower( $value ), $this->noValues, true ) ) {
			return false;
		}
		return empty( $value ) ? true : $value;
	}
}