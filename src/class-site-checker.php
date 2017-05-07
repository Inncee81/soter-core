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
class Site_Checker {
	/**
	 * Checker instance.
	 *
	 * @var Checker
	 */
	protected $checker;

	/**
	 * Cache of Package instances for all packages installed on site.
	 *
	 * @var Package[]
	 */
	protected $package_cache = [
		'plugins' => [],
		'themes' => [],
		'wordpresses' => [],
	];

	/**
	 * Class constructor.
	 *
	 * @param array   $ignored_packages List of package slugs to ignore in checks.
	 * @param Checker $checker          Checker instance.
	 */
	public function __construct( array $ignored_packages, Checker $checker ) {
		$this->ignored_packages = $ignored_packages;
		$this->checker = $checker;
	}

	/**
	 * Check a single package.
	 *
	 * @param  Package $package Package instance.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function check_package( Package $package ) {
		$vulnerabilities = $this->checker->check_package( $package );

		do_action( 'soter_core_check_package_complete', $package, $vulnerabilities );

		return $vulnerabilities;
	}

	/**
	 * Check multiple packages.
	 *
	 * @param  Api_Package[] $packages List of Package instances.
	 * @return Api_Package[]
	 */
	public function check_packages( array $packages ) {
		$vulnerabilities = [];

		foreach ( $packages as $package ) {
			$vulnerabilities = array_merge(
				$vulnerabilities,
				$this->check_package( $package )
			);
		}

		$vulnerabilities = array_unique( $vulnerabilities );

		do_action( 'soter_core_check_packages_complete', $vulnerabilities );

		return $vulnerabilities;
	}

	/**
	 * Check currently installed plugins.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function check_plugins() {
		return $this->check_packages( $this->get_plugins() );
	}

	/**
	 * Check all currently installed packages.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function check_site() {
		return $this->check_packages( $this->get_packages() );
	}

	/**
	 * Check currently installed themes.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function check_themes() {
		return $this->check_packages( $this->get_themes() );
	}

	/**
	 * Check current version of WordPress.
	 *
	 * @return Api_Vulnerability[]
	 */
	public function check_wordpress() {
		return $this->check_packages( $this->get_wordpress() );
	}

	/**
	 * Checker getter.
	 *
	 * @return Checker
	 */
	public function get_checker() {
		return $this->checker;
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
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( empty( $this->package_cache['plugins'] ) ) {
			$plugins = get_plugins();

			$this->package_cache['plugins'] = array_values(
				array_filter(
					array_map(
						function( $file, $plugin ) {
							$parts = explode( DIRECTORY_SEPARATOR, $file );
							$slug = reset( $parts );

							return new Package( $slug, 'plugin', $plugin['Version'] );
						},
						array_keys( $plugins ),
						$plugins
					),
					function( Package $plugin ) {
						return ! in_array(
							$plugin->get_slug(),
							$this->ignored_packages,
							true
						);
					}
				)
			);
		}

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
		if ( empty( $this->package_cache['themes'] ) ) {
			$this->package_cache['themes'] = array_values(
				array_filter(
					array_map(
						function( WP_Theme $theme ) {
							return new Package(
								$theme->stylesheet,
								'theme',
								$theme->get( 'Version' )
							);
						},
						wp_get_themes()
					),
					function( Package $theme ) {
						return ! in_array(
							$theme->get_slug(),
							$this->ignored_packages,
							true
						);
					}
				)
			);
		}

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
		if ( is_null( $this->package_cache['wordpresses'] ) ) {
			$version = get_bloginfo( 'version' );
			$slug = str_replace( '.', '', $version );

			$this->wordpress_cache = [
				new Package( $slug, 'wordpress', $version ),
			];
		}

		return $this->package_cache['wordpresses'];
	}
}
