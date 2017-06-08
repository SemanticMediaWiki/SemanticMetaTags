<?php

namespace SMT;

use SMW\ApplicationFactory;
use SMW\Store;
use Hooks;

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
	private $handlers = [];

	/**
	 * @since 1.0
	 *
	 * @param Store $store
	 * @param Options $options
	 */
	public function __construct( Store $store, Options $options ) {
		$this->addCallbackHandlers( $store, $options );
	}

	/**
	 * @since  1.0
	 */
	public function register() {
		foreach ( $this->handlers as $name => $callback ) {
			Hooks::register( $name, $callback );
		}
	}

	/**
	 * @since  1.0
	 *
	 * @param string $name
	 *
	 * @return boolean
	 */
	public function isRegistered( $name ) {
		return Hooks::isRegistered( $name );
	}

	/**
	 * @since  1.1
	 *
	 * @param string $name
	 *
	 * @return Callable|false
	 */
	public function getHandlerFor( $name ) {
		return isset( $this->handlers[$name] ) ? $this->handlers[$name] : false;
	}

	private function addCallbackHandlers( $store, $options ) {

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
		 */
		$this->handlers['OutputPageParserOutput'] = function ( &$outputPage, $parserOutput ) use( $store, $options ) {

			$parserData = ApplicationFactory::getInstance()->newParserData(
				$outputPage->getTitle(),
				$parserOutput
			);

			$lazySemanticDataLookup = new LazySemanticDataLookup(
				$parserData,
				$store
			);

			$outputPageHtmlTagsInserter = new OutputPageHtmlTagsInserter(
				$outputPage
			);

			$outputPageHtmlTagsInserter->setMetaTagsBlacklist(
				$options->get( 'metaTagsBlacklist' )
			);

			$outputPageHtmlTagsInserter->setMetaPropertyPrefixes(
				$options->get( 'metaTagsMetaPropertyPrefixes' )
			);

			$outputPageHtmlTagsInserter->setActionName(
				\Action::getActionName( $outputPage->getContext() )
			);

			$propertyValuesContentAggregator = new PropertyValuesContentAggregator(
				$lazySemanticDataLookup
			);

			$propertyValuesContentAggregator->useFallbackChainForMultipleProperties(
				$options->get( 'metaTagsFallbackUseForMultipleProperties' )
			);

			$metaTagsProcessor = new MetaTagsProcessor(
				$propertyValuesContentAggregator
			);

			$metaTagsProcessor->setMetaTagsContentPropertySelector(
				$options->get( 'metaTagsContentPropertySelector' )
			);

			$metaTagsProcessor->setMetaTagsStaticContentDescriptor(
				$options->get( 'metaTagsStaticContentDescriptor' )
			);

			$metaTagsProcessor->addMetaTags( $outputPageHtmlTagsInserter );

			return true;
		};
	}

}
