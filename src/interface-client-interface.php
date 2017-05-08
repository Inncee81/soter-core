<?php
/**
 * Client_Interface interface.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the client interface.
 */
interface Client_Interface {
	/**
	 * Make a request against the plugins endpoint.
	 *
	 * @param  string $slug Plugin slug.
	 *
	 * @return Response_Interface
	 */
	public function plugins( $slug );

	/**
	 * Make a request against the themes endpoint.
	 *
	 * @param  string $slug Theme slug.
	 *
	 * @return Response_Interface
	 */
	public function themes( $slug );

	/**
	 * Make a request against the WordPresses endpoint.
	 *
	 * @param  string $slug WordPress slug (version stripped of all ".").
	 *
	 * @return Response_Interface
	 */
	public function wordpresses( $slug );
}
