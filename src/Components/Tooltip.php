<?php
/**
 * Contains the component class for rendering a tooltip.
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

/**
 * Class Tooltip
 *
 * Class for component 'tooltip'
 *
 * @see   https://github.com/oetterer/BootstrapComponents/blob/master/docs/components.md#Tooltip
 * @since 1.0
 */
class Tooltip extends AbstractComponent {
	/**
	 * @inheritdoc
	 *
	 * @param string $input
	 */
	public function placeMe( $input ) {
		if ( empty( $input ) ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-tooltip-target-missing' );
		}
		$tooltip = $this->getValueFor( 'text' );
		if ( empty( $tooltip ) ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-tooltip-content-missing' );
		}
		list( $tag, $input, $attributes ) = $this->buildHtmlElements( $input, (string)$tooltip );

		return [
				Html::rawElement(
				$tag,
				$attributes,
				$input
			),
			'isHTML'  => true,
			'noparse' => true,
		];
	}

	/**
	 * @param string $input
	 * @param string $tooltip
	 *
	 * @return array $tag, $text, $attributes
	 */
	private function buildHtmlElements( $input, $tooltip ) {
		list ( $class, $style ) = $this->processCss( [ 'bootstrap-tooltip' ], [] );

		list ( $input, $target ) = $this->stripLinksFrom( $input, '' );

		$attributes = [
			'class'          => $this->arrayToString( $class, ' ' ),
			'style'          => $this->arrayToString( $style, ';' ),
			'id'             => $this->getId(),
		];
		if ( empty( $target ) ) {
			// this is the normal tooltip process
			$attributes = array_merge(
				$attributes,
				[
					'data-toggle'    => 'tooltip',
					'title'          => $tooltip,
					'data-placement' => $this->getValueFor( 'placement' ),
				]
			);
			$tag = "span";
		} else {
			$attributes['href'] = $target;
			$tag = "a";
		}
		return [ $tag, $input, $attributes ];
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