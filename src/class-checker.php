<?php
/**
 * Checker class.
 *
 * @package soter-core
 */

namespace Soter_Core;

use Closure;

/**
 * Defines the checker class.
 */
class Checker {
	/**
	 * API client instance.
	 *
	 * @var Api_Client
	 */
	protected $client;

	/**
	 * Package manager instance.
	 *
	 * @var Package_Manager_Interface
	 */
	protected $package_manager;

	/**
	 * List of anonymous functions to call after each package check.
	 *
	 * @var array
	 */
	protected $post_check_callbacks = array();

	/**
	 * Class constructor.
	 *
	 * @param Api_Client                $client          API client instance.
	 * @param Package_Manager_Interface $package_manager Package manager instance.
	 */
	public function __construct( Api_Client $client, Package_Manager_Interface $package_manager ) {
		$this->client          = $client;
		$this->package_manager = $package_manager;
	}

	/**
	 * Add a callback to be fired after each package check.
	 *
	 * @param Closure $callback Callback.
	 *
	 * @return void
	 */
	public function add_post_check_callback( Closure $callback ) {
		$this->post_check_callbacks[] = $callback;
	}

	/**
	 * Check a single package.
	 *
	 * @param  Package $package Package instance.
	 *
	 * @return Vulnerabilities
	 */
	public function check_package( Package $package ) {
		$response        = $this->client->check( $package );
		$vulnerabilities = $response->get_vulnerabilities_for_current_version();

		$this->do_post_check_callbacks( $vulnerabilities, $response );

		return $vulnerabilities;
	}

	/**
	 * Check multiple packages.
	 *
	 * @param  Package[] $packages List of Package instances.
	 * @param  string[]  $ignored  List of package slugs to ignore.
	 *
	 * @return Vulnerabilities
	 */
	public function check_packages( array $packages, array $ignored = array() ) {
		if ( ! empty( $ignored ) ) {
			$packages = array_filter(
				$packages,
				function( Package $package ) use ( $ignored ) {
					return ! in_array( $package->get_slug(), $ignored, true );
				}
			);
		}

		$vulnerabilities = new Vulnerabilities();

		foreach ( $packages as $package ) {
			$vulnerabilities->merge_in( $this->check_package( $package ) );
		}

		return $vulnerabilities;
	}

	/**
	 * Check currently installed plugins.
	 *
	 * @param string[] $ignored List of plugin slugs to ignore.
	 *
	 * @return Vulnerabilities
	 */
	public function check_plugins( array $ignored = array() ) {
		return $this->check_packages( $this->package_manager->get_plugins(), $ignored );
	}

	/**
	 * Check all currently installed packages.
	 *
	 * @param string[] $ignored List of package slugs to ignore.
	 *
	 * @return Vulnerabilities
	 */
	public function check_site( array $ignored = array() ) {
		return $this->check_packages( $this->package_manager->get_packages(), $ignored );
	}

	/**
	 * Check currently installed themes.
	 *
	 * @param string[] $ignored List of theme slugs to ignore.
	 *
	 * @return Vulnerabilities
	 */
	public function check_themes( array $ignored = array() ) {
		return $this->check_packages( $this->package_manager->get_themes(), $ignored );
	}

	/**
	 * Check current version of WordPress.
	 *
	 * @param string[] $ignored List of WordPress slugs to ignore.
	 *
	 * @return Vulnerabilities
	 */
	public function check_wordpress( array $ignored = array() ) {
		return $this->check_packages( $this->package_manager->get_wordpresses(), $ignored );
	}

	/**
	 * Trigger all post-check callbacks registered on this instance.
	 *
	 * @param  Vulnerabilities $vulnerabilities List of vulnerabilities.
	 * @param  Response        $response        Response instance.
	 *
	 * @return void
	 */
	public function do_post_check_callbacks(
		Vulnerabilities $vulnerabilities,
		Response $response
	) {
		if ( empty( $this->post_check_callbacks ) ) {
			return;
		}

		foreach ( $this->post_check_callbacks as $callback ) {
			$callback( $vulnerabilities, $response );
		}
	}

	/**
	 * Get the API client instance.
	 *
	 * @return Api_Client
	 */
	public function get_client() {
		return $this->client;
	}

	/**
	 * Get total count of all installed packages.
	 *
	 * @return integer
	 */
	public function get_package_count() {
		return count( $this->package_manager->get_packages() );
	}

	/**
	 * Package manager getter.
	 *
	 * @return Package_Manager_Interface
	 */
	public function get_package_manager() {
		return $this->package_manager;
	}

	/**
	 * Get total count of all installed plugins.
	 *
	 * @return integer
	 */
	public function get_plugin_count() {
		return count( $this->package_manager->get_plugins() );
	}

	/**
	 * Get total count of all installed themes.
	 *
	 * @return integer
	 */
	public function get_theme_count() {
		return count( $this->package_manager->get_themes() );
	}

	/**
	 * Get total count of all installed WordPress versions (should always be 1).
	 *
	 * @return integer
	 */
	public function get_wordpress_count() {
		return count( $this->package_manager->get_wordpresses() );
	}
}
