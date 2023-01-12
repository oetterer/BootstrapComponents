<?php
/**
 * Contains the class for replacing image normal image display with a modal.
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

use \Linker;
use \Html;
use \MediaWiki\MediaWikiServices;
use \RequestContext;
use \Title;

/**
 * Class ImageModal
 *
 * @since 1.0
 */
class ImageModal implements NestableInterface {

	const CSS_CLASS_PREVENTING_MODAL = 'no-modal';

	/**
	 * The components listed here prevent the generation of an image modal.
	 * @var array
	 */
	const PARENTS_PREVENTING_MODAL = [ 'button', 'collapse ', 'image_modal', 'modal', 'popover', 'tooltip' ];

	/**
	 * @var \DummyLinker $dummyLinker
	 */
	private $dummyLinker;

	/**
	 * @var \File $file
	 */
	private $file;

	/**
	 * @var string $id
	 */
	private $id;

	/**
	 * @var NestingController $nestingController
	 */
	private $nestingController;

	/**
	 * @var NestableInterface|false $parentComponent
	 */
	private $parentComponent;

	/**
	 * @var ParserOutputHelper $parserOutputHelper
	 */
	private $parserOutputHelper;

	/**
	 * @var bool $disableSourceLink
	 */
	private $disableSourceLink;

	/**
	 * @var Title $title
	 */
	private $title;

	/**
	 * ImageModal constructor.
	 *
	 * @param \DummyLinker       $dummyLinker
	 * @param \Title             $title
	 * @param \File              $file
	 * @param NestingController  $nestingController
	 * @param ParserOutputHelper $parserOutputHelper DI for unit testing
	 *
	 * @throws \MWException cascading {@see \BootstrapComponents\ApplicationFactory} methods
	 */
	public function __construct( $dummyLinker, $title, $file, $nestingController = null, $parserOutputHelper = null ) {
		$this->file = $file;
		$this->dummyLinker = $dummyLinker;
		$this->title = $title;

		$this->nestingController = is_null( $nestingController )
			? ApplicationFactory::getInstance()->getNestingController()
			: $nestingController;
		$this->parserOutputHelper = is_null( $parserOutputHelper )
			? ApplicationFactory::getInstance()->getParserOutputHelper()
			: $parserOutputHelper ;

		$this->parentComponent = $this->getNestingController()->getCurrentElement();
		$this->id = $this->getNestingController()->generateUniqueId(
			$this->getComponentName()
		);
		$this->disableSourceLink = false;
	}

	/**
	 * @inheritdoc
	 */
	public function getComponentName() {
		return "image_modal";
	}

	/**
	 * @inheritdoc
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param array       $frameParams   Associative array of parameters external to the media handler.
	 *                                   Boolean parameters are indicated by presence or absence, the value is arbitrary and
	 *                                   will often be false.
	 *                                   thumbnail       If present, downscale and frame
	 *                                   manualthumb     Image name to use as a thumbnail, instead of automatic scaling
	 *                                   framed          Shows image in original size in a frame
	 *                                   frameless       Downscale but don't frame
	 *                                   upright         If present, tweak default sizes for portrait orientation
	 *                                   upright_factor  Fudge factor for "upright" tweak (default 0.75)
	 *                                   border          If present, show a border around the image
	 *                                   align           Horizontal alignment (left, right, center, none)
	 *                                   valign          Vertical alignment (baseline, sub, super, top, text-top, middle,
	 *                                                   bottom, text-bottom)
	 *                                   alt             Alternate text for image (i.e. alt attribute). Plain text.
	 *                                   class           HTML for image classes. Plain text.
	 *                                   caption         HTML for image caption.
	 *                                   link-url        URL to link to
	 *                                   link-title      Title object to link to
	 *                                   link-target     Value for the target attribute, only with link-url
	 *                                   no-link         Boolean, suppress description link
	 * @param array       $handlerParams Associative array of media handler parameters, to be passed
	 *                                   to transform(). Typical keys are "width" and "page".
	 * @param string|bool $time          Timestamp of the file, set as false for current
	 * @param string      $res           Final HTML output, used if this returns false
	 *
	 * @throws \MWException     cascading {@see \BootstrapComponents\NestingController::open}
	 * @throws \ConfigException cascading {@see \BootstrapComponents\ImageModal::generateTrigger}
	 *
	 * @return bool
	 */
	public function parse( &$frameParams, &$handlerParams, &$time, &$res ) {
		if ( !$this->assertResponsibility( $this->getFile(), $frameParams ) ) {
			wfDebugLog( 'BootstrapComponents', 'Image modal relegating image rendering back to Linker.php.' );
			return true;
		}

		// it's on us, let's do some modal-ing
		$this->augmentParserOutput();
		$this->getNestingController()->open( $this );

		$sanitizedFrameParams = $this->sanitizeFrameParams( $frameParams );
		$handlerParams['page'] = isset( $handlerParams['page'] ) ? $handlerParams['page'] : false;

		$res = $this->turnParamsIntoModal( $sanitizedFrameParams, $handlerParams );

		$this->getNestingController()->close(
			$this->getId()
		);

		if ( $res === '' ) {
			// ImageModal::turnParamsIntoModal returns the empty string, when something went wrong
			return true;
		}
		return false;
	}

