<?php
/**
 * Composer_Package_Manager class.
 *
 * @package soter-core
 */

namespace Soter_Core;

use SSNepenthe\ComposerUtilities\WordPress\Lock;
use SSNepenthe\ComposerUtilities\WordPress\Package as LockPackage;

/**
 * Defines the composer package manager class.
 */
class Composer_Package_Manager implements Package_Manager_Interface {
	/**
	 * WordPress lock file instance.
	 *
	 * @var Lock
	 */
	protected $lock;

	/**
	 * Cache of Package instances for all packages installed on site.
	 *
	 * @var Package[]
	 */
	protected $package_cache = array();

	/**
	 * Class constructor.
	 *
	 * @param Lock $lock WordPress lock file instance.
	 */
	public function __construct( Lock $lock ) {
		$this->lock = $lock;
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
		if ( isset( $this->package_cache['plugins'] ) ) {
			return $this->package_cache['plugins'];
		}

		$this->package_cache['plugins'] = array_map(
			function( LockPackage $plugin ) {
				list( $_, $slug ) = explode( '/', $plugin->name() );

				return new Package( $slug, Package::TYPE_PLUGIN, $plugin->version() );
			},
			$this->lock->plugin_packages()
		);

		return $this->package_cache['plugins'];
	}

	/**
	 * Get a list of all installed themes.
	 *
	 * @return Package[]
	 */
	public function get_themes() {
		if ( isset( $this->package_cache['themes'] ) ) {
			return $this->package_cache['themes'];
		}

		$this->package_cache['themes'] = array_map(
			function( LockPackage $theme ) {
				list( $_, $slug ) = explode( '/', $theme->name() );

				return new Package( $slug, Package::TYPE_THEME, $theme->version() );
			},
			$this->lock->theme_packages()
		);

		return $this->package_cache['themes'];
	}

	/**
	 * Get a list of all installed WordPress versions (should only ever have 1 item).
	 *
	 * @return Package[]
	 */
	public function get_wordpresses() {
		if ( isset( $this->package_cache['wordpresses'] ) ) {
			return $this->package_cache['wordpresses'];
		}

		$this->package_cache['wordpresses'] = array_map(
			function( LockPackage $wordpress ) {
				$slug = str_replace( '.', '', $wordpress->version() );

				return new Package( $slug, Package::TYPE_WORDPRESS, $wordpress->version() );
			},
			$this->lock->core_packages()
		);

		return $this->package_cache['wordpresses'];
	}
}
