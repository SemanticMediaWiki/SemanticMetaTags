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
 * An array of tags to assign related properties such as
 *
 * 'keywords' => array( 'Has keywords', ... )
 */
$GLOBALS['smtgTagsProperties'] = array();

/**
 * Describes static content for an assigned `<meta>` tag
 *
 * 'some:tag' => 'Content that is static'
 */
$GLOBALS['smtgTagsStrings'] = array();

/**
 * Listed tags are generally assumed to be reserved or excluded for free use
 */
$GLOBALS['smtgTagsBlacklist'] = array(
	'generator',
	'robots'
);

/**
 * In case it is set `true` then the first property that returns a valid content
 * for an assigned tag will be used  exclusively.
 */
$GLOBALS['smtgTagsPropertyFallbackUsage'] = false;