	/**
	 * After this, all bool params ( 'thumbnail', 'framed', 'frameless', 'border' ) are true, if they were present before, false otherwise and all
	 * string params are set (to the original value or the empty string).
	 *
	 * This method is public, because it is used in {@see \BootstrapComponents\Tests\ImageModalTest::doTestCompareTriggerWithOriginalThumb}
	 *
	 * @param array $frameParams
	 *
	 * @return array
	 */
	public function sanitizeFrameParams( $frameParams ) {
		foreach ( [ 'thumbnail', 'framed', 'frameless', 'border' ] as $boolField ) {
			$frameParams[$boolField] = isset( $frameParams[$boolField] );
		}
		foreach ( [ 'align', 'alt', 'caption', 'class', 'title', 'valign' ] as $stringField ) {
			$frameParams[$stringField] = !empty( $frameParams[$stringField] ) ? $frameParams[$stringField] : false;
		}
		$frameParams['caption'] = $this->preventModalInception( $frameParams['caption'] );
		$frameParams['title'] = $this->preventModalInception( $frameParams['title'] );
		return $frameParams;
	}

	/**
	 * Disables the source link in modal content.
	 */
	public function disableSourceLink() {
		$this->disableSourceLink = true;
	}

	/**
	 * Runs various tests, to see, if we delegate processing back to {@see \Linker::makeImageLink}
	 * After this, we can assume:
	 * * file is a {@see \File} and exists
	 * * there is no link param set (link-url, link-title, link-target, no-link)
	 * * file allows inline display (ref {@see \File::allowInlineDisplay})
	 * * we are not inside an image modal or an otherwise compromising component  (thanks to {@see ImageModal::getNestingController})
	 * * no magic word suppressing image modals is on the page
	 * * image does not have the "no-modal" class {@see ImageModal::CSS_CLASS_PREVENTING_MODAL}
	 *
	 * @param \File $file
	 * @param array $frameParams
	 *
	 * @return bool true, if all assertions hold, false if one fails (see above)
	 */
	protected function assertResponsibility( $file, $frameParams ) {
		if ( !$this->assertImageTagValid( $file, $frameParams ) ) {
			return false;
		}
		return $this->assertImageModalNotSuppressed( $frameParams );
	}

	/**
	 * @param \File $file
	 * @param array $sanitizedFrameParams
	 * @param array $handlerParams
	 *
	 * @return array bool|string bool (large image yes or no)
	 */
	protected function generateContent( $file, $sanitizedFrameParams, $handlerParams ) {

		/** @var \MediaTransformOutput $img $img */
		$img = $file->getUnscaledThumb(
			[ 'page' => $handlerParams['page'] ]
		);
		if ( !$img ) {
			return [ false, false ];
		}
		return [
			$this->buildContentImageString( $img, $sanitizedFrameParams ),
			$img->getWidth() > 600
		];
	}

	/**
	 * @return \DummyLinker
	 */
	/** @scrutinizer ignore-unused */
	protected function getDummyLinker() {
		return $this->dummyLinker;
	}

	/**
	 * @return \File
	 */
	protected function getFile() {
		return $this->file;
	}

	/**
	 * @return NestingController
	 */
	protected function getNestingController() {
		return $this->nestingController;
	}

	/**
	 * @return null|NestableInterface
	 */
	protected function getParentComponent() {
		return $this->parentComponent;
	}

	/**
	 * @return ParserOutputHelper
	 */
	protected function getParserOutputHelper() {
		return $this->parserOutputHelper;
	}

	/**
	 * @return Title
	 */
	protected function getTitle() {
		return $this->title;
	}

	/**
	 * @param $sanitizedFrameParams
	 * @param $handlerParams
	 *
	 * @throws \ConfigException
	 *
	 * @return string   rendered modal on success, empty string on failure.
	 */
	protected function turnParamsIntoModal( $sanitizedFrameParams, $handlerParams ) {
		$trigger = new ImageModalTrigger(
			$this->getId(),
			$this->getFile()
		);

		$triggerString = $trigger->generate( $sanitizedFrameParams, $handlerParams );

		if ( $triggerString === false ) {
			// something wrong with the trigger. Relegating back
			return '';
		}

		list ( $content, $largeDialog ) = $this->generateContent(
			$this->getFile(),
			$sanitizedFrameParams,
			$handlerParams
		);

		if ( $content === false ) {
			// could not create content image. Relegating back
			return '';
		}

		$modal = ApplicationFactory::getInstance()->getNewModalBuilder(
			$this->getId(),
			$triggerString,
			$content,
			$this->getParserOutputHelper()
		);
		$modal->setHeader(
			$this->getTitle()->getBaseText()
		);

		if ( !$this->disableSourceLink ) {
			$modal->setFooter(
				$this->generateButtonToSource(
					$this->getTitle(),
					$handlerParams
				)
			);
		};

		if ( $largeDialog ) {
			$modal->setDialogClass( 'modal-lg' );
		}

		return $modal->parse();
	}

