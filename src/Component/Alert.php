<?php
/**
 * Contains the component class for rendering an alert.
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

use BootstrapComponents\AbstractComponent;
use BootstrapComponents\ParserRequest;
use \Html;
use \MWException;

/**
 * Class Alert
 *
 * Class for component 'alert'
 *
 * @see   https://github.com/oetterer/BootstrapComponents/blob/master/docs/components.md#Alert
 * @since 1.0
 */
class Alert extends AbstractComponent {
	/**
	 * Indicates, whether this alert is dismissible
	 *
	 * @var boolean $dismissible
	 */
	private $dismissible;

	/**
	 * @inheritdoc
	 *
	 * @param string $input
	 */
	public function placeMe( $input ) {

		$this->dismissible = (bool)$this->getValueFor( 'dismissible' );

		$class = $this->calculateAlertClassAttribute();
		$inside = $input;
		if ( $this->isDismissible() ) {
			$inside = $this->renderDismissButton() . $inside;
		}

		list ( $class, $style ) = $this->processCss( $class, [] );
		return Html::rawElement(
			'div',
			[
				'class' => $this->arrayToString( $class, ' ' ),
				'style' => $this->arrayToString( $style, ';' ),
				'id'    => $this->getId(),
				'role'  => 'alert',
			],
			$inside
		);
	}

	/**
	 * Calculates the class attribute value from the passed attributes
	 *
	 * @return string[]
	 */
	private function calculateAlertClassAttribute() {
		$class = [ 'alert' ];
		$class[] = 'alert-' . $this->getValueFor( 'color', 'info' );

		if ( $this->isDismissible() ) {
			if ( $this->getValueFor( 'dismissible' ) === 'fade' ) {
				$class = array_merge( $class, [ 'fade', 'in' ] );
			} else {
				$class[] = 'alert-dismissible';
			}
		}
		return $class;
	}

	/**
	 * Indicates, whether this alert is dismissible or not
	 *
	 * @return bool
	 */
	private function isDismissible() {
		return $this->dismissible;
	}


	/**
	 * Generates the button, that lets us dismiss this alert
	 *
	 * @return string
	 */
	private function renderDismissButton() {
		return Html::rawElement(
			'div',
			[
				'type'         => 'button',
				'class'        => 'close',
				'data-dismiss' => 'alert',
				'aria-label'   => wfMessage( 'bootstrap-components-close-element' )->inContentLanguage()->text(),
			],
			Html::rawElement(
				'span',
				[
					'aria-hidden' => 'true',
				],
				'&times;'
			)
		);
	}
}