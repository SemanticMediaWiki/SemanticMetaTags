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
	 * @var PropertyValuesContentFinder
	 */
	private $propertyValuesContentFinder;

	/**
	 * @var OutputPageTagFormatter
	 */
	private $outputPageTagFormatter;

	/**
	 * @var array
	 */
	private $metaTagsContentPropertySelector = array();

	/**
	 * @var array
	 */
	private $metaTagsStaticContentDescriptor = array();

	/**
	 * @since 1.0
	 *
	 * @param PropertyValuesContentFinder $propertyValuesContentFinder
	 * @param OutputPageTagFormatter $outputPageTagFormatter
	 */
	public function __construct( PropertyValuesContentFinder $propertyValuesContentFinder, OutputPageTagFormatter $outputPageTagFormatter ) {
		$this->propertyValuesContentFinder = $propertyValuesContentFinder;
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
	 *
	 * @param array $metaTagsStaticContentDescriptor
	 */
	public function setMetaTagsStaticContentDescriptor( array $metaTagsStaticContentDescriptor ) {
		$this->metaTagsStaticContentDescriptor = $metaTagsStaticContentDescriptor;
	}

	/**
	 * @since 1.0
	 */
	public function addMetaTags() {

		if ( !$this->outputPageTagFormatter->canUseOutputPage() ) {
			return;
		}

		$this->addMetaTagsForProperties( $this->metaTagsContentPropertySelector );
		$this->addMetaTagsForStaticContent( $this->metaTagsStaticContentDescriptor );
	}

	private function addMetaTagsForProperties( $metaTagsContentPropertySelector ) {
		foreach ( $metaTagsContentPropertySelector as $tag => $propertySelector ) {
			$this->addMetaTagsForPropertySelector( $tag, $propertySelector );
		}
	}

	private function addMetaTagsForStaticContent( $metaTagsStaticContentDescriptor ) {

		foreach ( $metaTagsStaticContentDescriptor as $tag => $content ) {

			if ( $content === '' ) {
				return;
			}

			$this->outputPageTagFormatter->addTagContentToOutputPage(
				$tag,
				$content
			);
		}
	}

	private function addMetaTagsForPropertySelector( $tag, $propertySelector ) {

		if ( $propertySelector === '' || $propertySelector === array() ) {
			return;
		}

		if ( is_string( $propertySelector ) ) {
			$propertySelector = explode( ',', $propertySelector );
		}

		$content = $this->propertyValuesContentFinder->findContentForProperties(
			$propertySelector
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
