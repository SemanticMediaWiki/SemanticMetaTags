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
	 * @param array $configuration
	 */
	public function __construct( Store $store, $configuration ) {
		$this->addCallbackHandlers( $store, $configuration );
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
	public function getHandlersFor( $name ) {
		return isset( $this->handlers[$name] ) ? $this->handlers[$name] : false;
	}

	private function addCallbackHandlers( $store, $configuration ) {

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
		 */
		$this->handlers['OutputPageParserOutput'] = function ( &$outputPage, $parserOutput ) use( $store, $configuration ) {

			$parserData = ApplicationFactory::getInstance()->newParserData(
				$outputPage->getTitle(),
				$parserOutput
			);

			$fallbackSemanticDataFetcher = new FallbackSemanticDataFetcher(
				$parserData,
				$store
			);

			$outputPageTagFormatter = new OutputPageTagFormatter( $outputPage );
			$outputPageTagFormatter->setMetaTagsBlacklist( $configuration['metaTagsBlacklist'] );
			$outputPageTagFormatter->setViewActionState( \Action::getActionName( $outputPage->getContext() ) );

			$propertyValuesContentFetcher = new PropertyValuesContentFetcher( $fallbackSemanticDataFetcher );

			$propertyValuesContentFetcher->useFallbackChainForMultipleProperties(
				$configuration['metaTagsFallbackUseForMultipleProperties']
			);

			$metaTagsModifier = new MetaTagsModifier(
				$propertyValuesContentFetcher,
				$outputPageTagFormatter
			);

			$metaTagsModifier->setMetaTagsContentPropertySelector(
				$configuration['metaTagsContentPropertySelector']
			);

			$metaTagsModifier->setMetaTagsStaticContentDescriptor(
				$configuration['metaTagsStaticContentDescriptor']
			);

			$metaTagsModifier->addMetaTags();

			return true;
		};
	}

}
