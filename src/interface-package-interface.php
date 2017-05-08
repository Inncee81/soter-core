<?php
/**
 * Package_Interface interface.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the package interface.
 */
interface Package_Interface {
	/**
	 * Get the package slug.
	 *
	 * @return string
	 */
	public function get_slug();

	/**
	 * Get the package type.
	 *
	 * @return string
	 */
	public function get_type();

	/**
	 * Get the package version.
	 *
	 * @return string
	 */
	public function get_version();
}
