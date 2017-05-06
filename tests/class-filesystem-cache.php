<?php

class Filesystem_Cache implements Soter_Core\Cache_Interface {
	use Gets_Http_Fixtures_Trait;

	public function contains( $key ) {
		return $this->http_fixture_exists( $this->get_file( $key ) );
	}

	public function fetch( $key ) {
		return static::get_http_fixture_array(
			$this->get_http_fixture_path( $this->get_file( $key ) )
		);
	}

	public function save( $key, $data, $lifetime ) {
		return false;
	}

	protected function get_file( $key ) {
		return substr( $key, 33 );
	}
}
