<?php

function _sc_tests_require_once_if_exists( $file ) {
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

_sc_tests_require_once_if_exists( __DIR__ . '/../../vendor/autoload.php' );
