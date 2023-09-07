<?php
/**
 * Contains the class generating the trigger string for the class {@see ImageModal}.
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

use Config;
use ConfigException;
use Exception;
use \Linker;
use \Html;
use \MediaWiki\MediaWikiServices;
use MediaWiki\User\UserOptionsLookup;
use \RequestContext;
use \Title;

/**
 * Class ImageModal
 *
 * @since 1.0
 */
class ImageModalTrigger {
	/**
	 * @var \File $file
	 */
	private $file;

	/**
	 * @var string $id
	 */
	private $id;

	/**
	 * ImageModal constructor.
	 *
	 * @param string $id
	 * @param \File  $file
	 */
	public function __construct( $id, $file ) {
		$this->id = $id;
		$this->file = $file;
	}

	/**
	 * @param array $sanitizedFrameParams
	 * @param array $handlerParams
	 *
	 * @return false|string
	 *
	 * @throws Exception       cascading {@see ImageModalTrigger::wrapAndFinalize}
	 * @throws ConfigException cascading {@see ImageModalTrigger::generateTriggerCreateThumb}
	 */
	public function generate( array $sanitizedFrameParams, array $handlerParams ) {
		/** @var \MediaTransformOutput $thumb */
		list( $thumb, $thumbHandlerParams ) = $this->createThumb(
			$this->getFile(),
			$sanitizedFrameParams,
			$handlerParams
		);

		if ( !$thumb ) {
			// We could deal with an invalid thumb, but then we would also need to signal in invalid modal.
			// Better let Linker.php take care
			wfDebugLog( 'BootstrapComponents', 'Image modal encountered an invalid thumbnail. Relegating back.' );
			return false;
		}
		$triggerOptions = $this->calculateHtmlOptions(
			$this->getFile(),
			$thumb,
			$sanitizedFrameParams,
			$thumbHandlerParams
		);
		$publicationString = $thumb->toHtml( $triggerOptions );
		return $this->wrapAndFinalize(
			$publicationString,
			$sanitizedFrameParams,
			$thumb->getWidth()
		);
	}

	/**
	 * @inheritdoc
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param \File                 $file
	 * @param \MediaTransformOutput $thumb
	 * @param array                 $sanitizedFrameParams
	 * @param array                 $thumbHandlerParams
	 *
	 * @return array
	 */
	protected function calculateHtmlOptions( $file, $thumb, $sanitizedFrameParams, $thumbHandlerParams ) {
		if ( $sanitizedFrameParams['thumbnail'] && (!isset( $sanitizedFrameParams['manualthumb'] ) && !$sanitizedFrameParams['framed']) ) {
			Linker::processResponsiveImages( $file, $thumb, $thumbHandlerParams );
		}
		$options = [
			'alt'       => $sanitizedFrameParams['alt'],
			'img-class' => $sanitizedFrameParams['class'],  // removed: . ' img-fluid'; keeping it in, causes line breaks around the trigger.
			'title'     => $sanitizedFrameParams['title'],
			'valign'    => $sanitizedFrameParams['valign'],
		];
		if ( $sanitizedFrameParams['thumbnail'] || isset( $sanitizedFrameParams['manualthumb'] ) || $sanitizedFrameParams['framed'] ) {
			$options['img-class'] .= ' thumbimage';
		} elseif ( $sanitizedFrameParams['border'] ) {
			$options['img-class'] .= ' thumbborder';
		}
		$options['img-class'] = trim( $options['img-class'] );

		// in Linker.php, options also run through {@see \Linker::getImageLinkMTOParams} to calculate the link value.
		// Since we abort at the beginning, if any link related frameParam is set, we can skip this.
		// also, obviously, we don't want to have ANY link around the img present.
		return $options;
	}

	/**
	 * @param \File $file
	 * @param array $sanitizedFrameParams
	 * @param array $handlerParams
	 *
	 * @return array [ \MediaTransformOutput|false, handlerParams ]
	 *@throws ConfigException cascading {@see \BootstrapComponents\ImageModal::generateTriggerReevaluateImageDimensions}
	 *
	 */
	protected function createThumb( $file, $sanitizedFrameParams, $handlerParams ) {
		$transform = !isset( $sanitizedFrameParams['manualthumb'] ) && !$sanitizedFrameParams['framed'];
		$thumbFile = $file;
		$thumbHandlerParams = $this->reevaluateImageDimensions( $file, $sanitizedFrameParams, $handlerParams );

		if ( isset( $sanitizedFrameParams['manualthumb'] ) ) {
			$thumbFile = $this->getFileFromTitle( $sanitizedFrameParams['manualthumb'] );
		}

		if ( !$thumbFile
			|| (!$sanitizedFrameParams['thumbnail'] && !$sanitizedFrameParams['framed'] && !isset( $thumbHandlerParams['width'] ))
		) {
			return [ false, $thumbHandlerParams ];
		}

		if ( $transform ) {
			return [ $thumbFile->transform( $thumbHandlerParams ), $thumbHandlerParams ];
		} else {
			return [ $thumbFile->getUnscaledThumb( $thumbHandlerParams ), $thumbHandlerParams ];
		}
	}

