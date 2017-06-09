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
class PropertyValuesContentAggregator {

	/**
	 * @var LazySemanticDataLookup
	 */
	private $lazySemanticDataLookup;

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
	 * @param LazySemanticDataLookup $lazySemanticDataLookup
	 */
	public function __construct( LazySemanticDataLookup $lazySemanticDataLookup ) {
		$this->lazySemanticDataLookup = $lazySemanticDataLookup;
	}

	/**
	 * @since  1.0
	 *
	 * @param boolean $useFallbackChainForMultipleProperties
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
	public function doAggregateFor( array $propertyNames ) {

		$values = [];

		foreach ( $propertyNames as $property ) {

			// If content is already present and the fallback mode is enabled
			// stop requesting additional content
			if ( $this->useFallbackChainForMultipleProperties && $values !== [] ) {
				break;
			}

			$this->fetchContentForProperty( trim( $property ), $values );
		}

		return implode( ',', $values );
	}

	private function fetchContentForProperty( $property, &$values ) {

		$property = DIProperty::newFromUserLabel( $property );
		$semanticData = $this->lazySemanticDataLookup->getSemanticData();

		$this->iterateToCollectPropertyValues(
			$semanticData->getPropertyValues( $property ),
			$values
		);

		foreach ( $semanticData->getSubSemanticData() as $subSemanticData ) {
			$this->iterateToCollectPropertyValues(
				$subSemanticData->getPropertyValues( $property ),
				$values
			);
		}
	}

	private function iterateToCollectPropertyValues( array $propertyValues, &$values ) {

		foreach ( $propertyValues as $value ) {

			// Content escaping (htmlspecialchars) is being carried out
			// by the instance that adds the content
			if ( $value instanceof DIBlob ) {
				$values[$value->getHash()] = $value->getString();
			} elseif( $value instanceof DIWikiPage || $value instanceof DIUri ) {
				$values[$value->getHash()] = $value->getSortKey();
			}
		}
	}

}
