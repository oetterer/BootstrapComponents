<?php
/**
 * Contains the class holding a modal building kit.
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

namespace BootstrapComponents;

use \Html;

/**
 * Class ModalBase
 *
 * This is a low layer class, that helps build a modal. It does not have access to a parser, so it expects all content
 * elements to be hardened by you (with the help of {@see Parser::recursiveTagParse}). All attribute elements
 * will be hardened here, through the use of {@see Html::rawElement}.
 *
 * Tested hooks to insert the deferred content:
 * * ParserBeforeTidy: Injects content anytime a parser is invoked. Including for instance the searchGoButton... :(
 * * ParserAfterTidy: Same as ParserBeforeTidy
 * * SkinAfterContent: Works; content inside mw-data-after-content container, which trails the "mw-content-text" and the "printfooter" divs.
 *      Needs deferred content to be stored in parser cache. This runs into problems with fixed-head under chameleon 1.7.0+!
 * * OutputPageParserOutput: Works; adds either at the bottom of the content (right after the tidy remarks and the comment containing caching information)
 *      or at the top (at the start of the content div, direct after the "body text" comment); both options within the "mw-content-text" div container.
 *      Needs deferred content to be stored in parser cache. This runs into problems with fixed-head under chameleon 1.7.0+!
 *
 * @since 1.0
 */
class ModalBuilder {

	/**
	 * @var string $content
	 */
	private $content;

	/**
	 * @var string|false $footer
	 */
	private $footer;

	/**
	 * @var string|false $header
	 */
	private $header;

	/**
	 * @var string $id
	 */
	private $id;

	/**
	 * @var string|false $bodyClass
	 */
	private $bodyClass;

	/**
	 * @var string|false $bodyStyle
	 */
	private $bodyStyle;

	/**
	 * @var string|false $dialogClass
	 */
	private $dialogClass;

	/**
	 * @var string|false $dialogStyle
	 */
	private $dialogStyle;

	/**
	 * @var string|false $outerClass
	 */
	private $outerClass;

	/**
	 * @var string|false $outerStyle
	 */
	private $outerStyle;

	/**
	 * @var ParserOutputHelper $parserOutputHelper
	 */
	private $parserOutputHelper;

	/**
	 * @var string $trigger
	 */
	private $trigger;

	/**
	 * With this, you can wrap a generic trigger element inside a span block, that hopefully should
	 * work as a trigger for the modal
	 *
	 * @param string $element
	 * @param string $id
	 *
	 * @return string
	 */
	public static function wrapTriggerElement( $element, $id ) {
		return Html::rawElement(
			'span',
			[
				'class'       => 'modal-trigger',
				'data-toggle' => 'modal',
				'data-target' => '#' . $id,
			],
			$element
		);
	}

	/**
	 * ModalBase constructor.
	 *
	 * Takes $id, $trigger and $content and produces a modal with the html id $id, using $content as the
	 * body content of the opening modal. For trigger, you can use a generic html code and wrap it in
	 * {@see \BootstrapComponents\ModalBase::wrapTriggerElement}, or you make sure you generate
	 * a correct trigger for yourself, using the necessary attributes and especially the id, you supplied
	 * here (see {@see \BootstrapComponents\Components\Modal::generateButton} for example).
	 *
	 * Do not instantiate directly, but use {@see ApplicationFactory::getNewModalBuilder}
	 * instead.
	 *
	 * @param string             $id
	 * @param string             $trigger must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 * @param string             $content must be fully parsed html (use {@see Parser::recursiveTagParseFully})
	 * @param ParserOutputHelper $parserOutputHelper
	 *
	 *@see ApplicationFactory::getNewModalBuilder
	 * @see \BootstrapComponents\Components\Modal::generateButton
	 *
	 */

	public function __construct( $id, $trigger, $content, $parserOutputHelper ) {
		$this->id = $id;
		$this->trigger = $trigger;
		$this->content = $content;
		$this->parserOutputHelper = $parserOutputHelper;
	}

