<?php
/**
 * WP HTTP API Implementation.
 *
 * @package soter-core
 */

namespace Soter_Core;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class creates a simple HTTP GET client using the WP HTTP API.
 */
class WP_Http_Client implements Http_Interface {
	/**
	 * The user agent to use when making requests.
	 *
	 * @var string
	 */
	protected $user_agent;

	/**
	 * Class constructor.
	 *
	 * @param string $user_agent The user agent to use when making requests.
	 */
	public function __construct( $user_agent ) {
		$this->user_agent = (string) $user_agent;
	}

	/**
	 * Send a GET request to the given URL.
	 *
	 * @param  string $url The URL to make a request against.
	 *
	 * @return array
	 */
	public function get( $url ) {
		$args = [ 'user-agent' => $this->user_agent ];

		$response = wp_safe_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		return [
			wp_remote_retrieve_response_code( $response ),
			wp_remote_retrieve_headers( $response )->getAll(),
			wp_remote_retrieve_body( $response ),
		];
	}
}
