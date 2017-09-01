<?php
/**
 * Cache_Interface interface.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the cache interface.
 */
interface Cache_Interface {
	/**
	 * Get an entry from the cache.
	 *
	 * @param  string $key The cache key.
	 *
	 * @return mixed       The cached value, null if it is not set.
	 */
	public function get( $key );

	/**
	 * Put an entry in the cache.
	 *
	 * @param  string       $key      The cache key.
	 * @param  mixed        $value    The cache value.
	 * @param  null|integer $lifetime Time to cache expiration in seconds.
	 *
	 * @return boolean
	 */
	public function put( $key, $value, $lifetime = null );
}
