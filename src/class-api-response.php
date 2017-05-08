<?php
/**
 * Api_Response class.
 *
 * @package soter-core
 */

namespace Soter_Core;

use DateTime;
use RuntimeException;

/**
 * Defines the API response class.
 */
class Api_Response {
	/**
	 * Raw response body.
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
	 * @var integer
	 */
	protected $status;

	/**
	 * Class constructor.
	 *
	 * @param int      $status  Response status code.
	 * @param string[] $headers List of response headers with lowercase keys.
	 * @param string   $body    Response body.
	 */
	public function __construct( $status, array $headers, $body ) {
		$this->status = intval( $status );
		$this->headers = $headers;
		$this->body = (string) $body;

		$this->data = $this->generate_data();
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
	 * Raw body getter.
	 *
	 * @return string
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * Data getter.
	 *
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Headers getter.
	 *
	 * @return string[]
	 */
	public function get_headers() {
		return $this->headers;
	}

	/**
	 * Status code getter.
	 *
	 * @return integer
	 */
	public function get_status() {
		return $this->status;
	}

	/**
	 * Vulnerabilities getter.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function get_vulnerabilities() {
		if ( ! $this->has_vulnerabilities() ) {
			return array();
		}

		return $this->data['vulnerabilities'];
	}

	/**
	 * Get all vulnerabilities that affect a particular package version.
	 *
	 * @param  string|null $version Package version.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function get_vulnerabilities_by_version( $version = null ) {
		if ( is_null( $version ) ) {
			return $this->data['vulnerabilities'];
		}

		if ( ! $this->has_vulnerabilities() ) {
			return array();
		}

		$version = (string) $version;
		$vulnerabilities = array();

		foreach ( $this->data['vulnerabilities'] as $vulnerability ) {
			if ( $vulnerability->affects_version( $version ) ) {
				$vulnerabilities[] = $vulnerability;
			}
		}

		return $vulnerabilities;
	}

	/**
	 * Check whether any vulnerabilities exist on this response.
	 *
	 * @return boolean
	 */
	public function has_vulnerabilities() {
		return isset( $this->data['vulnerabilities'] )
			&& is_array( $this->data['vulnerabilities'] )
			&& count( $this->data['vulnerabilities'] );
	}

	/**
	 * Check whether this instance represents an error response.
	 *
	 * @return boolean
	 */
	public function is_error() {
		return isset( $this->data['error'] );
	}

	/**
	 * Generates the data array.
	 *
	 * @return array
	 */
	protected function generate_data() {
		// May want to revisit - Non-200 does not automatically mean error.
		if ( 200 !== $this->status ) {
			return $this->generate_error( 'Non-200 status code received' );
		}

		if (
			! isset( $this->headers['content-type'] )
			|| false === strpos( $this->headers['content-type'], 'application/json' )
		) {
			return $this->generate_error( 'Received non-JSON response' );
		}

		$decoded = json_decode( $this->body, true );

		if ( null === $decoded || JSON_ERROR_NONE !== json_last_error() ) {
			return $this->generate_error(
				'Response does not appear to be valid JSON'
			);
		}

		$data = current( $decoded );
		$data['slug'] = key( $decoded );
		$slug = $data['slug'];

		if ( isset( $data['last_updated'] ) ) {
			$data['last_updated'] = new DateTime(
				$data['last_updated']
			);
		}

		$data['vulnerabilities'] = array_map(
			function( array $vulnerability ) use ( $slug ) {
				return new Api_Vulnerability( $slug, $vulnerability );
			},
			$data['vulnerabilities']
		);

		return $data;
	}

	/**
	 * Generates a data array representing an error.
	 *
	 * @param  string|null $message Error message.
	 *
	 * @return array
	 */
	protected function generate_error( $message = null ) {
		// Consider using status message as default message here?
		$message = is_null( $message ) ? 'Invalid endpoint' : (string) $message;

		return array(
			'error' => array(
				'code' => $this->status,
				'message' => $message,
			),
		);
	}
}
