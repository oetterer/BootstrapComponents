<?php
/**
 * Contains the interface for nestable objects handled by {@see NestingController}.
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

namespace MediaWiki\Extension\BootstrapComponents;

/**
 * Interface Nestable
 *
 * All entities, that can be handled by the {@see \BootstrapComponents\NestingController}
 *
 * @since 1.0
 */
interface NestableInterface {
	/**
	 * Returns the name of the component.
	 *
	 * @return string
	 */
	public function getComponentName();

	/**
	 * Returns the id used in html output. Unique in a given page context.
	 *
	 * @return string
	 */
	public function getId();
}
