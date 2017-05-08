<?php
/**
 * Checker_Interface interface.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the checker interface.
 */
interface Checker_Interface {
	/**
	 * Check a single package.
	 *
	 * @param  Package_Interface $package Package instance.
	 *
	 * @return Vulnerability_Interface[]
	 */
	public function check_package( Package_Interface $package );

	/**
	 * Check multiple packages.
	 *
	 * @param  Package_Interface[] $packages List of package instances.
	 * @param  string[]            $ignored  List of package slugs to ignore.
	 *
	 * @return Vulnerability_Interface
	 */
	public function check_packages( array $packages, array $ignored );
}
