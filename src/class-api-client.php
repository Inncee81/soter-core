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
	const BASE_URL = 'https://wpvulndb.com/api/v3';

	/**
	 * WPVulnDB APIv3 key.
	 *
	 * @var string
	 */
	protected $api_key;

	/**
	 * HTTP client.
	 *
	 * @var  Http_Interface
	 */
	protected $http;

	/**
	 * Map of package types to API routes.
	 *
	 * @var array
	 */
	protected $route_map = [
		'plugin'    => 'plugins',
		'theme'     => 'themes',
		'wordpress' => 'wordpresses',
	];

	/**
	 * Class constructor.
	 *
	 * @param string         $api_key WPVulnDB APIv3 key.
	 * @param Http_Interface $http    HTTP instance.
	 */
	public function __construct( string $api_key, Http_Interface $http ) {
		$this->api_key = $api_key;
		$this->http    = $http;
	}

	/**
	 * Check a package instance against the API.
	 *
	 * @param  Package $package Package instance.
	 *
	 * @return Response
	 */
	public function check( Package $package ) {
		list( $status, $headers, $body ) = $this->http->get(
			$this->build_url_for( $package ),
			[
				'headers' => [
					'Authorization' => "Token token={$this->api_key}",
				],
			]
		);

		return new Response( $status, $headers, $body, $package );
	}

	/**
	 * Build the API URL for a given package.
	 *
	 * @param  Package $package Package instance.
	 *
	 * @return string
	 */
	protected function build_url_for( Package $package ) {
		return self::BASE_URL . '/' . $this->get_route_for( $package ) . '/' . $package->get_slug();
	}

	/**
	 * Get the API route for a given package.
	 *
	 * @param  Package $package Package instance.
	 *
	 * @return string
	 *
	 * @throws InvalidArgumentException When $package is of an unsupported type.
	 */
	protected function get_route_for( Package $package ) {
		if ( ! isset( $this->route_map[ $package->get_type() ] ) ) {
			throw new InvalidArgumentException(
				"Unsupported package type [{$package->get_type()}]"
			);
		}

		return $this->route_map[ $package->get_type() ];
	}
}
