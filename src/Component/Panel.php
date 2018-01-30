<?php
/**
 * Contains the component class for rendering a panel.
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

namespace BootstrapComponents\Component;

use BootstrapComponents\ComponentLibrary;
use BootstrapComponents\AbstractComponent;
use BootstrapComponents\NestingController;
use BootstrapComponents\ParserOutputHelper;
use BootstrapComponents\ParserRequest;
use \Html;
use \MWException;
use \Parser;

/**
 * Class Panel
 *
 * Class for component 'panel'
 *
 * @see   https://github.com/oetterer/BootstrapComponents/blob/master/docs/components.md#Panel
 * @since 1.0
 */
class Panel extends AbstractComponent {

	/**
	 * Indicates, whether this panel is collapsible
	 *
	 * @var bool $collapsible
	 */
	private $collapsible;

	/**
	 * If true, indicates that we are inside an accordion
	 *
	 * @var bool $insideAccordion
	 */
	private $insideAccordion;

	/**
	 * Panel constructor.
	 *
	 * @param ComponentLibrary   $componentLibrary
	 * @param ParserOutputHelper $parserOutputHelper
	 * @param NestingController  $nestingController
	 *
	 * @throws MWException
	 */
	public function __construct( $componentLibrary, $parserOutputHelper, $nestingController ) {
		parent::__construct( $componentLibrary, $parserOutputHelper, $nestingController );
		$this->collapsible = false;
		$this->insideAccordion = $this->isInsideAccordion();
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $input
	 */
	public function placeMe( $input ) {

		$this->collapsible = $this->getValueFor( 'collapsible' ) || $this->isInsideAccordion();

		$outerClass = $this->calculateOuterClassAttribute();
		$innerClass = $this->calculateInnerClassAttribute();

		list ( $outerClass, $style ) = $this->processCss( $outerClass, [] );

		return Html::rawElement(
			'div',
			[
				'class' => $this->arrayToString( $outerClass, ' ' ),
				'style' => $this->arrayToString( $style, ';' ),
			],
			$this->processAdditionToPanel( 'heading' )
			. Html::rawElement(
				'div',
				[
					'id'    => $this->getId(),
					'class' => $this->arrayToString( $innerClass, ' ' ),
				],
				Html::rawElement(
					'div',
					[
						'class' => 'panel-body',
					],
					$input
				)
				. $this->processAdditionToPanel( 'footer' )
			)
		);
	}

	/**
	 * Calculates the css class string from the attributes array
	 *
	 * @return string[]
	 */
	private function calculateOuterClassAttribute() {

		$class = [ 'panel' ];
		$class[] = 'panel-' . $this->getValueFor( 'color', 'default' );
		return $class;
	}

	/**
	 * Calculates the css class from the attributes array for the "inner" section (div around body and footer)
	 *
	 * @return bool|array
	 */
	private function calculateInnerClassAttribute() {

		$class = false;
		if ( $this->isCollapsible() ) {
			$class = [ 'panel-collapse', 'collapse', 'fade' ];
			if ( $this->getValueFor( 'active' ) ) {
				$class[] = 'in';
			}
		}
		return $class;
	}

	/**
	 * Returns my data parent attribute (the one to put in the heading toggle when inside an accordion).
	 *
	 * @return string
	 */
	private function getDataParent() {
		$parent = $this->getParentComponent();
		if ( $parent && $this->isInsideAccordion() && $parent->getId() ) {
			return '#' . $parent->getId();
		}
		return false;
	}

	/**
	 * Indicates, whether this panel is collapsible or not.
	 *
	 * @return bool
	 */
	private function isCollapsible() {
		return $this->collapsible;
	}

	/**
	 * Checks, whether this panel is directly inside an accordion.
	 *
	 * @return bool
	 */
	private function isInsideAccordion() {
		if ( !is_null( $this->insideAccordion ) ) {
			return $this->insideAccordion;
		}
		$parent = $this->getParentComponent();
		return $this->insideAccordion = ($parent && ($this->getParentComponent()->getComponentName() == 'accordion'));
	}

	/**
	 * Processes the addition heading or footer.
	 *
	 * This examines $attributes and produces an appropriate heading or footing if corresponding data is found.
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	private function processAdditionToPanel( $type ) {
		$inside = $this->getValueFor( $type );

		if ( empty( $inside ) ) {
			if ( $type == 'heading' && $this->isInsideAccordion() ) {
				$inside = $this->getId();
			} else {
				return '';
			}
		}
		$newAttributes = [
			'class' => 'panel-' . $type,
		];
		if ( $type == 'heading' ) {
			if ( $this->isCollapsible() ) {
				$newAttributes += [
						'data-parent' => $this->getDataParent(),
						'data-toggle' => 'collapse',
						'href'        => '#' . $this->getId(),
					];
			}
			$inside = Html::rawElement(
				'h4',
				[
					'class' => 'panel-title',
					'style' => 'margin-top:0;padding-top:0;',
				],
				$inside
			);
		}

		return Html::rawElement(
			'div',
			$newAttributes,
			$inside
		);
	}
}