<?php

namespace SMT;

use OutputPage;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class OutputPageMetaTagsModifier {

	/**
	 * @var PropertyValueContentFinder
	 */
	private $propertyValueContentFinder;

	/**
	 * @var array
	 */
	private $metaTagsContentPropertySelector = array();

	/**
	 * @var boolean
	 */
	private $usedOpenGraphProtocolMarkup = false;

	/**
	 * @since 1.0
	 *
	 * @param PropertyValueContentFinder $propertyValueContentFinder
	 */
	public function __construct( PropertyValueContentFinder $propertyValueContentFinder ) {
		$this->propertyValueContentFinder = $propertyValueContentFinder;
	}

	/**
	 * @since 1.0
	 *
	 * @param array $metaTagsContentPropertySelector
	 */
	public function setMetaTagsContentPropertySelector( array $metaTagsContentPropertySelector ) {
		$this->metaTagsContentPropertySelector = $metaTagsContentPropertySelector;
	}

	/**
	 * @since 1.0
	 *
	 * @param OutputPage &$outputPage
	 */
	public function modifyOutputPage( OutputPage &$outputPage ) {
		if ( $this->canModifyOutputPage( $outputPage->getTitle() ) ) {
			$this->addMetaTagsToOutputPage( $outputPage );
		}
	}

	private function canModifyOutputPage( $title ) {

		if ( $this->metaTagsContentPropertySelector === array() ) {
			return false;
		}

		if ( $title === null || $title->isSpecialPage() ) {
			return false;
		}

		return true;
	}

	private function addMetaTagsToOutputPage( OutputPage $outputPage ) {

		foreach ( $this->metaTagsContentPropertySelector as $tag => $propertySelector ) {

			if ( $propertySelector === '' ) {
				continue;
			}

			$content = $this->propertyValueContentFinder->findContentForProperties(
				explode( ',', $propertySelector )
			);

			$this->addTagContentToOutputPage(
				strtolower( htmlspecialchars( $tag ) ),
				$content,
				$outputPage
			);
		}
	}

	private function addTagContentToOutputPage( $tag, $content, $outputPage ) {

		if ( $content === '' ) {
			return;
		}

		// If a tag contains a `:` such as `og:title` it is expected to be a
		// OpenGraph protocol tag
		if ( strpos( $tag, ':' ) !== false ) {

			$content = $this->formatContentToIncludeOpenGraphProperty(
				$tag,
				$content
			);

			$outputPage->addHeadItem( "meta:property:$tag", $content );

			return;
		}

		$outputPage->addMeta( $tag, $content );
	}

	private function formatContentToIncludeOpenGraphProperty( $tag, $content ) {

		$comment = '';

		if ( !$this->usedOpenGraphProtocolMarkup ) {
			$comment .= '<!-- Open Graph protocol markup -->' . "\n";
			$this->usedOpenGraphProtocolMarkup = true;
		}

		return $comment . \Html::element( 'meta', array(
			'property' => $tag,
			'content'  => $content
		) );
	}

}