	/**
	 * Parses the modal.
	 *
	 * @return string
	 */
	public function parse() {
		$this->parserOutputHelper->injectLater(
			$this->getId(),
			$this->buildModal()
		);
		return $this->buildTrigger();
	}

	/**
	 * Lets you set the class attribute for the modal body. The body is the element holding the content.
	 *
	 * @param string|false $bodyClass
	 *
	 * @return ModalBuilder
	 */
	public function setBodyClass( $bodyClass ) {
		$this->bodyClass = $bodyClass;
		return $this;
	}

	/**
	 * Lets you set the style attribute for the modal body. The body is the element holding the content.
	 *
	 * @param string|false $bodyStyle
	 *
	 * @return ModalBuilder
	 */
	public function setBodyStyle( $bodyStyle ) {
		$this->bodyStyle = $bodyStyle;
		return $this;
	}

	/**
	 * Lets you set the class attribute for the dialog part. The dialog is the container, holding header, body, and footer.
	 * The dialog is surrounded by the outer modal. See {@see ModalBuilder::setOuterClass}.
	 *
	 * @param string|false $dialogClass
	 *
	 * @return ModalBuilder
	 */
	public function setDialogClass( $dialogClass ) {
		$this->dialogClass = $dialogClass;
		return $this;
	}

	/**
	 * Lets you set the style attribute for the dialog part. The dialog is the container, holding header, body, and footer.
	 * The dialog is surrounded by the outer modal. See {@see ModalBuilder::setOuterClass}.
	 *
	 * @param string|false $dialogStyle
	 *
	 * @return ModalBuilder
	 */
	public function setDialogStyle( $dialogStyle ) {
		$this->dialogStyle = $dialogStyle;
		return $this;
	}

	/**
	 * Sets the content for the footer section.
	 *
	 * @param string|false $footer must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return ModalBuilder
	 */
	public function setFooter( $footer ) {
		$this->footer = $footer;
		return $this;
	}

	/**
	 * Sets the content for the header section.
	 *
	 * @param string|false $header must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return ModalBuilder
	 */
	public function setHeader( $header ) {
		$this->header = $header;
		return $this;
	}

	/**
	 * Lets you set the class attribute for the outermost modal container.
	 *
	 * @param string|false $outerClass
	 *
	 * @return ModalBuilder
	 */
	public function setOuterClass( $outerClass ) {
		$this->outerClass = $outerClass;
		return $this;
	}

	/**
	 * Lets you set the style attribute for the outermost modal container.
	 *
	 * @param string|false $outerStyle
	 *
	 * @return ModalBuilder
	 */
	public function setOuterStyle( $outerStyle ) {
		$this->outerStyle = $outerStyle;
		return $this;
	}

	/**
	 * From all the data passed by caller, this builds the dialog part (the one inside the modal holding the actual content).
	 *
	 * @return string
	 */
	protected function buildDialog() {
		return Html::rawElement(
			'div',
			[
				'class' => $this->compileClass(
					'modal-dialog',
					$this->getDialogClass()
				),
				'style' => $this->getDialogStyle(),
			],
			Html::rawElement(
				'div',
				[ 'class' => 'modal-content' ],
				$this->generateHeader(
					$this->getHeader()
				)
				. $this->generateBody(
					$this->getContent()
				)
				. $this->generateFooter(
					$this->getFooter()
				)
			)
		);
	}

	/**
	 * From all the data passed by caller, this builds the dialog part (the one that pops up when engaging the trigger).
	 *
	 * @return string
	 */
	protected function buildModal() {
		return Html::rawElement(
			'div',
			[
				'class'       => $this->compileClass(
					'modal fade',
					$this->getOuterClass()
				),
				'style'       => $this->getOuterStyle(),
				'role'        => 'dialog',
				'id'          => $this->getId(),
				'aria-hidden' => 'true',
			],
			$this->buildDialog()
		) . "\n";
	}

