<?php
/**
 * Integrates with the Api client to check an entire site.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * This class checks all plugins, themes and core against the WPScan API.
 */
class Package_Checker {
	/**
	 * WPScan API Client.
	 *
	 * @var Api_Client
	 */
	protected $client;

	/**
	 * Class constructor.
	 *
	 * @param Api_Client $client Api client instance.
	 */
	public function __construct( Api_Client $client ) {
		$this->client = $client;
	}

	/**
	 * Get the Api_Client instance.
	 *
	 * @return Api_Client
	 */
	public function get_client() {
		return $this->client;
	}

	/**
	 * Run a check on a specific package.
	 *
	 * @param  Package $package Theme/plugin/WordPress package.
	 *
	 * @return Api_Vulnerability[]
	 */
	protected function check_package( Package $package ) {
		$client_method = $package->get_type();

		$response = $this->client->{$client_method}( $package->get_slug() );

		if ( is_wp_error( $response ) ) {
			return [];
		}

		return $response->vulnerabilities_by_version( $package->get_version() );
	}

	/**
	 * Run a check on multiple packages.
	 *
	 * @param  Package[] $packages List of packages to check.
	 *
	 * @return Api_Vulnerability[]
	 */
	protected function check_packages( array $packages ) {
		$vulnerabilities = [];

		foreach ( $packages as $package ) {
			$vulnerabilities = array_merge(
				$vulnerabilities,
				$this->check_package( $package )
			);
		}

		return array_unique( $vulnerabilities );
	}
}
