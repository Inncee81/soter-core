<?php
/**
 * WP_Package_Manager class.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the WP package manager class.
 */
class WP_Package_Manager implements Package_Manager_Interface {
	/**
	 * Get a list of all installed packages.
	 *
	 * @return Package[]
	 */
	public function get_packages() {
		return array_merge( $this->get_plugins(), $this->get_themes(), $this->get_wordpresses() );
	}

	/**
	 * Get a list of all installed plugins.
	 *
	 * @return Package[]
	 */
	public function get_plugins() {
		// Class is being used outside of WordPress. Throw instead?
		if ( ! defined( 'ABSPATH' ) ) {
			return array();
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$plugins = get_plugins();

		return array_map(
			'Soter_Core\\Package::from_plugin_array',
			array_keys( $plugins ),
			$plugins
		);
	}

	/**
	 * Get a list of all installed themes.
	 *
	 * @return Package[]
	 */
	public function get_themes() {
		// Class is being used outside of WordPress. Throw instead?
		if ( ! function_exists( 'wp_get_themes' ) ) {
			return array();
		}

		return array_values( array_map(
			'Soter_Core\\Package::from_theme_object',
			wp_get_themes()
		) );
	}

	/**
	 * Get a list of all installed WordPress versions (should only have 1 item).
	 *
	 * @return Package[]
	 */
	public function get_wordpresses() {
		// Class is being used outside of WordPress. Throw instead?
		if ( ! function_exists( 'get_bloginfo' ) ) {
			return array();
		}

		return array( Package::from_wordpress_env() );
	}
}
