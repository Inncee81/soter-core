<?php
/**
 * WP_Http_Client class.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the WP HTTP client class.
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
	 *
	 * @throws \RuntimeException When there is an error.
	 */
	public function get( $url ) {
		$args = [
			'user-agent' => $this->user_agent,
		];
		$response = wp_safe_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			throw new \RuntimeException( $response->get_error_message() );
		}

		return [
			wp_remote_retrieve_response_code( $response ),
			wp_remote_retrieve_headers( $response )->getAll(),
			wp_remote_retrieve_body( $response ),
		];
	}
}
