<?php

namespace SMT;

use SMW\ApplicationFactory;
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
	 */
	public function register() {

		$configuration = $this->configuration;

		/**
		 * @see https://www.mediawiki.org/wiki/Manual:Hooks/OutputPageParserOutput
		 */
		$this->handlers['OutputPageParserOutput'] = function ( &$outputPage, $parserOutput ) use( $configuration ) {

			$parserData = ApplicationFactory::getInstance()->newParserData(
				$outputPage->getTitle(),
				$parserOutput
			);

			$metaTagsModifier = new MetaTagsModifier(
				new PropertyValueContentFinder( $parserData->getSemanticData() ),
				new OutputPageTagFormatter( $outputPage )
			);

			$metaTagsModifier->setMetaTagsContentPropertySelector( $configuration['metaTagsContentPropertySelector'] );
			$metaTagsModifier->setMetaTagsStaticContentDescriptor( $configuration['metaTagsStaticContentDescriptor'] );

			$metaTagsModifier->addMetaTags();

			return true;
		};

		foreach ( $this->handlers as $name => $callback ) {
			Hooks::register( $name, $callback );
		}
	}

	/**
	 * @since  1.0
	 */
	public function deregister() {
		foreach ( array_keys( $this->handlers ) as $name ) {

			Hooks::clear( $name );

			if ( isset( $GLOBALS['wgHooks'][ $name ] ) ) {
				unset( $GLOBALS['wgHooks'][ $name ] );
			}
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
	 * @since  1.0
	 *
	 * @param string $name
	 *
	 * @return array
	 */
	public function getHandlers( $name ) {
		return Hooks::getHandlers( $name );
	}

}
