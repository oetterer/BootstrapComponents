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

namespace BootstrapComponents\Components;

use BootstrapComponents\ComponentLibrary;
use BootstrapComponents\AbstractComponent;
use BootstrapComponents\NestingController;
use BootstrapComponents\ParserOutputHelper;
use \Html;
use \MWException;

/**
 * Class Card
 *
 * Class for component 'card'
 *
 * @see   https://github.com/oetterer/BootstrapComponents/blob/master/docs/components.md#Card
 * @since 4.0
 */
class Card extends AbstractComponent {

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
	 * Card constructor.
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
	protected function placeMe( $input ) {

		$this->collapsible = $this->getValueFor( 'collapsible' ) || $this->isInsideAccordion();

		$outerClass = $this->calculateOuterClassAttribute();
		$innerClass = $this->calculateInnerClassAttribute();
		$bodyClass = $this->calculateBodyClassAttribute();

		list ( $outerClass, $style ) = $this->processCss( $outerClass, [] );

		return Html::rawElement(
			'div',
			[
				'class' => $this->arrayToString( $outerClass, ' ' ),
				'style' => $this->arrayToString( $style, ';' ),
			],
			$this->processAdditionToPanel( 'header' )
			. Html::rawElement(
				'div',
				[
					'id'    => $this->getId(),
					'class' => $this->arrayToString( $innerClass, ' ' ),
				],
				Html::rawElement(
					'div',
					[
						'class' => $this->arrayToString( $bodyClass, ' ' ),
					],
					$input
				)
				. $this->processAdditionToPanel( 'footer' )
			)
		);
	}

	/**
	 * Calculates the css class from the attributes array for the text body
	 *
	 * @return array
	 */
	private function calculateBodyClassAttribute() {
		$class = [ 'card-body' ];
		if ( $this->hasValueFor( 'color' ) && ( $this->getValueFor( 'color', 'primary' ) != 'light' ) ) {
			$class[] = 'text-' . $this->getValueFor( 'color', 'primary' );
		}
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
			$class = [ 'card-collapse', 'collapse', 'fade' ];
			if ( $this->getValueFor( 'active' ) ) {
				$class[] = 'show';
			}
		}
		return $class;
	}

	/**
	 * Calculates the css class string from the attributes array
	 *
	 * @return string[]
	 */
	private function calculateOuterClassAttribute() {

		$class = [ 'card' ];
		if ( $this->hasValueFor( 'background' ) ) {
			$class[] = 'bg-' . $this->getValueFor( 'background', 'primary' );
			if ( $this->getValueFor( 'background', 'primary' ) != 'light' ) {
				$class[] = 'text-white';
			}
		} elseif ( $this->hasValueFor( 'color' ) ) {
			$class[] = 'border-' . $this->getValueFor( 'color', 'primary' );
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
			if ( $type == 'header' && $this->isInsideAccordion() ) {
				$inside = $this->getId();
			} else {
				return '';
			}
		}
		$newAttributes = [
			'class' => 'card-' . $type,
		];
		if ( $type == 'header' ) {
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
					'class' => 'card-title',
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
