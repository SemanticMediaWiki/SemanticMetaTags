<?php

namespace SMT;

use SMW\Store;
use SMW\ApplicationFactory;

/**
 * @license GNU GPL v2+
 * @since 1.0
 *
 * @author mwjames
 */
class HookRegistry {

	/**
	 * @var array
	 */
	private $configuration;

	/**
	 * @since 1.0
	 *
	 * @param array $configuration
	 */
	public function __construct( $configuration ) {
		$this->configuration = $configuration;
	}

	/**
	 * @since  1.0
	 *
	 * @param array &$wgHooks
	 */
	public function register( &$wgHooks ) {

		$configuration = $this->configuration;

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
		 */
		$wgHooks['OutputPageParserOutput'][] = function ( &$outputPage, $parserOutput ) use( $configuration ) {

			$parserData = ApplicationFactory::getInstance()->newParserData(
				$outputPage->getTitle(),
				$parserOutput
			);

			$outputPageMetaTagsModifier = new OutputPageMetaTagsModifier(
				new PropertyValueContentFinder( $parserData->getSemanticData() )
			);

			$outputPageMetaTagsModifier->setMetaTagsContentPropertySelector( $configuration['metaTagsContentPropertySelector'] );
			$outputPageMetaTagsModifier->modifyOutputPage( $outputPage );

			return true;
		};
	}

}