	/**
	 * @return \File
	 */
	protected function getFile() {
		return $this->file;
	}

	/**
	 * This is mostly taken from {@see \Linker::makeImageLink}, rest originates from {@see \Linker::makeThumbLink2}. Extracts are heavily
	 * squashed and condensed
	 *
	 * @param \File $file
	 * @param array $sanitizedFrameParams
	 * @param array $handlerParams
	 *
	 * @return array thumbnail handler params
	 *@throws ConfigException cascading {@see \BootstrapComponents\ImageModal::generateTriggerCalculateImageWidth}
	 *
	 */
	protected function reevaluateImageDimensions( $file, $sanitizedFrameParams, $handlerParams ) {
		if ( !isset( $handlerParams['width'] ) ) {
			$handlerParams = $this->calculateImageWidth( $file, $sanitizedFrameParams, $handlerParams );
		}
		if ( $this->amIThumbnailRelated( $sanitizedFrameParams ) ) {
			if ( empty( $handlerParams['width'] ) && !$sanitizedFrameParams['frameless'] ) {
				// Reduce width for upright images when parameter 'upright' is used
				$handlerParams['width'] = isset( $sanitizedFrameParams['upright'] ) ? 130 : 180;
			}
			$handlerParams = $this->limitSizeToSourceOnBitmapImages( $file, $sanitizedFrameParams, $handlerParams );
		}

		return $handlerParams;
	}

	/**
	 * Envelops the publication trigger img-tag.
	 *
	 * @param string $publicationString
	 * @param array  $sanitizedFrameParams
	 * @param int    $publicationWidth
	 *
	 * @throws \Exception cascading {@see ImageModalTrigger::buildThumbnailTrigger}
	 *
	 * @return string
	 */
	protected function wrapAndFinalize( $publicationString, $sanitizedFrameParams, $publicationWidth ) {
		if ( $sanitizedFrameParams['thumbnail'] || isset( $sanitizedFrameParams['manualthumb'] ) || $sanitizedFrameParams['framed'] ) {
			$ret = $this->buildThumbnailTrigger( $publicationString, $sanitizedFrameParams, $publicationWidth );
		} elseif ( strlen( $sanitizedFrameParams['align'] ) ) {
			$class = $sanitizedFrameParams['align'] == 'center' ? 'center' : 'float' . $sanitizedFrameParams['align'];
			$ret = Html::rawElement(
				'div',
				[ 'class' => $class, ],
				ModalBuilder::wrapTriggerElement( $publicationString, $this->getId() )
			);
		} else {
			$ret = ModalBuilder::wrapTriggerElement( $publicationString, $this->getId() );
		}

		return str_replace( "\n", ' ', $ret );
	}

	/**
	 * @param $sanitizedFrameParams
	 *
	 * @return bool
	 */
	private function amIThumbnailRelated( $sanitizedFrameParams ) {
		return $sanitizedFrameParams['thumbnail']
			|| isset( $sanitizedFrameParams['manualthumb'] )
			|| $sanitizedFrameParams['framed']
			|| $sanitizedFrameParams['frameless'];
	}

	/**
	 * Envelops a publication trigger img-tag that is a thumbnail.
	 *
	 * @param string $publicationString
	 * @param array  $sanitizedFrameParams
	 * @param int    $publicationWidth
	 *
	 * @throws \Exception cascading {@see \RequestContext::getMain}
	 *
	 * @return string
	 */
	private function buildThumbnailTrigger( $publicationString, $sanitizedFrameParams, $publicationWidth ) {
		if ( empty( $sanitizedFrameParams['align'] ) ) {
			$sanitizedFrameParams['align'] = RequestContext::getMain()->getLanguage()->alignEnd();
		}
		$zoomIcon = $this->buildZoomIcon( $sanitizedFrameParams );
		$outerWidth = $publicationWidth + 2;
		$class = 'thumb t' . ($sanitizedFrameParams['align'] == 'center' ? 'none' : $sanitizedFrameParams['align']);

		return Html::rawElement(
			'div',
			[
				'class' => $class,
			],
			ModalBuilder::wrapTriggerElement(
				Html::rawElement(
					'div',
					[
						'class' => 'thumbinner',
						'style' => 'width:' . $outerWidth . 'px;',
					],
					$publicationString . '  ' . Html::rawElement(
						'div',
						[ 'class' => 'thumbcaption' ],
						$zoomIcon . $sanitizedFrameParams['caption']
					)
				),
				$this->getId()
			)
		);
	}

	/**
	 * @param array $sanitizedFrameParams
	 *
	 * @return string
	 */
	private function buildZoomIcon( $sanitizedFrameParams ) {
		if ( $sanitizedFrameParams['framed'] ) {
			return '';
		}
		return Html::rawElement(
			'div',
			[
				'class' => 'magnify',
			],
			Html::rawElement(
				'a',
				[
					'class' => 'internal',
					'title' => wfMessage( 'thumbnail-more' )->text(),
				],
				""
			)
		);
	}

