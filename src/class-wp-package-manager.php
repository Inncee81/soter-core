<?php
/**
 * WP_Package_Manager class.
 *
 * @package soter-core
 */

namespace Soter_Core;

use RuntimeException;

/**
 * Defines the WP package manager class.
 */
class WP_Package_Manager implements Package_Manager_Interface {
	/**
	 * Class constructor.
	 *
	 * @throws RuntimeException When instantiated outside of a WordPress context.
	 */
	public function __construct() {
		if ( ! defined( 'ABSPATH' ) ) {
			throw new RuntimeException(
				sprintf( '%s can only be used within a WordPress context', __CLASS__ )
			);
		}
	}
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
		return array( Package::from_wordpress_env() );
	}
}
