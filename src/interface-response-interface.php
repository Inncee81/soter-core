<?php
/**
 * Response_Interface interface.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the response interface.
 */
interface Response_Interface {
	/**
	 * Get all vulnerabilities from the response.
	 *
	 * @return Vulnerability_Interface[]
	 */
	public function get_vulnerabilities();

	/**
	 * Get all vulnerabilities that affect a given package version.
	 *
	 * @param  string|null $version Package version to check.
	 *
	 * @return Vulnerability_Interface[]
	 */
	public function get_vulnerabilities_by_version( $version = null );
}
