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
class PropertyValueContentFinder {

	/**
	 * @var LazySemanticDataFetcher
	 */
	private $lazySemanticDataFetcher;

	/**
	 * @since 1.0
	 *
	 * @param LazySemanticDataFetcher $lazySemanticDataFetcher
	 */
	public function __construct( LazySemanticDataFetcher $lazySemanticDataFetcher ) {
		$this->lazySemanticDataFetcher = $lazySemanticDataFetcher;
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
			$this->findContentForProperty( trim( $property ), $values );
		}

		return implode( ',', $values );
	}

	private function findContentForProperty( $property, &$values ) {

		$property = DIProperty::newFromUserLabel( $property );
		$semanticData = $this->lazySemanticDataFetcher->getSemanticData();

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
