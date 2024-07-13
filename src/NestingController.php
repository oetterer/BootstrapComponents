<?php
/**
 * Contains the class handling the component nesting stack.
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

use MediaWiki\MediaWikiServices;
use MWException;

/**
 * Class NestingController
 *
 * Takes care of some things that occur when nesting bootstrap components
 *
 * @since 1.0
 */
class NestingController {

	/**
	 * List of ids already in use in the context of the bootstrap components.
	 * Key is of this array is the component name, value is the next usable id.
	 *
	 * @var array $autoincrementPerComponent
	 */
	private array $autoincrementPerComponent;

	/**
	 * Holds information about the bootstrap component stack, so that components
	 * can get information about their parent "nest".
	 *
	 * Consists of elements of type {@see Nestable}.
	 *
	 * @var array $componentStack
	 */
	private array $componentStack;

	/**
	 * NestingController constructor.
	 *
	 * Do not instantiate directly, but use {@see MediaWikiServices::get()} with argument
	 * 'NestingController' instead.
	 */
	public function __construct() {
		$this->autoincrementPerComponent = [];
		$this->componentStack = [];
	}

	/**
	 * Signals the closing of a bootstrap component.
	 *
	 * @param string|false $id id of the current object we are trying to close
	 *
	 * @throws MWException if current and closing component is different
	 */
	public function close( $id ): void {
		$current = $this->getCurrentElement();
		if ( !$current ) {
			throw new MWException( 'Nesting error. Tried to close an empty stack.' );
		}
		if ( $id === false || ($current->getId() != $id) ) {
			throw new MWException( 'Nesting error. Trying to close a component that is not the currently open one.' );
		}
		array_pop( $this->componentStack );
	}

	/**
	 * Generates an id not in use within any bootstrap component on this page yet.
	 *
	 * @param string $componentName
	 *
	 * @return string
	 */
	public function generateUniqueId( string $componentName ): string {
		if ( !isset( $this->autoincrementPerComponent[$componentName] ) ) {
			$this->autoincrementPerComponent[$componentName] = 0;
		}
		return 'bsc_' . $componentName . '_' . ($this->autoincrementPerComponent[$componentName]++);
	}

	/**
	 * Returns a reference to the last opened component.
	 *
	 * @note do not declare a return type for it will break unit tests.
	 *
	 * @return false|NestableInterface
	 */
	public function getCurrentElement() {
		return end( $this->componentStack );
	}

	/**
	 * Returns the size of the stack.
	 *
	 * @return int
	 */
	public function getStackSize(): int {
		return count( $this->componentStack );
	}

	/**
	 * Signals the opening of a bootstrap component (thus letting the nc put the nestable component on its stack).
	 *
	 * @param NestableInterface $nestable
	 *
	 * @throws MWException when open is called with an invalid object
	 */
	public function open( mixed &$nestable ): void {
		if ( !$nestable instanceof NestableInterface ) {
			throw new MWException( 'Nesting error. Trying to put an object other than a Component an the nesting stack.' );
		}
		$this->componentStack[] = $nestable;
	}
}
