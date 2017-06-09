<?php

namespace SMT;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class MetaTagsProcessor {

	/**
	 * @var PropertyValuesContentAggregator
	 */
	private $propertyValuesContentAggregator;

	/**
	 * @var OutputPageHtmlTagsInserter
	 */
	private $outputPageHtmlTagsInserter;

	/**
	 * @var array
	 */
	private $metaTagsContentPropertySelector = [];

	/**
	 * @var array
	 */
	private $metaTagsStaticContentDescriptor = [];

	/**
	 * @since 1.0
	 *
	 * @param PropertyValuesContentAggregator $propertyValuesContentAggregator
	 */
	public function __construct( PropertyValuesContentAggregator $propertyValuesContentAggregator ) {
		$this->propertyValuesContentAggregator = $propertyValuesContentAggregator;
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
	 *
	 * @param OutputPageHtmlTagsInserter $outputPageHtmlTagsInserter
	 */
	public function addMetaTags( OutputPageHtmlTagsInserter $outputPageHtmlTagsInserter ) {

		if ( !$outputPageHtmlTagsInserter->canUseOutputPage() ) {
			return;
		}

		$this->outputPageHtmlTagsInserter = $outputPageHtmlTagsInserter;

		$this->addMetaTagsForProperties();
		$this->addMetaTagsForStaticContent();
	}

	private function addMetaTagsForProperties() {

		foreach ( $this->metaTagsContentPropertySelector as $tag => $properties ) {

			if ( $properties === '' || $properties === [] ) {
				continue;
			}

			$this->addMetaTagsForAggregatedProperties( $tag, $properties );
		}
	}

	private function addMetaTagsForAggregatedProperties( $tag, $properties ) {

		if ( is_string( $properties ) ) {
			$properties = explode( ',', $properties );
		}

		$content = $this->propertyValuesContentAggregator->doAggregateFor(
			$properties
		);

		if ( $content === '' ) {
			return;
		}

		$this->outputPageHtmlTagsInserter->addTagContentToOutputPage(
			$tag,
			$content
		);
	}

	private function addMetaTagsForStaticContent() {

		foreach ( $this->metaTagsStaticContentDescriptor as $tag => $content ) {

			if ( $content === '' ) {
				continue;
			}

			$this->outputPageHtmlTagsInserter->addTagContentToOutputPage(
				$tag,
				$content
			);
		}
	}

}
