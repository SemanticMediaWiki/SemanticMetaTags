<?php

/**
 * @see https://www.mediawiki.org/wiki/Extension:PageProperties
 * @author thomas-topway-it for KM-A
 */

namespace SMT;

use Exception;
use MediaWiki\Html\Html;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;
use MediaWiki\Output\OutputPage;
use MediaWiki\Title\Title;
use SMW\Exporter\ExporterFactory;

class JsonLDSerializer {
	 /* @var OutputPage */
	 private $outputPage;

	/**
	 * @param Title $title
	 * @param OutputPage $outputPage
	 */
	public function __construct( $title, $outputPage ) {
		if ( $this->isKnownArticle( $title ) ) {
			$this->outputPage = $outputPage;
			$this->setJsonLD( $title );
		}
	}

	/**
	 * @param Title $title
	 * @return bool
	 */
	private function isKnownArticle( $title ) {
		return ( $title && $title->canExist() && $title->getArticleID() > 0
			&& $title->isKnown() );
	}

	/**
	 * @see https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/extensions/SubpageNavigation/+/refs/heads/master/includes/SubpageNavigation.php#278
	 * 
	 * @return mixed|false
	 */
	private function getCache() {
		switch ( $GLOBALS['smtgJsonLDCacheStore'] ) {
			case 'LocalServerObjectCache':
				return MediaWikiServices::getInstance()->getLocalServerObjectCache();

			// @see https://github.com/SemanticMediaWiki/SemanticMetaTags/pull/118
			case 'WANObjectCache':
				return MediaWikiServices::getInstance()->getMainWANObjectCache();

			case 'SessionCache':
			default:
				// @see MediaWiki\Session\SessionManager
				$config = MediaWikiServices::getInstance()->getMainConfig();
				$store = \ObjectCache::getInstance( $config->get(
					class_exists( 'MainConfigNames' ) ? MainConfigNames::SessionCacheType : 'SessionCacheType' ) );
				return new \CachedBagOStuff( $store );
		}
	}

	/**
	 * @param Title $title
	 * @return string|null
	 */
	private function getJsonLDOutput( $title ) {
		// @see SMW\Query\ResultPrinters\RdfResultPrinter
		$exporterFactory = new ExporterFactory();
		$serializer = $exporterFactory->newRDFXMLSerializer();
		$export_controller = $exporterFactory->newExportController( $serializer );

		$this->outputPage->disable();
		ob_start();

		try {
			$recursive = true;
			$export_controller->enableBacklinks( false );
			$revisionDate = false;
			$pages = [ $title->getFullText() ];
			$export_controller->printPages( $pages, $recursive, $revisionDate );

		} catch ( Exception $e ) {
			ob_end_clean();
			LoggerFactory::getInstance( 'smt' )->error( 'SMW ExporterFactory error: ' . $e->getMessage() );
			return;
		}

		$data = ob_get_clean();

		try {
			$foaf = new \EasyRdf\Graph( $title->getFullUrl(), $data );
			$format = \EasyRdf\Format::getFormat( 'jsonld' );
			return $foaf->serialise( $format, [
				'compact' => true,
			] );

		} catch ( Exception $e ) {
			LoggerFactory::getInstance( 'smt' )->error( 'EasyRdf error: ' . $e->getMessage() );
		}
	}

	/**
	 * @param Title $title
	 * @return void
	 */
	public function setJsonLD( $title ) {
		if ( !class_exists( '\EasyRdf\Graph' ) || !class_exists( '\ML\JsonLD\JsonLD' ) ) {
			return;
		}

		$cache = null;
		if ( empty( $GLOBALS['smtgJsonLDDisableCache'] ) ) {
			$revisionId = (int)$title->getLatestRevID();
			$cache = $this->getCache();

			if ( $cache ) {
				$cacheKey = $cache->makeKey(
					'SemanticMetaTags',
					'jsonld',
					$title->getNamespace(),
					$title->getDBkey(),
					$revisionId,
				);

				$thisClass = $this;
				$output = $cache->getWithSetCallback(
					$cacheKey,
					$cache::TTL_INDEFINITE,
					static function () use ( &$thisClass, $title ) {
						return $thisClass->getJsonLDOutput( $title );
					}
				);
			}
		}

		if ( !$cache ) {		
			$output = $this->getJsonLDOutput( $title );
		}

		// https://hotexamples.com/examples/-/EasyRdf_Graph/serialise/php-easyrdf_graph-serialise-method-examples.html
		if ( is_string( $output ) && !empty( $output ) ) {
			$this->outputPage->addHeadItem( 'json-ld', Html::Element(
					'script', [ 'type' => 'application/ld+json' ], $output
				)
			);
		}
	}
}
