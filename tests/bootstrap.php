<?php

if ( PHP_SAPI !== 'cli' ) {
	die( 'Not an entry point' );
}

error_reporting( E_ALL | E_STRICT );
date_default_timezone_set( 'UTC' );
ini_set( 'display_errors', 1 );

if ( !is_readable( $autoloaderClassPath = __DIR__ . '/../../SemanticMediaWiki/tests/autoloader.php' ) ) {
	die( 'The SemanticMediaWiki test autoloader is not available' );
}

if ( !class_exists( 'SemanticMetaTags' ) || ( $version = SemanticMetaTags::getVersion() ) === null ) {
	die( "\nSemantic Meta Tags is not available, please check your Composer or LocalSettings.\n" );
}

print sprintf( "\n%-20s%s\n", "Semantic Meta Tags: ", $version );

$autoloader = require $autoloaderClassPath;
$autoloader->addPsr4( 'SMT\\Tests\\', __DIR__ . '/phpunit/Unit' );
$autoloader->addPsr4( 'SMT\\Tests\\Integration\\', __DIR__ . '/phpunit/Integration' );
unset( $autoloader );
