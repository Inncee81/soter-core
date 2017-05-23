<?php

namespace Soter_Core;

class Cached_Http_Client implements Http_Interface {
	protected $cache;
	protected $http;

	public function __construct( Http_Interface $http, Cache_Interface $cache ) {
		$this->http = $http;
		$this->cache = $cache;
	}

	public function get( $url ) {
		$key = "soter_core:http:get:{$url}";

		$value = $this->cache->get( $key );

		if ( ! is_null( $value ) ) {
			return $value;
		}

		$value = $this->http->get( $url );

		// @todo
		$this->cache->put( $key, $value );

		return $value;
	}
}
