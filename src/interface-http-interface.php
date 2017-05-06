<?php
/**
 * HTTP Client Interface.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Simple HTTP GET client interface.
 */
interface Http_Interface {
	/**
	 * Send a GET request to a given URL.
	 *
	 * @param  string $url The URL to make a request against.
	 *
	 * @return array       The array contents should match the following:
	 *                         [0] int    Response code.
	 *                         [1] array  Response headers, keys all in lowercase.
	 *                         [2] string Response body.
	 *
	 * @throws  \RuntimeException When there is an error.
	 */
	public function get( $url );
}
