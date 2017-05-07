<?php
/**
 * Site_Checker class.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the site checker class.
 */
class Checker {
	protected $client;

	/**
	 * Cache of Package instances for all packages installed on site.
	 *
	 * @var Package[]
	 */
	protected $package_cache = [];

	protected $method_map = [
		'plugin' => 'plugins',
		'theme' => 'themes',
		'wordpress' => 'wordpresses',
	];

	public function __construct( Api_Client $client ) {
		$this->client = $client;
	}

	/**
	 * Check a single package.
	 *
	 * @param  Package $package Package instance.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function check_package( Package $package ) {
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
	 * @param  Api_Package[] $packages List of Package instances.
	 * @return Api_Package[]
	 */
	public function check_packages( array $packages, array $ignored = [] ) {
		if ( ! empty( $ignored ) ) {
			$packages = array_filter( $packages, function( Package $package ) {
				return ! in_array( $package->get_slug(), $ignored, true );
			} );
		}

		$vulnerabilities = [];

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
	 * @return Api_Vulnerability[]
	 */
	public function check_plugins( array $ignored = [] ) {
		return $this->check_packages( $this->get_plugins(), $ignored );
	}

	/**
	 * Check all currently installed packages.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function check_site( array $ignored = [] ) {
		return $this->check_packages( $this->get_packages(), $ignored );
	}

	/**
	 * Check currently installed themes.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function check_themes( array $ignored = [] ) {
		return $this->check_packages( $this->get_themes(), $ignored );
	}

	/**
	 * Check current version of WordPress.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function check_wordpress( array $ignored = [] ) {
		return $this->check_packages( $this->get_wordpress(), $ignored );
	}

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
	 * @return Package[]
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
	 * @return Package[]
	 */
	public function get_plugins() {
		// Class is being used outside of WordPress.
		if ( ! defined( 'ABSPATH' ) ) {
			return [];
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
				$parts = explode( DIRECTORY_SEPARATOR, $file );
				$slug = array_shift( $parts );

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
	 * @return Package[]
	 */
	public function get_themes() {
		// Class is being used outside of WordPress.
		if ( ! function_exists( 'wp_get_themes' ) ) {
			return [];
		}

		if ( isset( $this->package_cache['themes'] ) ) {
			return $this->package_cache['themes'];
		}

		$this->package_cache['themes'] = array_map( function( WP_Theme $theme ) {
			return new Package(
				$theme->stylesheet,
				'theme',
				$theme->get( 'Version ' )
			);
		}, wp_get_themes() );

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
	 * @return Package[]
	 */
	public function get_wordpress() {
		// Class is being used outside of WordPress.
		if ( ! function_exists( 'get_bloginfo' ) ) {
			return [];
		}

		if ( isset( $this->package_cache['wordpress'] ) ) {
			return $this->package_cache['wordpress'];
		}

		$version = get_bloginfo( 'version' );
		$slug = str_replace( '.', '', $version );

		$this->wordpress_cache = [ new Package( $slug, 'wordpress', $version ) ];

		return $this->package_cache['wordpresses'];
	}

	protected function get_client_method( Package $package ) {
		if ( isset( $this->method_map[ $package->get_type() ] ) ) {
			return $this->method_map[ $package->get_type() ];
		}

		throw new \InvalidArgumentException(
			"Unsupported package type [{$package->get_type()}]"
		);
	}
}
