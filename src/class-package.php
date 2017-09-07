<?php
/**
 * Package class.
 *
 * @package soter-core
 */

namespace Soter_Core;

use WP_Theme;

/**
 * Defines the package class.
 *
 * This class acts as a data container for packages to provide some minmal
 * normalization between themes, plugins and WordPress core.
 */
class Package {
	const TYPE_PLUGIN = 'plugin';
	const TYPE_THEME = 'theme';
	const TYPE_WORDPRESS = 'wordpress'; // WPCS: spelling ok.

	/**
	 * Package slug.
	 *
	 * @var string
	 */
	protected $slug;

	/**
	 * Package type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Package version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Class constructor
	 *
	 * @param string $slug    Package slug.
	 * @param string $type    Package type.
	 * @param string $version Package version.
	 */
	public function __construct( $slug, $type, $version ) {
		$this->slug = (string) $slug;
		$this->type = (string) $type;
		$this->version = (string) $version;
	}

	/**
	 * Slug getter.
	 *
	 * @return string
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Type getter.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Version getter.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Create a package from a plugin array.
	 *
	 * @param  string $file    Plugin file path relative to plugins dir.
	 * @param  array  $headers List of plugin headers.
	 *
	 * @return static
	 */
	public static function from_plugin_array( $file, array $headers ) {
		if ( false === strpos( $file, '/' ) ) {
			$slug = basename( $file, '.php' );
		} else {
			$slug = dirname( $file );
		}

		return new static( $slug, static::TYPE_PLUGIN, $headers['Version'] );
	}

	/**
	 * Create a package from a WP_Theme object.
	 *
	 * @param  WP_Theme $theme Theme object.
	 *
	 * @return static
	 */
	public static function from_theme_object( WP_Theme $theme ) {
		return new static( $theme->get_stylesheet(), static::TYPE_THEME, $theme->get( 'Version' ) );
	}

	/**
	 * Create a package from the current WordPress environment.
	 *
	 * @return static
	 */
	public static function from_wordpress_env() {
		$version = get_bloginfo( 'version' );
		$slug = str_replace( '.', '', $version );

		return new static( $slug, static::TYPE_WORDPRESS, $version );
	}
}