	/**
	 * Calculates a with from File, $sanitizedFrameParams, and $handlerParams
	 *
	 * @param \File $file
	 * @param array $sanitizedFrameParams
	 * @param array $handlerParams
	 *
	 * @return array thumbnail handler params
	 *@throws ConfigException cascading {@see ImageModal::getInitialWidthSuggestion} or {@see ImageModal::getPreferredWidth}
	 *
	 */
	private function calculateImageWidth( $file, $sanitizedFrameParams, $handlerParams ) {
		$globalConfig = MediaWikiServices::getInstance()->getMainConfig();

		$handlerParams['width'] = $this->getInitialWidthSuggestion( $globalConfig, $file, $handlerParams );

		if ( $this->amIThumbnailRelated( $sanitizedFrameParams ) || !$handlerParams['width'] ) {
			// Reduce width for upright images when parameter 'upright' is used
			if ( isset( $sanitizedFrameParams['upright'] ) && $sanitizedFrameParams['upright'] == 0 ) {
				$sanitizedFrameParams['upright'] = $globalConfig->get( 'ThumbUpright' );
			}

			// note: the upright calculation above resided originally inside the two blocks in the following method
			$prefWidth = $this->getPreferredWidth( $globalConfig, $sanitizedFrameParams );

			// Use width which is smaller: real image width or user preference width
			// Unless image is scalable vector.
			if ( !isset( $handlerParams['height'] ) &&
				($handlerParams['width'] <= 0 || $prefWidth < $handlerParams['width'] || $file->isVectorized())
			) {
				$handlerParams['width'] = $prefWidth;
			}
		}
		return $handlerParams;
	}

	/**
	 * @param string $fileTitle
	 *
	 * @return bool|\File
	 */
	private function getFileFromTitle( string $fileTitle ) {
		$manual_title = Title::makeTitleSafe( NS_FILE, $fileTitle );
		if ( $manual_title ) {
			return MediaWikiServices::getInstance()->getRepoGroup()->findFile( $manual_title );
		}
		return false;
	}

	/**
	 * @param Config $globalConfig
	 * @param \File   $file
	 * @param array   $handlerParams
	 *
	 * @return mixed
	 * @throws ConfigException cascading {@see \Config::get}
	 *
	 */
	private function getInitialWidthSuggestion( $globalConfig, $file, $handlerParams ) {
		if ( isset( $handlerParams['height'] ) && $file->isVectorized() ) {
			// If its a vector image, and user only specifies height
			// we don't want it to be limited by its "normal" width.
			return $globalConfig->get( 'SVGMaxSize' );
		} else {
			return $file->getWidth( $handlerParams['page'] );
		}
	}

	/**
	 * @param Config $globalConfig
	 * @param array   $sanitizedFrameParams
	 *
	 * @return float
	 *
	 * @throws ConfigException cascading {@see \Config::get}
	 */
	private function getPreferredWidth( Config $globalConfig, array $sanitizedFrameParams ): float
	{
		$thumbLimits = $globalConfig->get( 'ThumbLimits' );
		$widthOption = $this->getWidthOptionForThumbLimits( $thumbLimits );

		// For caching health: If width scaled down due to upright
		// parameter, round to full __0 pixel to avoid the creation of a
		// lot of odd thumbs.
		return isset( $sanitizedFrameParams['upright'] )
			? round( $thumbLimits[$widthOption] * $sanitizedFrameParams['upright'], -1 )
			: $thumbLimits[$widthOption];
	}

	/**
	 * @param array $thumbLimits
	 *
	 * @return int|string
	 */
	private function getWidthOptionForThumbLimits( array $thumbLimits ) {

		$widthOption = MediaWikiServices::getInstance()->getUserOptionsLookup()->getDefaultOption( 'thumbsize' );

		// we have a problem here: the original \Linker::makeImageLink does get a value for $widthOption,
		// for instance in parser tests. unfortunately, this value is not passed through the hook.
		// so there are instances, where $thumbLimits[$widthOption] is not defined.
		// solution: we cheat and take the first one
		if ( $widthOption !== null && isset( $thumbLimits[$widthOption] ) ) {
			return $widthOption;
		}
		$availableOptions = array_keys( $thumbLimits );
		return reset( $availableOptions );
	}

	/**
	 * Do not present an image bigger than the source, for bitmap-style images
	 *
	 * @param \File $file
	 * @param array $sanitizedFrameParams
	 * @param array $handlerParams
	 *
	 * @return array
	 */
	private function limitSizeToSourceOnBitmapImages( $file, $sanitizedFrameParams, $handlerParams ) {
		if ( $sanitizedFrameParams['frameless']
			|| (!isset( $sanitizedFrameParams['manualthumb'] ) && !$sanitizedFrameParams['framed'])
		) {
			$srcWidth = $file->getWidth( $handlerParams['page'] );
			if ( $srcWidth && !$file->mustRender() && $handlerParams['width'] > $srcWidth ) {
				$handlerParams['width'] = $srcWidth;
			}
		}
		return $handlerParams;
	}
}
