<?php

namespace SMT;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class MetaTagsModifier {

	/**
	 * @var PropertyValueContentFinder
	 */
	private $propertyValueContentFinder;

	/**
	 * @var OutputPageTagFormatter
	 */
	private $outputPageTagFormatter;

	/**
	 * @var array
	 */
	private $metaTagsContentPropertySelector = array();

	/**
	 * @since 1.0
	 *
	 * @param PropertyValueContentFinder $propertyValueContentFinder
	 * @param OutputPageTagFormatter $outputPageTagFormatter
	 */
	public function __construct( PropertyValueContentFinder $propertyValueContentFinder, OutputPageTagFormatter $outputPageTagFormatter ) {
		$this->propertyValueContentFinder = $propertyValueContentFinder;
		$this->outputPageTagFormatter = $outputPageTagFormatter;
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
	 */
	public function addMetaTags() {

		if ( !$this->outputPageTagFormatter->canUseOutputPage() ) {
			return;
		}

		$this->addMetaTagsForProperties( $this->metaTagsContentPropertySelector );
	}

	private function addMetaTagsForProperties( $metaTagsContentPropertySelector ) {
		foreach ( $metaTagsContentPropertySelector as $tag => $propertySelector ) {
			$this->addMetaTagsForPropertySelector( $tag, $propertySelector );
		}
	}

	private function addMetaTagsForPropertySelector( $tag, $propertySelector ) {

		if ( $propertySelector === '' ) {
			return;
		}

		$content = $this->propertyValueContentFinder->findContentForProperties(
			explode( ',', $propertySelector )
		);

		if ( $content === '' ) {
			return;
		}

		$this->outputPageTagFormatter->addTagContentToOutputPage(
			$tag,
			$content
		);
	}

}
