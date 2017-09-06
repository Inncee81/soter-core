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
		return array_map(
			function( LockPackage $plugin ) {
				list( $_, $slug ) = explode( '/', $plugin->name() );

				return new Package( $slug, Package::TYPE_PLUGIN, $plugin->version() );
			},
			$this->lock->plugin_packages()
		);
	}

	/**
	 * Get a list of all installed themes.
	 *
	 * @return Package[]
	 */
	public function get_themes() {
		return array_map(
			function( LockPackage $theme ) {
				list( $_, $slug ) = explode( '/', $theme->name() );

				return new Package( $slug, Package::TYPE_THEME, $theme->version() );
			},
			$this->lock->theme_packages()
		);
	}

	/**
	 * Get a list of all installed WordPress versions (should only ever have 1 item).
	 *
	 * @return Package[]
	 */
	public function get_wordpresses() {
		return array_map(
			function( LockPackage $wordpress ) {
				$slug = str_replace( '.', '', $wordpress->version() );

				return new Package( $slug, Package::TYPE_WORDPRESS, $wordpress->version() );
			},
			$this->lock->core_packages()
		);
	}
}
