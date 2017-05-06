<?php

trait Gets_Http_Fixtures_Trait {
	protected static function get_http_fixture_path( $path ) {
		return __DIR__ . '/fixtures/http' . $path . '.php';
	}

	protected static function http_fixture_exists( $path ) {
		return file_exists( static::get_http_fixture_path( $path ) );
	}

	protected static function get_http_fixture_array( $path ) {
		$realpath = static::get_http_fixture_path( $path );

		$data = include $realpath;

		return $data;
	}
}
