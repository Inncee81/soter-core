<?php
/**
 * WP_Package_Manager class.
 *
 * @package soter-core
 */

namespace Soter_Core;

use WP_Theme;

/**
 * Defines the WP package manager class.
 */
class WP_Package_Manager implements Package_Manager_Interface {
	/**
	 * Cache of Package instances for all packages installed on site.
	 *
	 * @var Package_Interface[]
	 */
	protected $package_cache = array();

	/**
	 * Get a list of all installed packages.
	 *
	 * @return Package_Interface[]
	 */
	public function get_packages() {
		return array_merge(
			$this->get_plugins(),
			$this->get_themes(),
			$this->get_wordpresses()
		);
	}

	/**
	 * Get a list of all installed plugins.
	 *
	 * @return Package_Interface[]
	 */
	public function get_plugins() {
		// Class is being used outside of WordPress. Throw instead?
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
	 * Get a list of all installed themes.
	 *
	 * @return Package_Interface[]
	 */
	public function get_themes() {
		// Class is being used outside of WordPress. Throw instead?
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
	 * Get a list of all installed WordPress versions (should only have 1 item).
	 *
	 * @return Package_Interface[]
	 */
	public function get_wordpresses() {
		// Class is being used outside of WordPress. Throw instead?
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
}
