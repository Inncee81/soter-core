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
class Checker {
	const METHOD_MAP = [
		'plugin' => 'plugins',
		'theme' => 'themes',
		'wordpress' => 'wordpresses',
	];

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
	public function check_package( Package $package ) {
		$client_method = $this->get_client_method( $package );

		$response = $this->client->{$client_method}( $package->get_slug() );

		return $response->get_vulnerabilities_by_version( $package->get_version() );
	}

	/**
	 * Run a check on multiple packages.
	 *
	 * @param  Package[] $packages List of packages to check.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function check_packages( array $packages ) {
		$vulnerabilities = [];

		foreach ( $packages as $package ) {
			$vulnerabilities = array_merge(
				$vulnerabilities,
				$this->check_package( $package )
			);
		}

		return array_unique( $vulnerabilities );
	}

	protected function get_client_method( Package $package ) {
		if ( isset( self::METHOD_MAP[ $package->get_type() ] ) ) {
			return self::METHOD_MAP[ $package->get_type() ];
		}

		throw new \InvalidArgumentException(
			"Unsupported package type [{$package->get_type()}]"
		);
	}
}
