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
	 * @var PropertyValuesContentFetcher
	 */
	private $propertyValuesContentFetcher;

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
	 * @param PropertyValuesContentFetcher $propertyValuesContentFetcher
	 * @param OutputPageTagFormatter $outputPageTagFormatter
	 */
	public function __construct( PropertyValuesContentFetcher $propertyValuesContentFetcher, OutputPageTagFormatter $outputPageTagFormatter ) {
		$this->propertyValuesContentFetcher = $propertyValuesContentFetcher;
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

		$this->addMetaTagsForPropertySelector( $this->metaTagsContentPropertySelector );
		$this->addMetaTagsForStaticContent( $this->metaTagsStaticContentDescriptor );
	}

	private function addMetaTagsForPropertySelector( $metaTagsContentPropertySelector ) {

		foreach ( $metaTagsContentPropertySelector as $tag => $properties ) {

			if ( $properties === '' || $properties === array() ) {
				continue;
			}

			$this->addMetaTagsForProperties( $tag, $properties );
		}
	}

	private function addMetaTagsForStaticContent( $metaTagsStaticContentDescriptor ) {

		foreach ( $metaTagsStaticContentDescriptor as $tag => $content ) {

			if ( $content === '' ) {
				continue;
			}

			$this->outputPageTagFormatter->addTagContentToOutputPage(
				$tag,
				$content
			);
		}
	}

	private function addMetaTagsForProperties( $tag, $properties ) {

		if ( is_string( $properties ) ) {
			$properties = explode( ',', $properties );
		}

		$content = $this->propertyValuesContentFetcher->fetchContentForProperties(
			$properties
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
