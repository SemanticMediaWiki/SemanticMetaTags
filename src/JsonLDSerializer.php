<?php

namespace SMT;

use Title;
use OutputPage;
use SpecialPage;
use Html;

/**
 * @credits https://www.mediawiki.org/wiki/Extension:PageProperties 
 */

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

		// @TODO use directly the function makeExportDataForSubject
		// SemanticMediawiki/includes/export/SMW_Exporter.php
		$export_rdf = SpecialPage::getTitleFor( 'ExportRDF' );
		if ( $export_rdf->isKnown() ) {
			$export_url = $export_rdf->getFullURL( [
				'page' => $title->getFullText(),
				'recursive' => '1',
				'backlinks' => 0
			] );

			try {
				$foaf = new \EasyRdf\Graph( $export_url );
				$foaf->load();

				$format = \EasyRdf\Format::getFormat( 'jsonld' );
				$output = $foaf->serialise( $format, [
					'compact' => true,
				] );

			} catch ( Exception $e ) {
				self::$Logger->error( 'EasyRdf error: ' . $export_url );
				return;
			}

			// https://hotexamples.com/examples/-/EasyRdf_Graph/serialise/php-easyrdf_graph-serialise-method-examples.html
			if ( is_scalar( $output ) ) {
				$outputPage->addHeadItem( 'json-ld', Html::Element(
						'script', [ 'type' => 'application/ld+json' ], $output
					)
				);
			}
		}
	}
}
