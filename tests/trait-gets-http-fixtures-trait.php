<?php

trait Gets_Http_Fixtures_Trait {
	protected function get_http_fixture_path( $path ) {
		return __DIR__ . '/fixtures/http' . $path . '.php';
	}

	protected function http_fixture_exists( $path ) {
		return file_exists( $this->get_http_fixture_path( $path ) );
	}

	protected static function get_http_fixture_array( $path ) {
		$data = include $path;

		return $data;
	}
}
