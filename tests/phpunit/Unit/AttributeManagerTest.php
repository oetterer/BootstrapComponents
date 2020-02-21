<?php

namespace BootstrapComponents\Tests\Unit;

use BootstrapComponents\AttributeManager;
use \PHPUnit_Framework_TestCase;

/**
 * @covers  \BootstrapComponents\AttributeManager
 *
 * @ingroup Test
 *
 * @group   extension-bootstrap-components
 * @group   mediawiki-databaseless
 *
 * @license GNU GPL v3+
 *
 * @since   1.0
 * @author  Tobias Oetterer
 */
class AttributeManagerTest extends PHPUnit_Framework_TestCase {
	public function testCanConstruct() {

		$this->assertInstanceOf(
			'BootstrapComponents\\AttributeManager',
			new AttributeManager( [] )
		);
	}

	public function testGetAllAttributes() {
		$instance = new AttributeManager( [] );
		$this->assertEquals(
			[
				'active', 'class', 'color', 'collapsible', 'disabled', 'dismissible', 'footer', 'heading',
				'id', 'link', 'outline', 'pill', 'placement', 'size', 'style', 'text', 'trigger',
			],
			$instance->getAllKnownAttributes()
		);
	}

	/**
	 * @param string $attribute
	 * @param array  $allowedValues
	 *
	 * @dataProvider allowedValuesForAttributeProvider
	 */
	public function testGetAllowedValuesFor( $attribute, $allowedValues ) {
		$instance = new AttributeManager( [ $attribute ] );
		$this->assertEquals(
			$allowedValues,
			$instance->getAllowedValuesFor( $attribute )
		);
	}

	/**
	 * @param string    $newAttribute
	 * @param int|array $allowedValue
	 *
	 * @dataProvider canRegisterNewAttributesProvider
	 */
	public function testCanRegisterNewAttributes( $newAttribute, $allowedValue ) {
		$instance = new AttributeManager( [] );
		$this->assertTrue(
			!$instance->isRegistered( $newAttribute )
		);
		$this->assertTrue(
			$instance->register( $newAttribute, $allowedValue )
		);
		$this->assertTrue(
			$instance->isRegistered( $newAttribute )
		);
		$this->assertEquals(
			$allowedValue,
			$instance->getAllowedValuesFor( $newAttribute )
		);
	}

	public function testFailRegister() {
		$instance = new AttributeManager( [] );
		$this->assertTrue(
			!$instance->register( '', 1 )
		);
		$this->assertTrue(
			!$instance->register( 'empty_array_fail', [] )
		);
	}

	/**
	 * @param string $attribute
	 * @param array  $valuesToTest
	 *
	 * @dataProvider verifyValueProvider
	 */
	public function testVerifyAttributes( $attribute, $valuesToTest ) {
		$instance = new AttributeManager( [ 'id', 'style', $attribute ] );
		foreach ( $valuesToTest as $value ) {
			$attributesToVerify = [ $attribute => $value ];
			$expectedVerifiedAttributes = [ 'id' => false, 'style' => false, $attribute => $value ];
			$this->assertEquals(
				$expectedVerifiedAttributes,
				$instance->verifyAttributes( $attributesToVerify ),
				'failed with value (' . gettype( $value ) . ') ' . $value . ' for attribute ' . $attribute
			);
		}
	}

	public function testVerifyAttributesAliases() {
		$instance = new AttributeManager( [ 'heading', 'footer' ] );
		$attributesToVerify = [
			'header'  => 'heading text',
			'footing' => 'footer text',
		];
		$this->assertEquals(
			[
				'heading'  => 'heading text',
				'footer' => 'footer text',
			],
			$instance->verifyAttributes( $attributesToVerify )
		);
	}

	/**
	 * @param string $attribute
	 * @param array  $valuesToTest
	 *
	 * @dataProvider failToVerifyValueProvider
	 */
	public function testFailToVerifyAttributes( $attribute, $valuesToTest ) {
		$instance = new AttributeManager( [ $attribute ] );
		foreach ( $valuesToTest as $value ) {
			$attributesToVerify = [ $attribute => $value ];
			$this->assertEquals(
				[ $attribute => false ],
				$instance->verifyAttributes( $attributesToVerify ),
				'failed with false value (' . gettype( $value ) . ') ' . $value . ' for attribute ' . $attribute
			);
		}
	}

	public function testFailToVerifyUnknownAttributes() {
		$instance = new AttributeManager( [ 'class', 'id', 'style' ] );
		$attributesToVerify = [ 'rnd' => md5( microtime() ) ];
		$this->assertEquals(
			[ 'class' => false, 'id' => false, 'style' => false ],
			$instance->verifyAttributes( $attributesToVerify )
		);
	}

	/**
	 * @return array[]
	 */
	public function allowedValuesForAttributeProvider() {
		return [
			'active'      => [ 'active', false ],
			'class'       => [ 'class', true ],
			'color'       => [ 'color', [ 'default', 'primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'white', ] ],
			'collapsible' => [ 'collapsible', false ],
			'disabled'    => [ 'disabled', false ],
			'dismissible' => [ 'dismissible', false ],
			'footer'      => [ 'footer', true ],
			'heading'     => [ 'heading', true ],
			'id'          => [ 'id', true ],
			'link'        => [ 'link', true ],
			'outline'     => [ 'outline', false ],
			'pill'        => [ 'pill', false ],
			'placement'   => [ 'placement', [ 'top', 'bottom', 'left', 'right' ] ],
			'size'        => [ 'size', [ 'xs', 'sm', 'md', 'lg' ] ],
			'style'       => [ 'style', true ],
			'text'        => [ 'text', true ],
			'trigger'     => [ 'trigger', [ 'default', 'focus', 'hover' ] ],
			'rnd'         => [ md5( microtime() ), null ],
		];
	}

	/**
	 * @return array
	 */
	public function canRegisterNewAttributesProvider() {
		return [
			'any_value' => [ 'any_value', AttributeManager::ANY_VALUE ],
			'no_value'  => [ 'no_value', AttributeManager::NO_FALSE_VALUE ],
			'array'     => [ 'array_value', [ 'yes', 'no' ] ],
		];
	}

	/**
	 * @return array[]
	 */
	public function verifyValueProvider() {
		return [
			'active'  => [ 'active', [ md5( microtime() ), md5( microtime() . microtime() ) ] ],
			'class'   => [ 'class', [ md5( microtime() ), md5( microtime() . microtime() ) ] ],
			'color'   => [ 'color', [ 'default', 'primary', 'success', 'info', 'warning', 'danger' ] ],
		];
	}

	/**
	 * @return array[]
	 */
	public function failToVerifyValueProvider() {
		return [
			'active'      => [ 'active', [ 0, 'no', 'false', 'off', '0', 'disabled', 'ignored' ] ],
			'collapsible' => [ 'collapsible', [ 0, 'no', 'false', 'off', '0', 'disabled', 'ignored' ] ],
			'color'       => [ 'color', [ 0, 'no', 'false', 'off', '0', 'disabled', 'ignored' ] ],
			'disabled'    => [ 'disabled', [ 0, 'no', 'false', 'off', '0', 'disabled', 'ignored' ] ],
			'dismissible' => [ 'dismissible', [ 0, 'no', 'false', 'off', '0', 'disabled', 'ignored' ] ],
		];
	}
}
