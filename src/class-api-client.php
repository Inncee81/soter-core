<?php
/**
 * Api_Client class.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the API client class.
 */
class Api_Client {
	const BASE_URL = 'https://wpvulndb.com/api/v2/';

	/**
	 * Cache provider.
	 *
	 * @var  Cache_Interface
	 */
	protected $cache;

	/**
	 * Cache duration in seconds.
	 *
	 * @var integer
	 */
	protected $cache_duration = 60 * 60;

	/**
	 * HTTP client.
	 *
	 * @var  Http_Interface
	 */
	protected $http;

	/**
	 * Class constructor.
	 *
	 * @param Http_Interface  $http  HTTP instance.
	 * @param Cache_Interface $cache Cache instance.
	 */
	public function __construct( Http_Interface $http, Cache_Interface $cache ) {
		$this->http = $http;
		$this->cache = $cache;
	}

	/**
	 * Cache duration getter.
	 *
	 * @return integer
	 */
	public function get_cache_duration() {
		return $this->cache_duration;
	}

	/**
	 * Makes a request to the plugins endpoint.
	 *
	 * @param  string $slug Plugin slug.
	 *
	 * @return Api_Response
	 */
	public function plugins( $slug ) {
		return $this->get_and_cache( 'plugins/' . $slug );
	}

	/**
	 * Cache duration setter.
	 *
	 * @param integer $seconds Cache duration in seconds.
	 */
	public function set_cache_duration( $seconds ) {
		$this->cache_duration = absint( $seconds );
	}

	/**
	 * Make a request to the themes endpoint.
	 *
	 * @param  string $slug Theme slug.
	 *
	 * @return Api_Response
	 */
	public function themes( $slug ) {
		return $this->get_and_cache( 'themes/' . $slug );
	}

	/**
	 * Make a request to the WordPresses endpoint.
	 *
	 * @param  string $slug WordPress slug (aka version stripped of "." characters).
	 *
	 * @return Api_Response
	 */
	public function wordpresses( $slug ) {
		return $this->get_and_cache( 'wordpresses/' . $slug );
	}

	/**
	 * Retrieve response from cache if it exists otherwise make a GET request.
	 *
	 * @param  string $endpoint API endpoint.
	 *
	 * @return Api_Response
	 */
	protected function get_and_cache( $endpoint ) {
		$url = self::BASE_URL . (string) $endpoint;

		try {
			list( $status, $headers, $body ) = $this->remember(
				$this->get_cache_key( $url ),
				$this->get_cache_duration(),
				function() use ( $url ) {
					return $this->http->get( $url );
				}
			);
		} catch ( \RuntimeException $e ) {
			// @todo
			$status = 418;
			$headers = array();
			$body = '';
		}

		return new Api_Response( $status, $headers, $body );
	}

	/**
	 * Get the cache key for a given request.
	 *
	 * @param  string $url API $url request is being made against.
	 *
	 * @return string
	 */
	protected function get_cache_key( $url ) {
		return 'api_response_' . $url;
	}

	/**
	 * Returns a value from the cache if it exists, otherwise sets it to the value
	 * returned from the provided callback.
	 *
	 * @param  string   $key      Cache key.
	 * @param  integer  $duration Cache duration in seconds.
	 * @param  \Closure $callback Callback used to generate value for caching.
	 *
	 * @return mixed
	 */
	protected function remember( $key, $duration, \Closure $callback ) {
		if ( $this->cache->contains( $key ) ) {
			return $this->cache->fetch( $key );
		}

		$value = $callback();

		$this->cache->save( $key, $value, $duration );

		return $value;
	}
}
