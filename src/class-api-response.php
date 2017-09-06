<?php
/**
 * Api_Response class.
 *
 * @package soter-core
 */

namespace Soter_Core;

use DateTime;

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
	 * Package for which this response was generated.
	 *
	 * @var Package
	 */
	protected $package;

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
	 * @param Package  $package Package instance for which the reponse is generated.
	 */
	public function __construct( $status, array $headers, $body, Package $package ) {
		$this->status = intval( $status );
		$this->headers = $headers;
		$this->body = (string) $body;
		$this->package = $package;

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
	 * Package getter.
	 *
	 * @return Package
	 */
	public function get_package() {
		return $this->package;
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
	 * @return Vulnerabilities
	 */
	public function get_vulnerabilities() {
		return $this->get_vulnerabilities_by_version();
	}

	/**
	 * Get all vulnerabilities that affect a particular package version.
	 *
	 * @param  string|null $version Package version.
	 *
	 * @return Vulnerabilities
	 */
	public function get_vulnerabilities_by_version( $version = null ) {
		if ( is_null( $version ) ) {
			return $this->data['vulnerabilities'];
		}

		$version = (string) $version;

		return $this->data['vulnerabilities']->filter(
			function( Api_Vulnerability $vulnerability ) use ( $version ) {
				return $vulnerability->affects_version( $version );
			}
		);
	}

	/**
	 * Get all vulnerabilities that affect the current version of the attached package.
	 *
	 * @return Vulnerabilities
	 */
	public function get_vulnerabilities_for_current_version() {
		return $this->get_vulnerabilities_by_version( $this->get_package()->get_version() );
	}

	/**
	 * Check whether any vulnerabilities exist on this response.
	 *
	 * @return boolean
	 */
	public function has_vulnerabilities() {
		return $this->data['vulnerabilities']->not_empty();
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
		if ( ! $this->is_success() ) {
			return $this->generate_error( 'Non-200 status code received' );
		}

		if ( ! $this->is_json() ) {
			return $this->generate_error( 'Received non-JSON response' );
		}

		$decoded = json_decode( $this->body, true );

		if ( null === $decoded || JSON_ERROR_NONE !== json_last_error() ) {
			return $this->generate_error( 'Response does not appear to be valid JSON' );
		}

		$data = current( $decoded );
		$slug = key( $decoded );

		// Slug will be slug for plugins and themes, version for WordPress.
		if ( $slug !== $this->package->get_slug() && $slug !== $this->package->get_version() ) {
			return $this->generate_error( 'Response slug does not match package slug' );
		}

		if ( isset( $data['last_updated'] ) ) {
			$data['last_updated'] = new DateTime(
				$data['last_updated']
			);
		}

		$vulnerabilities = new Vulnerabilities();

		if ( isset( $data['vulnerabilities'] ) && is_array( $data['vulnerabilities'] ) ) {
			$vulnerabilities->add_many(
				array_map( function( array $vulnerability ) {
					return new Api_Vulnerability( $this->package, $vulnerability );
				}, $data['vulnerabilities'] )
			);
		}

		$data['vulnerabilities'] = $vulnerabilities;

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
			'vulnerabilities' => new Vulnerabilities(),
		);
	}

	/**
	 * Check whether this response appears to be JSON.
	 *
	 * @return boolean
	 */
	protected function is_json() {
		return isset( $this->headers['content-type'] )
			&& false !== strpos( $this->headers['content-type'], 'application/json' );
	}

	/**
	 * Check whether this response has a success status code.
	 *
	 * @return boolean
	 */
	protected function is_success() {
		return 200 === $this->status;
	}
}
