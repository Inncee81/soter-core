<?php

class Filesystem_Cache implements Soter_Core\Cache_Interface {
	public function flush() {
		return false;
	}

	public function flush_expired() {
		return false;
	}

	public function forget( $key ) {
		return false;
	}

	public function get( $key ) {
		if ( ! sct_http_fixture_exists( $this->get_file( $key ) ) ) {
			return null;
		}

		return sct_get_http_fixture_array( $this->get_file( $key ) );
	}

	public function put( $key, $value, $seconds = 0 ) {
		return false;
	}

	protected function get_file( $key ) {
		return substr( $key, 33 );
	}
}
