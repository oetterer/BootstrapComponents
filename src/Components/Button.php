<?php
/**
 * Contains the component class for rendering a button.
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

use BootstrapComponents\AbstractComponent;
use \Html;
use \Title;

/**
 * Class Button
 *
 * Class for component 'button'
 *
 * @see   https://github.com/oetterer/BootstrapComponents/blob/master/docs/components.md#Button
 * @since 1.0
 */
class Button extends AbstractComponent {
	/**
	 * @var array $rawAttributes
	 */
	private $rawAttributes = [];

	/**
	 * Allows spawning objects (like {@see \BootstrapComponents\Collapse}) to insert additional data into the button tag.
	 * These attributes and their values will neither be parsed or verified. It is assumed, that you know what you are doing.
	 *
	 * @param array $rawAttributes of form attribute => value
	 */
	public function injectRawAttributes( array $rawAttributes ) {
		if ( is_array( $rawAttributes ) && count( $rawAttributes ) ) {
			$this->rawAttributes += $rawAttributes;
		}
	}

	/**
	 * @inheritdoc
	 *
	 * @param string $input
	 */
	public function placeMe( $input ) {
		if ( empty( $input ) ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-button-target-missing' );
		}

		list( $target, $text ) = $this->getTargetAndText( $input );

		if ( empty( $target ) ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-button-target-invalid' );
		}

		list ( $class, $style ) = $this->processCss(
			$this->calculateClassAttribute(),
			[]
		);

		return [
			Html::rawElement(
				'a',
				[
					'class' => $this->arrayToString( $class, ' ' ),
					'style' => $this->arrayToString( $style, ';' ),
					'role'  => 'button',
					'id'    => $this->getId(),
					'href'  => $target,
				] + $this->rawAttributes,
				$text
			),
			'isHTML'  => true,
			'noparse' => true,
		];
	}

	/**
	 * Calculates the css class string from the attributes array.
	 *
	 * @return string[]
	 */
	private function calculateClassAttribute() {

		$class = [ "btn" ];
		$colorClass = 'btn-';
		if ( (bool)$this->getValueFor( 'outline' ) ) {
			$colorClass .= 'outline-';
		}
		$class[] = $colorClass . $this->getValueFor( 'color', 'primary' );
		if ( $size = $this->getValueFor( 'size' ) ) {
			$class[] = "btn-" . $size;
		}
		if ( $this->getValueFor( 'active' ) ) {
			$class[] = 'active';
		}
		if ( $this->getValueFor( 'disabled' ) ) {
			$class[] = 'disabled';
		}
		return $class;
	}

	/**
	 * Generates a valid target a suitable text for the button
	 *
	 * @param string $input
	 *
	 * @return array containing (string|null) target, (string) text. Note that target is null when invalid
	 */
	private function getTargetAndText( $input ) {
		$target = trim( $input );
		$text = (string)$this->getValueFor( 'text' );
		if ( empty( $text ) ) {
			$text = $target;
		}
		if ( strlen( $target ) && !preg_match( '/^#[A-Za-z_0-9-]+/', $target ) ) {
			// $target is not a fragment (e.g. not #anchor)
			$targetTitle = Title::newFromText( $target );
			$target = $targetTitle ? $targetTitle->getLocalURL() : null;
		}
		list( $text, $target ) = $this->stripLinksFrom( $text, $target );
		return [ $target, $text ];
	}

	/**
	 * @param string $text
	 * @param string $target
	 *
	 * @return string[]
	 */
	private function stripLinksFrom( $text, $target ) {
		if ( preg_match( '~<a.+href=.([^>]+Special:Upload[^"]+)[^>]*>(.+)</a>~', $text, $matches ) ) {
			// we have an non existing image as text, return image name as text and upload url as target
			// since $text was already parsed and html_encoded and Html::rawElement will do this again,
			// we need to decode the html special characters in target aka $matches[1]
			// also clear additionally injected raw attributes
			$this->rawAttributes = [];
			return [ $matches[2], htmlspecialchars_decode( $matches[1] ) ];
		}
		return [ preg_replace( '~^(.*)(<a.+href=[^>]+>)(.+)(</a>)(.*)$~ms', '\1\3\5', $text ), $target ];
	}
}
