<?php
/**
 * WPScan API response wrapper.
 *
 * @package soter-core
 */

namespace Soter_Core;

use DateTime;
use RuntimeException;

/**
 * This class provides a simple wrapper for responses from the WPScan API.
 */
class Api_Response {
	/**
	 * Response body.
	 *
	 * @var  string
	 */
	protected $body;

	/**
	 * JSON decoded response.
	 *
	 * @var  array
	 */
	protected $data;

	/**
	 * List of response headers.
	 *
	 * @var string[]
	 */
	protected $headers;

	/**
	 * Response status code.
	 *
	 * @var int
	 */
	protected $status;

	/**
	 * Cache of vulnerabilities by version.
	 *
	 * @var array
	 */
	protected $version_cache = [];

	/**
	 * Class constructor.
	 *
	 * @param int      $status  Response status code.
	 * @param string[] $headers List of response headers.
	 * @param string   $body    Response body.
	 */
	public function __construct( $status, array $headers, $body ) {
		$this->status = intval( $status );
		$this->headers = $headers;
		$this->body = (string) $body;
		$this->data = $this->generate_data_array();
	}

	/**
	 * Magic getter, proxies all property requests to the data array.
	 *
	 * @param  string $key Property name.
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}

		return null;
	}

	/**
	 * Check whether this instance represents a non-200 response.
	 *
	 * @return boolean
	 */
	public function is_error() {
		return isset( $this->data['error'] );
	}

	/**
	 * Vulnerabilities getter.
	 *
	 * @return Vulnerability[]
	 */
	public function vulnerabilities() {
		if ( $this->is_error() ) {
			return [];
		}

		return $this->data['vulnerabilities'];
	}

	/**
	 * Get all vulnerabilities that affect a particular package version.
	 *
	 * @param  string|null $version Package version.
	 *
	 * @return Vulnerability[]
	 */
	public function vulnerabilities_by_version( $version = null ) {
		if ( $this->is_error() || empty( $this->data['vulnerabilities'] ) ) {
			return [];
		}

		if ( is_null( $version ) ) {
			return $this->data['vulnerabilities'];
		}

		$version = (string) $version;

		if ( isset( $this->version_cache[ $version ] ) ) {
			return $this->version_cache[ $version ];
		}

		$vulnerabilities = [];

		foreach ( $this->data['vulnerabilities'] as $vulnerability ) {
			if ( $vulnerability->affects_version( $version ) ) {
				$vulnerabilities[] = $vulnerability;
			}
		}

		return $this->version_cache[ $version ] = $vulnerabilities;
	}

	/**
	 * Generates the data array based on status code and whether response is JSON.
	 *
	 * @return array
	 */
	protected function generate_data_array() {
		// May want to revisit - Non-200 does not automatically mean error.
		$response_ok = 200 === $this->status;
		$is_json = isset( $this->headers['content-type'] )
			&& false !== strpos(
				$this->headers['content-type'],
				'application/json'
			);

		if ( $response_ok && $is_json ) {
			try {
				return $this->generate_ok_data_array();
			} catch ( RuntimeException $e ) {
				return $this->generate_error_data_array( null, $e->getMessage() );
			}
		}

		return $this->generate_error_data_array();
	}

	/**
	 * Generates a data array representing an error.
	 *
	 * @param  int|null    $code    Error code.
	 * @param  string|null $message Error message.
	 *
	 * @return array
	 */
	protected function generate_error_data_array( $code = null, $message = null ) {
		$code = is_null( $code ) ? $this->status : intval( $code );
		// Consider using status message as default message here?
		$message = is_null( $message ) ? 'Invalid endpoint' : (string) $message;

		return [
			'error' => compact( 'code', 'message' ),
		];
	}

	/**
	 * Generates a data array representing a valid response.
	 *
	 * @return array
	 *
	 * @throws  RuntimeException When response cannot be JSON decoded.
	 */
	protected function generate_ok_data_array() {
		$decoded = json_decode( $this->body, true );

		if ( null === $decoded || JSON_ERROR_NONE !== json_last_error() ) {
			throw new RuntimeException(
				'Response does not appear to be valid JSON'
			);
		}

		$data = current( $decoded );
		$data['slug'] = key( $decoded );

		if ( isset( $data['last_updated'] ) ) {
			$data['last_updated'] = new DateTime(
				$data['last_updated']
			);
		}

		$data['vulnerabilities'] = array_map(
			function( array $vulnerability ) {
				return new Api_Vulnerability( $vulnerability );
			},
			$data['vulnerabilities']
		);

		return $data;
	}
}