	/**
	 * @param \File $file
	 * @param array $frameParams
	 *
	 * @return bool
	 */
	private function assertImageTagValid( $file, $frameParams ) {
		if ( !$file || !$file->exists() ) {
			return false;
		}
		if ( isset( $frameParams['link-url'] ) || isset( $frameParams['link-title'] )
			|| isset( $frameParams['link-target'] ) || isset( $frameParams['no-link'] )
		) {
			return false;
		}
		if ( !$file->allowInlineDisplay() ) {
			// let Linker.php handle these cases as well
			return false;
		}
		return true;
	}

	/**
	 * @param array $frameParams
	 *
	 * @return bool
	 */
	private function assertImageModalNotSuppressed( array $frameParams ): bool
	{
		if ( $this->getParentComponent() && in_array( $this->getParentComponent()->getComponentName(), self::PARENTS_PREVENTING_MODAL ) ) {
			return false;
		}
		if ( isset( $frameParams['class'] ) && in_array( self::CSS_CLASS_PREVENTING_MODAL, explode( ' ', $frameParams['class'] ) ) ) {
			return false;
		}
		/** @see ParserOutputHelper::areImageModalsSuppressed as to why we need to use the global parser! */
		//$parser = $GLOBALS['wgParser'];   //  Use of $wgParser was deprecated in MediaWiki 1.32.
		$parser = MediaWikiServices::getInstance()->getParser();
		// the is_null test has to be added because otherwise some unit tests will fail
		return is_null( $parser->getOutput() ) || !$parser->getOutput()->getExtensionData( 'bsc_no_image_modal' );
	}

	/**
	 * Performs all the mandatory actions on the parser output for the component class
	 *
	 * @throws \MWException cascading {@see \BootstrapComponents\ApplicationFactory::getComponentLibrary}
	 */
	private function augmentParserOutput() {
		$skin = $this->getParserOutputHelper()->getNameOfActiveSkin();
		$this->getParserOutputHelper()->loadBootstrapModules();
		$this->getParserOutputHelper()->addModules(
			ApplicationFactory::getInstance()->getComponentLibrary()->getModulesFor( 'modal', $skin )
		);
	}

	/**
	 * @param \MediaTransformOutput $img
	 * @param array                 $sanitizedFrameParams
	 *
	 * @return string
	 */
	private function buildContentImageString( $img, $sanitizedFrameParams ) {
		$imgParams = [
			'alt'       => $sanitizedFrameParams['alt'],
			'title'     => $sanitizedFrameParams['title'],
			'img-class' => trim( $sanitizedFrameParams['class'] . ' img-fluid' ),
		];
		$imgString = $img->toHtml( $imgParams );
		if ( $sanitizedFrameParams['caption'] ) {
			$imgString .= ' ' . Html::rawElement(
					'div',
					[ 'class' => 'modal-caption' ],
					$this->sanitizeCaption( $sanitizedFrameParams['caption'] )
				);
		}
		return $imgString;
	}

	/**
	 * @param Title $title
	 * @param array $handlerParams
	 *
	 * @return string
	 */
	private function generateButtonToSource( $title, $handlerParams ) {
		$url = $title->getLocalURL();
		if ( isset( $handlerParams['page'] ) ) {
			$url = wfAppendQuery( $url, [ 'page' => $handlerParams['page'] ] );
		}
		return Html::rawElement(
			'a',
			[
				'class' => 'btn btn-primary',
				'role'  => 'button',
				'href'  => $url,
			],
			wfMessage( 'bootstrap-components-image-modal-source-button' )->inContentLanguage()->text()
		);
	}

	/**
	 * We don't want a modal inside a modal. Unfortunately, the caption (and title) are parsed, before the modal is generated. So instead of
	 * building the modal from the outside, it is build from the inside. This method tries to detect this construct and removes any modal from
	 * the supplied text and replaces it with the image tag found inside the modal caption content.
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	private function preventModalInception( $text ) {
		if ( preg_match(
			'~div class="modal-dialog.+div class="modal-content.+div class="modal-body.+'
			. '(<img[^>]*/>).+ class="modal-footer.+~Ds', $text, $matches ) ) {
			$text = $matches[1];
		}
		return $text;
	}

	/**
	 * @param string $caption
	 *
	 * @return string
	 */
	private function sanitizeCaption( $caption ) {
		return preg_replace( '/([^\n])\n([^\n])/m', '\1\2', $caption );
	}
}
