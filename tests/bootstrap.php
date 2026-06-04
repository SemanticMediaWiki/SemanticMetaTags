<?php

if ( PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );
ini_set( 'display_errors', 1 );

if ( !defined( 'SMW_PHPUNIT_AUTOLOADER_FILE' ) || !is_readable( SMW_PHPUNIT_AUTOLOADER_FILE ) ) {
	die( "\nThe Semantic MediaWiki test autoloader is not available" );
}

$width = 20;

if ( !defined( 'SMW_PHPUNIT_FIRST_COLUMN_WIDTH' ) ) {
	define( 'SMW_PHPUNIT_FIRST_COLUMN_WIDTH', $width );
}

$extensionInfo = json_decode( file_get_contents( __DIR__ . '/../extension.json' ), true );

print sprintf( "\n%-{$width}s%s\n", "Semantic Meta Tags: ", $extensionInfo['version'] ?? 'UNKNOWN' );

$autoLoader = require SMW_PHPUNIT_AUTOLOADER_FILE;
$autoloader->addPsr4( 'SMT\\Tests\\', __DIR__ . '/phpunit/Unit' );
$autoloader->addPsr4( 'SMT\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );

unset( $autoloader );
