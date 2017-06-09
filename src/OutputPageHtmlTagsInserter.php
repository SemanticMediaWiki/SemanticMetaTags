<?php

namespace SMT;

use OutputPage;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class OutputPageHtmlTagsInserter {

	/**
	 * @var OutputPage
	 */
	private $outputPage;

	/**
	 * @var array
	 */
	private $metaTagsBlacklist = [];

	/**
	 * @var array
	 */
	private $metaPropertyPrefixes = [];

	/**
	 * @var boolean
	 */
	private $metaPropertyMarkup = false;

	/**
	 * @var string
	 */
	private $actionName = '';

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
	 * @since 1.4
	 *
	 * @param array $metaPropertyPrefixes
	 */
	public function setMetaPropertyPrefixes( array $metaPropertyPrefixes ) {
		$this->metaPropertyPrefixes = $metaPropertyPrefixes;
	}

	/**
	 * @since 1.0
	 *
	 * @param string $actionName
	 */
	public function setActionName( $actionName ) {
		$this->actionName = $actionName;
	}

	/**
	 * @since  1.0
	 *
	 * @return boolean
	 */
	public function canUseOutputPage() {

		if ( $this->outputPage->getTitle() === null || $this->outputPage->getTitle()->isSpecialPage() || $this->actionName !== 'view' ) {
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

		if ( $this->reqMetaPropertyMarkup( $tag ) ) {
			return $this->addMetaPropertyMarkup( $tag, $content );
		}

		$this->outputPage->addMeta( $tag, $content );
	}

	private function addMetaPropertyMarkup( $tag, $content ) {

		$comment = '';

		if ( !$this->metaPropertyMarkup ) {
			$comment .= '<!-- Semantic MetaTags -->' . "\n";
			$this->metaPropertyMarkup = true;
		}

		$content = $comment . \Html::element( 'meta', [
			'property' => $tag,
			'content'  => $content
		] );

		$this->outputPage->addHeadItem( "meta:property:$tag", $content );
	}

	private function reqMetaPropertyMarkup( $tag ) {

		// If a tag contains a `og:` such as `og:title` it is expected to be a
		// OpenGraph protocol tag along with other prefixes maintained in
		// $GLOBALS['smtgMetaPropertyPrefixes']
		foreach ( $this->metaPropertyPrefixes as $prefix ) {
			if ( strpos( $tag, $prefix ) !== false ) {
				return true;
			}
		}

		return false;
	}

}
