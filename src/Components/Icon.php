<?php
/**
 * Contains the component class for rendering an icon.
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
 * Class Icon
 *
 * Class for component 'icon'
 *
 * @see   https://github.com/oetterer/BootstrapComponents/blob/master/docs/components.md#Icon
 * @since 1.0
 */
class Icon extends AbstractComponent {
	/**
	 * @inheritdoc
	 *
	 * @param string $input
	 */
	public function placeMe( $input ) {
		if ( empty( $input ) ) {
			return $this->getParserOutputHelper()->renderErrorMessage( 'bootstrap-components-glyph-icon-name-missing' );
		}

		return Html::rawElement(
			'span',
			[ 'class' => 'glyphicon glyphicon-' . strtolower( trim( $input ) ) ]
		);
	}
}