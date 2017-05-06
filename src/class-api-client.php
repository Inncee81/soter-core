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
	 * @return \WP_Error|Response
	 */
	protected function get_and_cache( $endpoint ) {
		$url = self::BASE_URL . (string) $endpoint;
		$cache_key = 'api_response_' . $url;

		if ( $this->cache->contains( $cache_key ) ) {
			list( $status, $headers, $body ) = $this->cache->fetch( $cache_key );

			return new Api_Response( $status, $headers, $body );
		}

		$response = $this->http->get( $url );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$this->cache->save( $cache_key, $response, HOUR_IN_SECONDS );

		list( $status, $headers, $body ) = $response;

		return new Api_Response( $status, $headers, $body );
	}
}
