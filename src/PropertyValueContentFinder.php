<?php

namespace SMT;

use SMW\SemanticData;
use SMW\DIProperty;
use SMW\DIWikiPage;
use SMWDIBlob as DIBlob;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class PropertyValueContentFinder {

	/**
	 * @var SemanticData
	 */
	private $semanticData;

	/**
	 * @since 1.0
	 *
	 * @param SemanticData $semanticData
	 */
	public function __construct( SemanticData $semanticData ) {
		$this->semanticData = $semanticData;
	}

	/**
	 * @since  1.0
	 *
	 * @param array $properties
	 */
	public function findContentForProperties( array $properties ) {

		$values = array();

		foreach ( $properties as $property ) {
			$this->findContentForProperty( trim( $property ), $values );
		}

		return implode( ',', $values );
	}

	private function findContentForProperty( $property, &$values ) {

		$property = DIProperty::newFromUserLabel( $property );

		$this->iterateOverPropertyValues(
			$this->semanticData->getPropertyValues( $property ),
			$values
		);

		foreach ( $this->semanticData->getSubSemanticData() as $subSemanticData ) {
			$this->iterateOverPropertyValues(
				$subSemanticData->getPropertyValues( $property ),
				$values
			);
		}
	}

	private function iterateOverPropertyValues( array $propertyValues, &$values ) {

		foreach ( $propertyValues as $value ) {

			if ( $value instanceOf DIBlob ) {
				$values[] = htmlspecialchars( $value->getString() );
			} elseif( $value instanceOf DIWikiPage ) {
				$values[] = htmlspecialchars( $value->getSortKey() );
			}
		}
	}

}
