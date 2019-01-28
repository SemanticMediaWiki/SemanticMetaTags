<?php

use SMT\HookRegistry;
use SMT\Options;
use SMW\ApplicationFactory;

/**
 * @see https://github.com/SemanticMediaWiki/SemanticMetaTags/
 *
 * @defgroup SMT Semantic Meta Tags
 */

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

	}

	/**
	 * @since 1.0
	 */
	public static function initExtension( $credits = [] ) {

		// See https://phabricator.wikimedia.org/T151136
		define( 'SMT_VERSION', isset( $credits['version'] ) ? $credits['version'] : 'UNKNOWN' );

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

		if ( !defined( 'SMW_VERSION' ) ) {

			if ( PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg' ) {
				die( "\nThe 'Semantic Meta Tags' extension requires 'Semantic MediaWiki' to be installed and enabled.\n" );
			} else {
				die(
					'<b>Error:</b> The <a href="https://www.semantic-mediawiki.org/wiki/Extension:Semantic_Meta_Tags">Semantic Meta Tags</a> ' .
					'extension requires <a href="https://www.semantic-mediawiki.org/wiki/Semantic_MediaWiki">Semantic MediaWiki</a> to be ' .
					'installed and enabled.<br>'
				);
			}
		}

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
