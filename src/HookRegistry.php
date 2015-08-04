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
	private $handlers = array();

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

			$semanticDataFallbackFetcher = new SemanticDataFallbackFetcher(
				$parserData,
				$store
			);

			$outputPageTagFormatter = new OutputPageTagFormatter( $outputPage );

			$outputPageTagFormatter->setMetaTagsBlacklist(
				$options->get( 'metaTagsBlacklist' )
			);

			$outputPageTagFormatter->setActionName(
				\Action::getActionName( $outputPage->getContext() )
			);

			$propertyValuesContentAggregator = new PropertyValuesContentAggregator(
				$semanticDataFallbackFetcher
			);

			$propertyValuesContentAggregator->useFallbackChainForMultipleProperties(
				$options->get( 'metaTagsFallbackUseForMultipleProperties' )
			);

			$metaTagsModifier = new MetaTagsModifier(
				$propertyValuesContentAggregator,
				$outputPageTagFormatter
			);

			$metaTagsModifier->setMetaTagsContentPropertySelector(
				$options->get( 'metaTagsContentPropertySelector' )
			);

			$metaTagsModifier->setMetaTagsStaticContentDescriptor(
				$options->get( 'metaTagsStaticContentDescriptor' )
			);

			$metaTagsModifier->addMetaTags();

			return true;
		};
	}

}
