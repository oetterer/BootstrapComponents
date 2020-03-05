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
			new AttributeManager( [], [] )
		);
	}

	public function testCannotConstructWithInvalidAlias() {
		$this->expectException( \LogicException::class );
		$instance = new AttributeManager( [ 'class', 'id', 'style' ], [ 'test' => 'unknown' ] );
	}

	public function testGetAllAttributes() {
		$manager = new AttributeManager( [], [] );
		$this->assertEquals(
			[
				'active', 'background', 'class', 'color', 'collapsible', 'disabled', 'dismissible', 'fade', 'footer', 'header',
				'id', 'link', 'outline', 'pill', 'placement', 'size', 'style', 'text', 'trigger',
			],
			$manager->getAllKnownAttributes()
		);
	}

	/**
	 * @param string[] $attributes
	 * @param array    $aliases
	 * @param string   $attribute
	 * @param bool     $expected
	 *
	 * @dataProvider providerIsValid
	 */
	public function testIsValid( $attributes, $aliases, $attribute, $expected ) {
		$manager = new AttributeManager( $attributes, $aliases );
		$this->assertEquals(
			$expected,
			$manager->isValid( $attribute )
		);
	}

	/**
	 * @param string[] $attributes
	 * @param array    $aliases
	 * @param string   $attribute
	 * @param string[] $request
	 * @param bool     $expected
	 *
	 * @dataProvider providerIsSuppliedInRequest
	 */
	public function testIsSuppliedInRequest( $attributes, $aliases, $attribute, $request, $expected ) {
		$manager = new AttributeManager( $attributes, $aliases );
		$this->assertEquals(
			$expected,
			$manager->isSuppliedInRequest( $attribute, $request )
		);
	}

	/**
	 * @param string[] $attributes
	 * @param array    $aliases
	 * @param string   $attribute
	 * @param mixed    $value
	 * @param string   $expectedAttribute
	 * @param mixed    $expectedValue
	 *
	 * @dataProvider providerVerifyAttributeAndValue
	 */
	public function testVerifyAttributeAndValue( $attributes, $aliases, $attribute, $value, $expectedAttribute, $expectedValue ) {
		$manager = new AttributeManager( $attributes, $aliases );
		list( $returnedAttribute, $returnedValue ) = $manager->validateAttributeAndValue( $attribute, $value );
		$this->assertEquals( $expectedAttribute, $returnedAttribute );
		$this->assertEquals( $expectedValue, $returnedValue );
	}

	/**
	 * @return array
	 */
	public function providerIsValid() {
		return [
			// $attributes, $aliases, $attribute, $expected
			'normal' => [ [ 'class', 'id' ], [], 'class', true ],
			'fail'   => [ [ 'id', 'style' ], [], 'class', false ],
			'alias'  => [ [ 'class', 'id' ], [ 'stand' => 'class' ], 'stand', true ],
		];
	}

	/**
	 * @return array
	 */
	public function providerIsSuppliedInRequest() {
		return [
			// $attributes, $aliases, $attribute, $request, $expected
			'normal' => [ [ 'class', 'id' ], [], 'class', [ 'class' ], true ],
			'fail'   => [ [ 'class', 'id' ], [], 'class', [ 'id', 'style' ], false ],
			'alias'  => [ [ 'class', 'id' ], [ 'stand' => 'class' ], 'class', [ 'stand' ], true ],
		];
	}

	/**
	 * @return array
	 */
	public function providerVerifyAttributeAndValue() {
		$data = [
			// $attributes, $aliases, $attribute, $value, $expectedAttribute, $expectedValue
			'any w/ value'                 => [ [ 'header' ], [], 'header', 'foo bar', 'header', 'foo bar' ],
			'any w/ empty string'          => [ [ 'header' ], [], 'header', '', 'header', '' ],
			'any w/ null'                  => [ [ 'header' ], [], 'header', null, 'header', null ],
			'noFalseValue w/ empty string' => [ [ 'active' ], [], 'active', '', 'active', true ],
			'noFalseValue w/ any'          => [ [ 'active' ], [], 'active', 'foobar', 'active', 'foobar' ],
			'fixedList w/ match'           => [ [ 'color' ], [], 'color', 'danger', 'color', 'danger' ],
			'fixedList w/o match'          => [ [ 'color' ], [], 'color', 'ease', 'color', null ],
			'alias'                        => [ [ 'header' ], [ 'heading' => 'header '], 'heading', 'foo bar', 'header', 'foo bar' ],
		];
		// adding no values
		foreach ( [ false, 0, '0', 'no', 'false', 'off', 'disabled', 'ignored' ] as $key => $noValue ) {
			$data['noFalseValue #' . $key] = [ [ 'active' ], [], 'active', $noValue, 'active', false ];
		}
		return $data;
	}
}
