<?php

if ( ! function_exists( '_require_if_exists' ) ) {
	function _require_if_exists( $file ) {
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}
}

_require_if_exists( __DIR__ . '/../../vendor/autoload.php' );

require_once __DIR__ . '/functions.php';

WP_Mock::activateStrictMode();
WP_Mock::bootstrap();
