<?php

class Filesystem_Http_Client implements Soter_Core\Http_Interface {
	use Gets_Http_Fixtures_Trait;

	public function get( $url ) {
		$parsed = parse_url( $url );

		return static::get_http_fixture_array( $parsed['path'] );
	}
}
