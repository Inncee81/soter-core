<?php

class Filesystem_Http_Client implements Soter_Core\Http_Interface {
	public function get( $url ) {
		$parsed = parse_url( $url );

		return sct_get_http_fixture_array( $parsed['path'] );
	}
}
