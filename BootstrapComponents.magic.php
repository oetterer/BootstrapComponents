<?php
/**
 * File holding the BootstrapComponents magic words definition.
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

use MediaWiki\MediaWikiServices;

$magicWords = [];

/** @var \MediaWiki\Extension\BootstrapComponents\ComponentLibrary $componentLibrary */
$componentLibrary = MediaWikiServices::getInstance()->getService( 'BootstrapComponents.ComponentLibrary' );

// English
$magicWords['en'] = $componentLibrary->compileMagicWordsArray();

$magicWords['en']['BSC_NO_IMAGE_MODAL'] = array( 0, '__NOIMAGEMODAL__' );
