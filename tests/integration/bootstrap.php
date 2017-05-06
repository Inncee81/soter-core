<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

function _sc_tests_require_if_exists( $file ) {
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

function _sc_tests_manually_load_plugin() {
	_sc_tests_require_if_exists( __DIR__ . '/../../vendor/autoload.php' );
}
tests_add_filter( 'muplugins_loaded', '_sc_tests_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

require __DIR__ . '/../trait-gets-http-fixtures-trait.php';

require __DIR__ . '/../class-filesystem-http-client.php';
require __DIR__ . '/../class-null-http-client.php';
require __DIR__ . '/../class-filesystem-cache.php';
require __DIR__ . '/../class-null-cache.php';