	/**
	 * Performs the necessary steps to convert the string passed by caller into a working trigger for the modal.
	 *
	 * @return string
	 */
	protected function buildTrigger() {
		$trigger = $this->getTrigger();
		if ( preg_match( '/data-toggle[^"]+"modal/', $trigger )
			&& preg_match( '/data-target[^"]+"#' . $this->getId() . '"/', $trigger )
			&& preg_match( '/class[^"]+"[^"]*modal-trigger' . '/', $trigger )
		) {
			return $trigger;
		}
		return self::wrapTriggerElement( $trigger, $this->getId() );
	}

	/**
	 * Used to merge different class attributes.
	 *
	 * @param string       $baseClass
	 * @param string|false $additionalClass
	 *
	 * @return string
	 */
	protected function compileClass( $baseClass, $additionalClass ) {
		if ( trim( $additionalClass ) ) {
			return $baseClass . ' ' . trim( $additionalClass );
		}
		return $baseClass;
	}

	/**
	 * @return string|false
	 */
	protected function getBodyClass() {
		return $this->bodyClass;
	}

	/**
	 * @return string|false
	 */
	protected function getBodyStyle() {
		return $this->bodyStyle;
	}

	/**
	 * Build the modal, with all sections, requested content and necessary control elements.
	 *
	 * @return string
	 */
	protected function getContent() {
		return $this->content;
	}

	/**
	 * @return string|false
	 */
	protected function getDialogClass() {
		return $this->dialogClass;
	}

	/**
	 * @return string|false
	 */
	protected function getDialogStyle() {
		return $this->dialogStyle;
	}

	/**
	 * @return string|false
	 */
	protected function getFooter() {
		return $this->footer;
	}

	/**
	 * @return string|false
	 */
	protected function getHeader() {
		return $this->header;
	}

	/**
	 * @return string
	 */
	protected function getId() {
		return $this->id;
	}

	/**
	 * @return string|false
	 */
	protected function getOuterClass() {
		return $this->outerClass;
	}

	/**
	 * @return string|false
	 */
	protected function getOuterStyle() {
		return $this->outerStyle;
	}

	/**
	 * Returns the supplied trigger. Wraps it with {@see ModalBuilder::wrapTriggerElement} if certain attributes are not detected.
	 *
	 * @return string
	 */
	protected function getTrigger() {
		return $this->trigger;
	}

	/**
	 * Generates the body section.
	 *
	 * @param string $content must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return string
	 */
	private function generateBody( $content ) {
		return Html::rawElement(
				'div',
				[
					'class' => $this->compileClass( 'modal-body', $this->getBodyClass() ),
					'style' => $this->getBodyStyle(),
				],
				$content
			);
	}

	/**
	 * Generates the footer section.
	 *
	 * @param string|false $footer must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return string
	 */
	private function generateFooter( $footer = '' ) {
		if ( empty( $footer ) ) {
			$footer = '';
		}
		$close = wfMessage( 'bootstrap-components-close-element' )->inContentLanguage()->text();
		return Html::rawElement(
				'div',
				[ 'class' => 'modal-footer' ],
				$footer . Html::rawElement(
					'button',
					[
						'type'         => 'button',
						'class'        => 'btn btn-default',
						'data-dismiss' => 'modal',
						'aria-label'   => $close,
					],
					$close
				)
			);
	}

	/**
	 * Generates the header section together with the dismiss X and the heading, if provided.
	 *
	 * @param string|false $header must be safe raw html (best run through {@see Parser::recursiveTagParse})
	 *
	 * @return string
	 */
	private function generateHeader( $header = '' ) {
		if ( empty( $header ) ) {
			$header = '';
		} else {
			$header = Html::rawElement(
				'span',
				[ 'class' => 'modal-title' ],
				$header
			);
		}
		$button = Html::rawElement(
			'button',
			[
				'type'         => 'button',
				'class'        => 'close',
				'data-dismiss' => 'modal',
				'aria-label'   => wfMessage( 'bootstrap-components-close-element' )->inContentLanguage()->text(),
			],
			Html::rawElement(
				'span',
				[ 'aria-hidden' => 'true' ],
				'&times;'
			)
		);
		return Html::rawElement(
				'div',
				[ 'class' => 'modal-header' ],
				$button 	. $header
			);
	}
}
