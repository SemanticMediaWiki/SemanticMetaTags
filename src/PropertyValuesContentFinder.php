<?php

namespace SMT;

use SMW\DIProperty;
use SMW\DIWikiPage;
use SMWDIBlob as DIBlob;
use SMWDIUri as DIUri;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PropertyValuesContentFinder {

	/**
	 * @var FallbackSemanticDataFetcher
	 */
	private $fallbackSemanticDataFetcher;

	/**
	 * Whether multiple properties should be used through a fallback chain where
	 * the first available property with content will determine the end of the
	 * processing or content being simply concatenated
	 *
	 * @var boolean
	 */
	private $useFallbackChainForMultipleProperties = false;

	/**
	 * @since 1.0
	 *
	 * @param FallbackSemanticDataFetcher $fallbackSemanticDataFetcher
	 */
	public function __construct( FallbackSemanticDataFetcher $fallbackSemanticDataFetcher ) {
		$this->fallbackSemanticDataFetcher = $fallbackSemanticDataFetcher;
	}

	/**
	 * @since  1.0
	 *
	 * @param boolean useFallbackChainForMultipleProperties
	 */
	public function useFallbackChainForMultipleProperties( $useFallbackChainForMultipleProperties ) {
		$this->useFallbackChainForMultipleProperties = $useFallbackChainForMultipleProperties;
	}

	/**
	 * @since  1.0
	 *
	 * @param string[] $propertyNames
	 *
	 * @return string
	 */
	public function findContentForProperties( array $propertyNames ) {

		$values = array();

		foreach ( $propertyNames as $property ) {

			// If content is already present and the fallback mode is enabled
			// stop requesting additional content
			if ( $this->useFallbackChainForMultipleProperties && $values !== array() ) {
				break;
			}

			$this->findContentForProperty( trim( $property ), $values );
		}

		return implode( ',', $values );
	}

	private function findContentForProperty( $property, &$values ) {

		$property = DIProperty::newFromUserLabel( $property );
		$semanticData = $this->fallbackSemanticDataFetcher->getSemanticData();

		$this->iterateOverPropertyValues(
			$semanticData->getPropertyValues( $property ),
			$values
		);

		foreach ( $semanticData->getSubSemanticData() as $subSemanticData ) {
			$this->iterateOverPropertyValues(
				$subSemanticData->getPropertyValues( $property ),
				$values
			);
		}
	}

	private function iterateOverPropertyValues( array $propertyValues, &$values ) {

		foreach ( $propertyValues as $value ) {

			// Content escaping (htmlspecialchars) is being carried out
			// by the instance that adds the content
			if ( $value instanceOf DIBlob ) {
				$values[] = $value->getString();
			} elseif( $value instanceOf DIWikiPage || $value instanceOf DIUri ) {
				$values[] = $value->getSortKey();
			}
		}
	}

}
