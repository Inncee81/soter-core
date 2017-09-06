<?php
/**
 * Api_Client class.
 *
 * @package soter-core
 */

namespace Soter_Core;

use InvalidArgumentException;

/**
 * Defines the API client class.
 */
class Api_Client {
	const BASE_URL = 'https://wpvulndb.com/api/v2';

	/**
	 * HTTP client.
	 *
	 * @var  Http_Interface
	 */
	protected $http;

	protected $route_map = [
		'plugin' => 'plugins',
		'theme' => 'themes',
		'wordpress' => 'wordpresses',
	];

	/**
	 * Class constructor.
	 *
	 * @param Http_Interface $http  HTTP instance.
	 */
	public function __construct( Http_Interface $http ) {
		$this->http = $http;
	}

	public function check( Package $package ) {
		list( $status, $headers, $body ) = $this->http->get( $this->build_url_for( $package ) );

		return new Api_Response( $status, $headers, $body, $package );
	}

	protected function build_url_for( Package $package ) {
		return self::BASE_URL . '/' . $this->get_route_for( $package ) . '/' . $package->get_slug();
	}

	protected function get_route_for( Package $package ) {
		if ( ! isset( $this->route_map[ $package->get_type() ] ) ) {
			throw new InvalidArgumentException(
				"Unsupported package type [{$package->get_type()}]"
			);
		}

		return $this->route_map[ $package->get_type() ];
	}
}
