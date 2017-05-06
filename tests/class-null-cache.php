<?php

class Null_Cache implements Soter_Core\Cache_Interface {
	public function contains( $key ) {
		return false;
	}

	public function fetch( $key ) {
		return false;
	}

	public function save( $key, $data, $lifetime ) {
		return false;
	}
}
