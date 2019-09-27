<?php
/**
 * Cached_Http_Client class.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the cached HTTP client class.
 */
class Cached_Http_Client implements Http_Interface {
	// @todo Consider options for automatically syncing version with the latest github release.
	const KEY = 'soter_core:v0.3.0:%s';

	/**
	 * Cache instance.
	 *
	 * @var Cache_Interface
	 */
	protected $cache;

	/**
	 * HTTP instance.
	 *
	 * @var Http_Interface
	 */
	protected $http;

	/**
	 * Class constructor.
	 *
	 * @param Http_Interface  $http  HTTP instance.
	 * @param Cache_Interface $cache Cache instance.
	 */
	public function __construct( Http_Interface $http, Cache_Interface $cache ) {
		$this->http  = $http;
		$this->cache = $cache;
	}

	/**
	 * Check for a cached response, make a GET request to the given URL as necessary.
	 *
	 * @param  string $url  The URL to make a GET request against.
	 * @param  array  $args Additional request args.
	 *
	 * @return array
	 */
	public function get( $url, array $args = [] ) {
		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
		$key = sprintf( static::KEY, sha1( "http:get:{$url}:" . serialize( $args ) ) );

		$value = $this->cache->get( $key );

		if ( ! is_null( $value ) ) {
			return $value;
		}

		$value = $this->http->get( $url );

		$this->cache->put( $key, $value );

		return $value;
	}
}
