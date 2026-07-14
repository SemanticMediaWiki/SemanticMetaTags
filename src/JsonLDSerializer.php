<?php

/**
 * @see  https://www.mediawiki.org/wiki/Extension:PageProperties
 * @author thomas-topway-it for KM-A
 */

namespace SMT;

use Html;
use MediaWiki\MediaWikiServices;
use OutputPage;
use SpecialPage;
use Throwable;
use Title;
use Wikimedia\ObjectCache\WANObjectCache;

class JsonLDSerializer {
	/**
	 * @param Title $title
	 * @param OutputPage $outputPage
	 */
	public function __construct( $title, $outputPage ) {
		if ( $this->isKnownArticle( $title ) ) {
			$this->setJsonLD( $title, $outputPage );
		}
	}

	/**
	 * @see https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/extensions/PageProperties/+/548d30609c512a79e202dfa7c02a298c66ca34fa/includes/PageProperties.php
	 * @param Title $title
	 * @return bool
	 */
	private function isKnownArticle( $title ) {
		return ( $title && $title->canExist() && $title->getArticleID() > 0
			&& $title->isKnown() );
	}

	/**
	 * @see https://gerrit.wikimedia.org/r/plugins/gitiles/mediawiki/extensions/PageProperties/+/548d30609c512a79e202dfa7c02a298c66ca34fa/includes/PageProperties.php
	 * @param Title $title
	 * @param OutputPage $outputPage
	 * @return void
	 */
	public static function setJsonLD( $title, $outputPage ) {
		if ( !class_exists( '\EasyRdf\Graph' ) || !class_exists( '\ML\JsonLD\JsonLD' ) ) {
			return;
		}

		# HOTFIX BEG 2026-07-13 Harden JSON-LD RDF fetch path (safe errors, absolute URL, cache)
        if ( $outputPage->isDisabled() ) {
                        return;
                }
		
		// @TODO use directly the function makeExportDataForSubject
		// SemanticMediawiki/includes/export/SMW_Exporter.php
                $export_rdf = SpecialPage::getTitleFor( 'ExportRDF' );
                if ( !$export_rdf->isKnown() ) {
                        return;
                }

                $export_url = $export_rdf->getFullURL( [
                        'page' => $title->getFullText(),
                        'recursive' => '1',
                        'backlinks' => 0
                ], false, PROTO_FALLBACK );

                $urlUtils = MediaWikiServices::getInstance()->getUrlUtils();
                $exportUrlBits = $urlUtils->parse( $export_url );
                if ( $exportUrlBits === null || ( $exportUrlBits['host'] ?? '' ) === '' ) {
                        $export_url = (string)$urlUtils->expand( $export_url, PROTO_CANONICAL );
                        $exportUrlBits = $urlUtils->parse( $export_url );
                        if ( $exportUrlBits === null || ( $exportUrlBits['host'] ?? '' ) === '' ) {
                                wfDebugLog(
                                        'SemanticMetaTags',
                                        __METHOD__ . ' could not build absolute RDF export URL for ' . $title->getPrefixedText()
                                );
                                return;
                        }
                }

		
                $revisionId = (int)$title->getLatestRevID();
                $wanCache = MediaWikiServices::getInstance()->getMainWANObjectCache();
                $cacheKey = $wanCache->makeKey(
                        'SemanticMetaTags',
                        'jsonld',
                        $title->getNamespace(),
                        $title->getDBkey(),
                        $revisionId,
                        'v1'
                );

                try {
                        $output = $wanCache->getWithSetCallback(
                                $cacheKey,
                                WANObjectCache::TTL_HOUR,
                                static function () use ( $export_url ) {
                                        $foaf = new \EasyRdf\Graph( $export_url );
                                        $foaf->load();

                                        $format = \EasyRdf\Format::getFormat( 'jsonld' );
                                        $result = $foaf->serialise( $format, [
                                                'compact' => true,
                                        ] );

                                        return is_scalar( $result ) ? (string)$result : '';
                                }
                        );
                        if ( is_string( $output ) && $output !== '' ) {
                                $outputPage->addHeadItem( 'json-ld', Html::Element(
                                                'script', [ 'type' => 'application/ld+json' ], $output
                                        )
                                );
                        }
                } catch ( Throwable $e ) {
                        wfDebugLog(
                                'SemanticMetaTags',
                                __METHOD__ . ' failed for ' . $title->getPrefixedText() .
                                ' (url: ' . $export_url . '): ' . $e->getMessage()
                        );
                        return;
                }
                # HOTFIX END 2026-07-13 Harden JSON-LD RDF fetch path (safe errors, absolute URL, cache)
        }
}
