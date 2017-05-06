<?php
/**
 * A data container for packages.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * This class acts as a data container for packages to provide some minmal
 * normalization between themes, plugins and WordPress core.
 */
class Package {
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
}
