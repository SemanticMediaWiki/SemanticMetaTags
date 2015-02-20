<?php

namespace SMT;

use OutputPage;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class OutputPageTagFormatter {

	/**
	 * @var OutputPage
	 */
	private $outputPage;

	/**
	 * @var array
	 */
	private $metaTagsBlacklist = array();

	/**
	 * @var boolean
	 */
	private $usedOpenGraphProtocolMarkup = false;

	/**
	 * @since 1.0
	 *
	 * @param OutputPage $outputPage
	 */
	public function __construct( OutputPage $outputPage ) {
		$this->outputPage = $outputPage;
	}

	/**
	 * @since 1.0
	 *
	 * @param array $metaTagsBlacklist
	 */
	public function setMetaTagsBlacklist( array $metaTagsBlacklist ) {
		$this->metaTagsBlacklist = array_flip( $metaTagsBlacklist );
	}

	/**
	 * @since  1.0
	 *
	 * @return boolean
	 */
	public function canUseOutputPage() {

		if ( $this->outputPage->getTitle() === null || $this->outputPage->getTitle()->isSpecialPage() ) {
			return false;
		}

		return true;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $tag
	 * @param string $content
	 */
	public function addTagContentToOutputPage( $tag, $content ) {

		$tag = strtolower( htmlspecialchars( trim( $tag ) ) );
		$content = htmlspecialchars( $content );

		if ( isset( $this->metaTagsBlacklist[$tag] ) ) {
			return;
		}

		// If a tag contains a `og:` such as `og:title` it is expected to be a
		// OpenGraph protocol tag
		if ( strpos( $tag, 'og:' ) !== false ) {

			$content = $this->formatContentToIncludeOpenGraphProtocolMarkup(
				$tag,
				$content
			);

			$this->outputPage->addHeadItem( "meta:property:$tag", $content );

			return;
		}

		$this->outputPage->addMeta( $tag, $content );
	}

	private function formatContentToIncludeOpenGraphProtocolMarkup( $tag, $content ) {

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
