<?php
/**
 * Integrates with the Api client to check an entire site.
 *
 * @package soter-core
 */

namespace Soter_Core;

use Closure;
use WP_Theme;

/**
 * This class checks all plugins, themes and core against the WPScan API.
 */
class Checker {
	/**
	 * Cache of plugin package objects.
	 *
	 * @var Package[]
	 */
	protected $plugin_cache;

	/**
	 * Cache of theme package objects.
	 *
	 * @var Package[]
	 */
	protected $theme_cache;

	/**
	 * Cache of WordPress package objects.
	 *
	 * @var Package[]
	 */
	protected $wordpress_cache;

	/**
	 * WPScan API Client.
	 *
	 * @var Api_Client
	 */
	protected $client;

	/**
	 * List of ignored plugins.
	 *
	 * @var string[]
	 */
	protected $ignored_plugins;

	/**
	 * List of ignored themes.
	 *
	 * @var string[]
	 */
	protected $ignored_themes;

	/**
	 * Class constructor.
	 *
	 * @param string[]   $ignored_plugins List of plugins to ignore.
	 * @param string[]   $ignored_themes  List of themes to ignore.
	 * @param Api_Client $client          Api client instance.
	 */
	public function __construct(
		array $ignored_plugins,
		array $ignored_themes,
		Api_Client $client
	) {
		$this->client = $client;
		$this->ignored_plugins = $ignored_plugins;
		$this->ignored_themes = $ignored_themes;
	}

	/**
	 * Checks all installed plugins.
	 *
	 * @return WPScan\Vulnerability[]
	 */
	public function check_plugins() {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return $this->check_packages( $this->get_plugins() );
	}

	/**
	 * Checks all installed packages of the site.
	 *
	 * @return WPScan\Vulnerability[]
	 */
	public function check_site() {
		return $this->check_packages( $this->get_packages() );
	}

	/**
	 * Check all installed themes.
	 *
	 * @return WPScan\Vulnerability[]
	 */
	public function check_themes() {
		return $this->check_packages( $this->get_themes() );
	}

	/**
	 * Check the installed version of WordPress.
	 *
	 * @return WPScan\Vulnerability[]
	 */
	public function check_wordpress() {
		return $this->check_packages( $this->get_wordpress() );
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
	 * Get count of all isntalled packages.
	 *
	 * @return int
	 */
	public function get_package_count() {
		return count( $this->get_packages() );
	}

	/**
	 * Get list of all installed packages.
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
	 * Get count of all installed plugins.
	 *
	 * @return int
	 */
	public function get_plugin_count() {
		return count( $this->get_plugins() );
	}

	/**
	 * Get list of all installed plugins.
	 *
	 * @return Package[]
	 */
	public function get_plugins() {
		if ( is_null( $this->plugin_cache ) ) {
			$plugins = get_plugins();

			$this->plugin_cache = array_values(
				array_filter(
					array_map(
						function( $file, $plugin ) {
							$parts = explode( DIRECTORY_SEPARATOR, $file );
							$slug = reset( $parts );

							return new Package( $slug, 'plugins', $plugin['Version'] );
						},
						array_keys( $plugins ),
						$plugins
					),
					function( Package $plugin ) {
						return ! in_array(
							$plugin->get_slug(),
							$this->ignored_plugins,
							true
						);
					}
				)
			);
		}

		return $this->plugin_cache;
	}

	/**
	 * Get count of all installed themes.
	 *
	 * @return int
	 */
	public function get_theme_count() {
		return count( $this->get_themes() );
	}

	/**
	 * Get list of all installed themes.
	 *
	 * @return Package[]
	 */
	public function get_themes() {
		if ( is_null( $this->theme_cache ) ) {
			$this->theme_cache = array_values(
				array_filter(
					array_map(
						function( WP_Theme $theme ) {
							return new Package(
								$theme->stylesheet,
								'themes',
								$theme->get( 'Version' )
							);
						},
						wp_get_themes()
					),
					function( Package $theme ) {
						return ! in_array(
							$theme->get_slug(),
							$this->ignored_themes,
							true
						);
					}
				)
			);
		}

		return $this->theme_cache;
	}

	/**
	 * Get count of installed WordPresses.
	 *
	 * @return int
	 */
	public function get_wordpress_count() {
		return count( $this->get_wordpress() );
	}

	/**
	 * Get list of all installed WordPresses.
	 *
	 * @return Package[]
	 */
	public function get_wordpress() {
		if ( is_null( $this->wordpress_cache ) ) {
			$version = get_bloginfo( 'version' );
			$slug = str_replace( '.', '', $version );

			$this->wordpress_cache = [
				new Package( $slug, 'wordpresses', $version ),
			];
		}

		return $this->wordpress_cache;
	}

	/**
	 * Run a check on a specific package.
	 *
	 * @param  Package $package Theme/plugin/WordPress package.
	 *
	 * @return WPScan\Vulnerability[]
	 */
	protected function check_package( Package $package ) {
		$client_method = $package->get_type();

		$response = $this->client->{$client_method}( $package->get_slug() );

		if ( is_wp_error( $response ) ) {
			return [];
		}

		$vulnerabilities = $response->vulnerabilities_by_version(
			$package->get_version()
		);

		do_action( 'soter_check_package_complete', $package, $vulnerabilities );

		return $vulnerabilities;
	}

	/**
	 * Run a check on multiple packages.
	 *
	 * @param  Package[] $packages List of packages to check.
	 *
	 * @return WPScan\Vulnerability[]
	 */
	protected function check_packages( array $packages ) {
		$vulnerabilities = [];

		foreach ( $packages as $package ) {
			$vulnerabilities = array_merge(
				$vulnerabilities,
				$this->check_package( $package )
			);
		}

		$vulnerabilities = array_unique( $vulnerabilities );

		do_action( 'soter_check_packages_complete', $vulnerabilities );

		return $vulnerabilities;
	}
}
