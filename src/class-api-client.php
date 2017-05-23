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
class Api_Client implements Client_Interface {
	const BASE_URL = 'https://wpvulndb.com/api/v2/';

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
	 */
	public function __construct( Http_Interface $http ) {
		$this->http = $http;
	}

	/**
	 * Makes a request to the plugins endpoint.
	 *
	 * @param  string $slug Plugin slug.
	 *
	 * @return Response_Interface
	 */
	public function plugins( $slug ) {
		return $this->get( 'plugins/' . $slug );
	}

	/**
	 * Make a request to the themes endpoint.
	 *
	 * @param  string $slug Theme slug.
	 *
	 * @return Response_Interface
	 */
	public function themes( $slug ) {
		return $this->get( 'themes/' . $slug );
	}

	/**
	 * Make a request to the WordPresses endpoint.
	 *
	 * @param  string $slug WordPress slug (aka version stripped of "." characters).
	 *
	 * @return Response_Interface
	 */
	public function wordpresses( $slug ) {
		return $this->get( 'wordpresses/' . $slug );
	}

	/**
	 * Make a get request against the given endpoint.
	 *
	 * @param  string $endpoint API endpoint.
	 *
	 * @return Response_Interface
	 */
	protected function get( $endpoint ) {
		$url = self::BASE_URL . (string) $endpoint;

		list( $status, $headers, $body ) = $this->http->get( $url );

		return new Api_Response( $status, $headers, $body );
	}
}
