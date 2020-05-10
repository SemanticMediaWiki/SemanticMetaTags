<?php

namespace SMT;

use SMW\SemanticData;
use SMW\Store;
use SMW\ParserData;
use SMW\RequestOptions;

/**
 * Sometimes the ParserCache provides an outdated ParserOutput with no
 * external data (e.g SematicData) attached therefore try to get the data
 * by other means.
 *
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class LazySemanticDataLookup {

	/**
	 * @var ParserData
	 */
	private $parserData;

	/**
	 * @var Store
	 */
	private $store;

	/**
	 * @var SemanticData|null
	 */
	private $semanticData = null;

	/**
	 * @since 1.0
	 *
	 * @param ParserData $parserData
	 * @param Store $store
	 */
	public function __construct( ParserData $parserData, Store $store ) {
		$this->parserData = $parserData;
		$this->store = $store;
	}

	/**
	 * @since  1.0
	 *
	 * @return SemanticData
	 */
	public function getSemanticData() {

		if ( $this->semanticData === null ) {
			$this->semanticData = $this->fetchSemanticData();
		}

		return $this->semanticData;
	}

	private function fetchSemanticData() {

		// First try the ParserOuput
		$semanticData = $this->parserData->getSemanticData();

		if ( !$semanticData->isEmpty() ) {
			return $semanticData;
		}

		$requestOptions = new RequestOptions();
		$requestOptions->setCaller( __METHOD__ );

		$subject = $this->parserData->getSemanticData()->getSubject();

		// Final method is the Store
		return $this->store->getSemanticData(
			$subject,
			$requestOptions
		);
	}

}
