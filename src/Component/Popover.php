<?php
/**
 * Contains the component class for rendering a popover.
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
 * Class Popover
 *
 * Class for component 'popover'
 *
 * @see   https://github.com/oetterer/BootstrapComponents/blob/master/docs/components.md#Popover
 * @since 1.0
 */
class Popover extends AbstractComponent {
	/**
	 * @inheritdoc
	 *
	 * @param string $input
	 */
	public function placeMe( $input ) {
		$heading = $this->getValueFor( 'heading' );
		$text    = $this->getValueFor( 'text' );
		if ( $heading === false || !strlen( $heading ) ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-popover-heading-missing' );
		}
		if ( $text === false || !strlen( $text ) ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-popover-text-missing' );
		}

		list( $tag, $text, $attributes ) = $this->buildHtmlElements( $input, (string)$text, (string)$heading );

		// I cannot use the button class here, because it needs a target and also does not accept pre-processed attributes.
		return Html::rawElement(
			$tag,
			$attributes,
			$text
		);
	}

	/**
	 * @param string $input
	 * @param string $text
	 * @param string $heading
	 *
	 * @return array $tag, $text, $attributes
	 */
	private function buildHtmlElements( $input, $text, $heading ) {
		list ( $class, $style ) = $this->processCss( $this->calculatePopoverClassAttribute(), [] );

		list ( $text, $target ) = $this->stripLinksFrom( $text, '' );

		$attributes = [
			'class'          => $this->arrayToString( $class, ' ' ),
			'style'          => $this->arrayToString( $style, ';' ),
			'id'             => $this->getId(),
		];
		if ( empty( $target ) ) {
			// this is the normal popover process
			$attributes = array_merge(
				$attributes,
				[
					'data-toggle'    => 'popover',
					'title'          => $heading,
					'data-content'   => str_replace( "\n", " ", trim( $input ) ),
					'data-placement' => $this->getValueFor( 'placement' ),
					'data-trigger'   => $this->getValueFor( 'trigger' ),
				]
			);
			$tag = "button";
		} else {
			$attributes['href'] = $target;
			$attributes['role'] = 'button';
			$tag = "a";
		}
		return [ $tag, $text, $attributes ];
	}

	/**
	 * Calculates the class attribute value from the passed attributes
	 *
	 * @return string[]
	 */
	private function calculatePopoverClassAttribute() {
		$class = [ 'btn', 'btn-' . $this->getValueFor( 'color', 'info' ) ];
		if ( $size = $this->getValueFor( 'size' ) ) {
			$class[] = 'btn-' . $size;
		}
		return $class;
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
			return [ $matches[2], htmlspecialchars_decode( $matches[1] ) ];
		}
		return [ preg_replace( '~^(.*)(<a.+href=[^>]+>)(.+)(</a>)(.*)$~ms', '\1\3\5', $text ), $target ];
	}
}