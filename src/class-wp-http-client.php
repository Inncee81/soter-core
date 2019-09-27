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
		$response = wp_safe_remote_get(
			(string) $url,
			array(
				'user-agent' => $this->user_agent,
			)
		);

		if ( is_wp_error( $response ) ) {
			/**
			 * Not sure how to best get psalm to understand that $response is a WP_Error instance...
			 *
			 * @psalm-suppress PossiblyInvalidMethodCall
			 */
			throw new \RuntimeException( $response->get_error_message() );
		}

		// phpcs:ignore Generic.Commenting.DocComment.MissingShort
		/** @psalm-var array $response */
		$headers = wp_remote_retrieve_headers( $response );

		return array(
			wp_remote_retrieve_response_code( $response ),
			$headers instanceof \Requests_Utility_CaseInsensitiveDictionary
				? $headers->getAll()
				: $headers,
			wp_remote_retrieve_body( $response ),
		);
	}
}
