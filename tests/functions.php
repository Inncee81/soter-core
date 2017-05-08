<?php

function sct_get_http_fixture_path( $path ) {
	return __DIR__ . '/fixtures/http' . $path . '.php';
}

function sct_http_fixture_exists( $path ) {
	return file_exists( sct_get_http_fixture_path( $path ) );
}

function sct_get_http_fixture_array( $path ) {
	$realpath = sct_get_http_fixture_path( $path );

	$data = include $realpath;

	return $data;
}
