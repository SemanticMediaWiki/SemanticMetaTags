<?php

use SMT\HookRegistry;

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

define( 'SMT_VERSION', '1.0-alpha' );

/**
 * @codeCoverageIgnore
 */
call_user_func( function () {

	// Register extension info
	$GLOBALS[ 'wgExtensionCredits' ][ 'semantic' ][ ] = array(
		'path'           => __FILE__,
		'name'           => 'Semantic Meta Tags',
		'author'         => array( 'James Hong Kong' ),
		'url'            => 'https://github.com/SemanticMediaWiki/SemanticMetaTags/',
		'descriptionmsg' => 'smt-desc',
		'version'        => SMT_VERSION,
		'license-name'   => 'GPL-2.0+',
	);

	// Register message files
	$GLOBALS['wgMessagesDirs']['semantic-meta-tags'] = __DIR__ . '/i18n';

	$GLOBALS['egSMTMetaTagsContentPropertySelector'] = array(
		'keywords' => '',
		'descriptions' => '',
		'author' => ''
	);

	// Finalize extension setup
	$GLOBALS['wgExtensionFunctions'][] = function() {

		$configuration = array(
			'metaTagsContentPropertySelector' => $GLOBALS['egSMTMetaTagsContentPropertySelector']
		);

		$hookRegistry = new HookRegistry( $configuration );
		$hookRegistry->register();
	};

} );
