<?php
/**
 * Site_Checker class.
 *
 * @package soter-core
 */

namespace Soter_Core;

use WP_Theme;

/**
 * Defines the site checker class.
 */
class Checker implements Checker_Interface {
	/**
	 * API client instance.
	 *
	 * @var Client_Interface
	 */
	protected $client;

	/**
	 * Cache of Package instances for all packages installed on site.
	 *
	 * @var Package_Interface[]
	 */
	protected $package_cache = array();

	/**
	 * Map of package types to API client methods.
	 *
	 * @var string[]
	 */
	protected $method_map = array(
		'plugin' => 'plugins',
		'theme' => 'themes',
		'wordpress' => 'wordpresses',
	);

	/**
	 * Class constructor.
	 *
	 * @param Client_Interface $client API client instance.
	 */
	public function __construct( Client_Interface $client ) {
		$this->client = $client;
	}

	/**
	 * Check a single package.
	 *
	 * @param  Package_Interface $package Package instance.
	 *
	 * @return Vulnerability_Interface[]
	 */
	public function check_package( Package_Interface $package ) {
		$client_method = $this->get_client_method( $package );

		$response = $this->client->{$client_method}( $package->get_slug() );

		$vulnerabilities = $response->get_vulnerabilities_by_version(
			$package->get_version()
		);

		if ( function_exists( 'do_action' ) ) {
			do_action(
				'soter_core_check_package_complete',
				$package,
				$vulnerabilities
			);
		}

		return $vulnerabilities;
	}

	/**
	 * Check multiple packages.
	 *
	 * @param  Package_Interface[] $packages List of Package instances.
	 * @param  string[]            $ignored  List of package slugs to ignore.
	 *
	 * @return Vulnerability_Interface[]
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

		$vulnerabilities = array();

		foreach ( $packages as $package ) {
			$vulnerabilities = array_merge(
				$vulnerabilities,
				$this->check_package( $package )
			);
		}

		$vulnerabilities = array_unique( $vulnerabilities );

		if ( function_exists( 'do_action' ) ) {
			do_action( 'soter_core_check_packages_complete', $vulnerabilities );
		}

		return $vulnerabilities;
	}

	/**
	 * Check currently installed plugins.
	 *
	 * @param string[] $ignored List of plugin slugs to ignore.
	 *
	 * @return Vulnerability_Interface[]
	 */
	public function check_plugins( array $ignored = array() ) {
		return $this->check_packages( $this->get_plugins(), $ignored );
	}

	/**
	 * Check all currently installed packages.
	 *
	 * @param string[] $ignored List of package slugs to ignore.
	 *
	 * @return Vulnerability_Interface[]
	 */
	public function check_site( array $ignored = array() ) {
		return $this->check_packages( $this->get_packages(), $ignored );
	}

	/**
	 * Check currently installed themes.
	 *
	 * @param string[] $ignored List of theme slugs to ignore.
	 *
	 * @return Vulnerability_Interface[]
	 */
	public function check_themes( array $ignored = array() ) {
		return $this->check_packages( $this->get_themes(), $ignored );
	}

	/**
	 * Check current version of WordPress.
	 *
	 * @param string[] $ignored List of WordPress slugs to ignore.
	 *
	 * @return Vulnerability_Interface[]
	 */
	public function check_wordpress( array $ignored = array() ) {
		return $this->check_packages( $this->get_wordpress(), $ignored );
	}

	/**
	 * Get the API client instance.
	 *
	 * @return Client_Interface
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
		return count( $this->get_packages() );
	}

	/**
	 * Get a list of all installed packages.
	 *
	 * @return Package_Interface[]
	 */
	public function get_packages() {
		return array_merge(
			$this->get_plugins(),
			$this->get_themes(),
			$this->get_wordpress()
		);
	}

	/**
	 * Get total count of all installed plugins.
	 *
	 * @return integer
	 */
	public function get_plugin_count() {
		return count( $this->get_plugins() );
	}

	/**
	 * Get a list of all installed plugins.
	 *
	 * @return Package_Interface[]
	 */
	public function get_plugins() {
		// Class is being used outside of WordPress.
		if ( ! defined( 'ABSPATH' ) ) {
			return array();
		}

		if ( isset( $this->package_cache['plugins'] ) ) {
			return $this->package_cache['plugins'];
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();

		$this->package_cache['plugins'] = array_map(
			function( $file, array $plugin ) {
				if ( false === strpos( $file, '/' ) ) {
					$slug = basename( $file, '.php' );
				} else {
					$slug = dirname( $file );
				}

				return new Package( $slug, 'plugin', $plugin['Version'] );
			},
			array_keys( $plugins ),
			$plugins
		);

		return $this->package_cache['plugins'];
	}

	/**
	 * Get total count of all installed themes.
	 *
	 * @return integer
	 */
	public function get_theme_count() {
		return count( $this->get_themes() );
	}

	/**
	 * Get a list of all installed themes.
	 *
	 * @return Package_Interface[]
	 */
	public function get_themes() {
		// Class is being used outside of WordPress.
		if ( ! function_exists( 'wp_get_themes' ) ) {
			return array();
		}

		if ( isset( $this->package_cache['themes'] ) ) {
			return $this->package_cache['themes'];
		}

		$themes = array_map( function( WP_Theme $theme ) {
			return new Package(
				$theme->get_stylesheet(),
				'theme',
				$theme->get( 'Version' )
			);
		}, wp_get_themes() );

		$this->package_cache['themes'] = array_values( $themes );

		return $this->package_cache['themes'];
	}

	/**
	 * Get total count of all installed WordPress versions (should always be 1).
	 *
	 * @return integer
	 */
	public function get_wordpress_count() {
		return count( $this->get_wordpress() );
	}

	/**
	 * Get a list of all installed WordPress versions (should only have 1 item).
	 *
	 * @return Package_Interface[]
	 */
	public function get_wordpress() {
		// Class is being used outside of WordPress.
		if ( ! function_exists( 'get_bloginfo' ) ) {
			return array();
		}

		if ( isset( $this->package_cache['wordpresses'] ) ) {
			return $this->package_cache['wordpresses'];
		}

		$version = get_bloginfo( 'version' );
		$slug = str_replace( '.', '', $version );

		$this->package_cache['wordpresses'] = array(
			new Package( $slug, 'wordpress', $version ),
		);

		return $this->package_cache['wordpresses'];
	}

	/**
	 * Get the appropriate API client method for a given package.
	 *
	 * @param  Package_Interface $package Package instance.
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException When there is no matching method for a type.
	 */
	protected function get_client_method( Package_Interface $package ) {
		if ( isset( $this->method_map[ $package->get_type() ] ) ) {
			return $this->method_map[ $package->get_type() ];
		}

		throw new \InvalidArgumentException(
			"Unsupported package type [{$package->get_type()}]"
		);
	}
}
