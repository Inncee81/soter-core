<?php

class Filesystem_Cache implements Soter_Core\Cache_Interface {
	public function contains( $key ) {
		return sct_http_fixture_exists( $this->get_file( $key ) );
	}

	public function fetch( $key ) {
		return sct_get_http_fixture_array( $this->get_file( $key ) );
	}

	public function save( $key, $data, $lifetime ) {
		return false;
	}

	protected function get_file( $key ) {
		return substr( $key, 33 );
	}
}
