<?php
/**
 * WPScan API client.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * The actual WPScan API client implementation.
 */
class Api_Client {
	const BASE_URL = 'https://wpvulndb.com/api/v2/';

	/**
	 * Cache provider.
	 *
	 * @var  Cache_Interface
	 */
	protected $cache;

	protected $cache_duration = 60 * 60;

	/**
	 * Http client.
	 *
	 * @var  Http_Interface
	 */
	protected $http;

	/**
	 * Class constructor.
	 *
	 * @param Http_Interface  $http  Http instance.
	 * @param Cache_Interface $cache Cache instance.
	 */
	public function __construct( Http_Interface $http, Cache_Interface $cache ) {
		$this->http = $http;
		$this->cache = $cache;
	}

	/**
	 * Makes a request to the plugins endpoint.
	 *
	 * @param  string $slug Plugin slug.
	 *
	 * @return Response
	 */
	public function plugins( $slug ) {
		return $this->get_and_cache( 'plugins/' . $slug );
	}

	/**
	 * Make a request to the themes endpoint.
	 *
	 * @param  string $slug Theme slug.
	 *
	 * @return Response
	 */
	public function themes( $slug ) {
		return $this->get_and_cache( 'themes/' . $slug );
	}

	/**
	 * Make a request to the WordPresses endpoint.
	 *
	 * @param  string $slug WordPress slug (aka version stripped of "." characters).
	 *
	 * @return Response
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
			$headers = [];
			$body = '';
		}

		return new Api_Response( $status, $headers, $body );
	}

	public function get_cache_duration() {
		return $this->cache_duration;
	}

	public function set_cache_duration( $seconds ) {
		$this->cache_duration = absint( $seconds );
	}

	protected function get_cache_key( $url ) {
		return 'api_response_' . $url;
	}

	protected function remember( $key, $duration, \Closure $callback ) {
		if ( $this->cache->contains( $key ) ) {
			return $this->cache->fetch( $key );
		}

		$value = $callback();

		$this->cache->save( $key, $value, $duration );

		return $value;
	}
}
