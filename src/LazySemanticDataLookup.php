<?php

namespace SMT;

use SMW\SemanticData;
use SMW\Store;
use SMW\ParserData;

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

		// In 2.3 SMW-core we expect that `$GLOBALS['smwgValueLookupCacheType']`
		// is enabled by whichCachedValueLookupStore::getSemanticData returns results
		// from Cache otherwise a DB connection is used

		// Final method is the Store
		return $this->store->getSemanticData(
			$this->parserData->getSemanticData()->getSubject()
		);
	}

}
