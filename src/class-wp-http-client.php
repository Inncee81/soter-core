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
	 * Default user-agent string to use for HTTP requests.
	 *
	 * @var string|null
	 */
	protected $user_agent;

	/**
	 * Class constructor.
	 *
	 * @param string|null $user_agent Default user-agent string to use for HTTP requests.
	 */
	public function __construct( string $user_agent = null ) {
		if ( null !== $user_agent ) {
			$this->user_agent = (string) $user_agent;
		}
	}

	/**
	 * Send a GET request to the given URL.
	 *
	 * @param string $url  The URL to make a request against.
	 * @param array  $args Additional request args.
	 *
	 * @return array
	 *
	 * @throws \RuntimeException When there is an error.
	 */
	public function get( $url, array $args = [] ) {
		if ( ! array_key_exists( 'user-agent', $args ) && null !== $this->user_agent ) {
			$args['user-agent'] = $this->user_agent;
		}

		$response = wp_safe_remote_get( (string) $url, $args );

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
