<?php


( function () {
	$autoloader = __DIR__ . '/../vendor/autoload.php';

	if ( file_exists( $autoloader ) ) {
		require_once $autoloader;
	}

	require_once __DIR__ . '/functions.php';

	WP_Mock::activateStrictMode();
	WP_Mock::bootstrap();
} )();
