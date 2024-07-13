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

namespace MediaWiki\Extension\BootstrapComponents\Components;

use MediaWiki\Extension\BootstrapComponents\ComponentLibrary;
use MediaWiki\Extension\BootstrapComponents\AbstractComponent;
use MediaWiki\Extension\BootstrapComponents\NestingController;
use MediaWiki\Extension\BootstrapComponents\ParserOutputHelper;
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
	private bool $collapsible;

	/**
	 * If true, indicates that we are inside an accordion
	 *
	 * @var bool $insideAccordion
	 */
	private bool $insideAccordion;

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
		$parent = $this->getParentComponent();
		$this->insideAccordion = ($parent && ($this->getParentComponent()->getComponentName() == 'accordion'));
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

		$innerAttributes = [
			'id'    => $this->getId(),
			'class' => $this->arrayToString( $innerClass, ' ' ),
		];
		if ( $this->isCollapsible() ) {
			$innerAttributes['data-parent'] = $this->getDataParent();
			$innerAttributes['aria-labelledby'] = $this->getId() . '_header';
		}

		return Html::rawElement(
			'div',
			[
				'class' => $this->arrayToString( $outerClass, ' ' ),
				'style' => $this->arrayToString( $style, ';' ),
			],

			$this->processAdditionToCard( 'header' )
			. $this->injectCssClass( $this->getValueFor( 'header-image' ), 'img', 'card-img-top' )
			. Html::rawElement(
				'div',
				$innerAttributes,
				Html::rawElement(
					'div',
					[
						'class' => $this->arrayToString( $bodyClass, ' ' ),
						'style' => $this->getValueFor( 'body-style' ) ?: null
					],
					$input
				)
				. $this->injectCssClass( $this->getValueFor( 'footer-image' ), 'img', 'card-img-bottom' )
				. $this->processAdditionToCard( 'footer' )
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

	private function injectCssClass(string $subject, string $tag, string $class ): string {
		$outerMatches = [];
		if ( !preg_match( '/^(.*<)' . $tag . '(.[^>]*)(>.*)$/', $subject, $outerMatches ) ) {
			// tag not found in subject
			return $subject;
		}
		$innerMatches = [];
		if ( !preg_match( '/^(.*class=")([^"]+)(".*)$/', $outerMatches[2], $innerMatches ) ) {
			// there is no class attribute for tag $tag
			return $outerMatches[1] . $tag . ' class="' . $class . '" ' . $outerMatches[2] . $outerMatches[3];
		}
		if ( strpos( $innerMatches[2], $class ) === false ) {
			// there is a class attribute, but it does not contain the desired class
			return $outerMatches[1] . $tag . $innerMatches[1] . $class . ' '
				. $innerMatches[2] . $innerMatches[3] . $outerMatches[3];
		}
		return $subject;
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
		return $this->insideAccordion;
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
	private function processAdditionToCard( string $type ): string {
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
		if ( $this->getValueFor( $type . '-style' ) ) {
			$newAttributes['style'] = $this->getValueFor( $type . '-style' );
		}
		if ( $type == 'header' ) {
			if ( $this->isCollapsible() ) {
				$newAttributes += [
					'data-toggle'   => 'collapse',
					'data-target'   => '#' . $this->getId(),
					'aria-controls' => $this->getId(),
					'aria-expanded' => $this->getValueFor( 'active' ) ? 'true' : 'false',
					'id'            => $this->getId() . '_header'
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
