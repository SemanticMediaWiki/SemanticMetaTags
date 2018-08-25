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

if ( defined( 'SMT_VERSION' ) ) {
	// Do not initialize more than once.
	return 1;
}

SemanticMetaTags::load();

/**
 * @codeCoverageIgnore
 */
class SemanticMetaTags {

	/**
	 * @since 1.4
	 *
	 * @note It is expected that this function is loaded before LocalSettings.php
	 * to ensure that settings and global functions are available by the time
	 * the extension is activated.
	 */
	public static function load() {

		// Load DefaultSettings
		require_once __DIR__ . '/DefaultSettings.php';

		if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
			include_once __DIR__ . '/vendor/autoload.php';
		}

		// In case extension.json is being used, the succeeding steps will
		// be handled by the ExtensionRegistry
		self::initExtension();

		$GLOBALS['wgExtensionFunctions'][] = function() {
			self::onExtensionFunction();
		};
	}

	/**
	 * @since 1.0
	 */
	public static function initExtension() {

		define( 'SMT_VERSION', '1.5.0-alpha' );

		// Register extension info
		$GLOBALS['wgExtensionCredits']['semantic'][] = [
			'path'           => __FILE__,
			'name'           => 'Semantic Meta Tags',
			'author'         => [ 'James Hong Kong' ],
			'url'            => 'https://github.com/SemanticMediaWiki/SemanticMetaTags/',
			'descriptionmsg' => 'smt-desc',
			'version'        => SMT_VERSION,
			'license-name'   => 'GPL-2.0-or-later',
		];

		// Register message files
		$GLOBALS['wgMessagesDirs']['SemanticMetaTags'] = __DIR__ . '/i18n';
	}

	/**
	 * @since 1.4
	 */
	public static function doCheckRequirements() {

		if ( version_compare( $GLOBALS[ 'wgVersion' ], '1.27', 'lt' ) ) {
			die( '<b>Error:</b> This version of <a href="https://github.com/SemanticMediaWiki/SemanticMetaTags/">SemanticMetaTags</a> is only compatible with MediaWiki 1.27 or above. You need to upgrade MediaWiki first.' );
		}
	}

	/**
	 * @since 1.0
	 */
	public static function onExtensionFunction() {

		// Check requirements after LocalSetting.php has been processed
		self::doCheckRequirements();

		$configuration = [
			'metaTagsContentPropertySelector' => $GLOBALS['smtgTagsProperties'],
			'metaTagsStaticContentDescriptor' => $GLOBALS['smtgTagsStrings'],
			'metaTagsBlacklist' => $GLOBALS['smtgTagsBlacklist'],
			'metaTagsFallbackUseForMultipleProperties' => $GLOBALS['smtgTagsPropertyFallbackUsage'],
			'metaTagsMetaPropertyPrefixes' => $GLOBALS['smtgMetaPropertyPrefixes']
		];

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
