<?php

/**
 * DO NOT EDIT!
 *
 * The following default settings are to be used by the extension itself,
 * please modify settings in the LocalSettings file.
 *
 * @codeCoverageIgnore
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( 'This file is part of the SemanticMetaTags extension, it is not a valid entry point.' );
}

/**
 * An array of tags that assigns related properties such as:
 *
 * 'keywords' => array( 'Has keywords', ... )
 */
$GLOBALS['smtgTagsProperties'] = [];

/**
 * Describes static content for an assigned `<meta>` tag
 *
 * 'some:tag' => 'Content that is static'
 */
$GLOBALS['smtgTagsStrings'] = [];

/**
 * Listed tags are generally assumed to be reserved or excluded for free use
 */
$GLOBALS['smtgTagsBlacklist'] = [
	'generator',
	'robots'
];

/**
 * In case it is set `true` then the first property that returns a valid content
 * for an assigned tag will be used  exclusively.
 */
$GLOBALS['smtgTagsPropertyFallbackUsage'] = false;

/**
 * Identifies prefixes that require `meta:property:...`
 */
$GLOBALS['smtgMetaPropertyPrefixes'] = [ 'og:' ];