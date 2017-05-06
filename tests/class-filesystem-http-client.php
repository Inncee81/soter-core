<?php

class Filesystem_Http_Client implements Soter_Core\Http_Interface {
	use Gets_Http_Fixtures_Trait;

	public function get( $url ) {
		$parsed = parse_url( $url );

		$fixture_path = $this->get_http_fixture_path( $parsed['path'] );

		return static::get_http_fixture_array( $fixture_path );
	}
}
