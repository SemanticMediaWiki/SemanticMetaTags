<?php

use SMT\HookRegistry;
use SMT\Options;
use SMW\ApplicationFactory;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticMetaTags/
 *
 * @defgroup SMT Semantic Meta Tags
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the SemanticMetaTags extension, it is not a valid entry point.' );
}

if ( version_compare( $GLOBALS[ 'wgVersion' ], '1.23', 'lt' ) ) {
	die( '<b>Error:</b> This version of <a href="https://github.com/SemanticMediaWiki/SemanticMetaTags/">SemanticMetaTags</a> is only compatible with MediaWiki 1.23 or above. You need to upgrade MediaWiki first.' );
}

if ( defined( 'SMT_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

SemanticMetaTags::initExtension();

$GLOBALS['wgExtensionFunctions'][] = function() {
	SemanticMetaTags::onExtensionFunction();
};

/**
 * @codeCoverageIgnore
 */
class SemanticMetaTags {

	/**
	 * @since 1.0
	 */
	public static function initExtension() {

		define( 'SMT_VERSION', '1.2.0' );

		// Register extension info
		$GLOBALS['wgExtensionCredits']['semantic'][] = array(
			'path'           => __FILE__,
			'name'           => 'Semantic Meta Tags',
			'author'         => array( 'James Hong Kong' ),
			'url'            => 'https://github.com/SemanticMediaWiki/SemanticMetaTags/',
			'descriptionmsg' => 'smt-desc',
			'version'        => SMT_VERSION,
			'license-name'   => 'GPL-2.0+',
		);

		// Register message files
		$GLOBALS['wgMessagesDirs']['SemanticMetaTags'] = __DIR__ . '/i18n';
	}

	/**
	 * @since 1.0
	 */
	public static function onExtensionFunction() {

		$configuration = array(
			'metaTagsContentPropertySelector' => $GLOBALS['smtgTagsProperties'],
			'metaTagsStaticContentDescriptor' => $GLOBALS['smtgTagsStrings'],
			'metaTagsBlacklist' => $GLOBALS['smtgTagsBlacklist'],
			'metaTagsFallbackUseForMultipleProperties' => $GLOBALS['smtgTagsPropertyFallbackUsage']
		);

		$hookRegistry = new HookRegistry(
			ApplicationFactory::getInstance()->getStore(),
			new Options( $configuration )
		);

		$hookRegistry->register();
	}

	/**
	 * @since 1.0
	 *
	 * @return string|null
	 */
	public static function getVersion() {
		return SMT_VERSION;
	}

}
