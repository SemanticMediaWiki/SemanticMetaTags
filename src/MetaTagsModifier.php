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
	 * @var PropertyValuesContentAggregator
	 */
	private $propertyValuesContentAggregator;

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
	 * @param PropertyValuesContentAggregator $propertyValuesContentAggregator
	 * @param OutputPageTagFormatter $outputPageTagFormatter
	 */
	public function __construct( PropertyValuesContentAggregator $propertyValuesContentAggregator, OutputPageTagFormatter $outputPageTagFormatter ) {
		$this->propertyValuesContentAggregator = $propertyValuesContentAggregator;
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

		if ( !$this->outputPageTagFormatter->canUseTagFormatter() ) {
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

		$content = $this->propertyValuesContentAggregator->doAggregateFor(
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
