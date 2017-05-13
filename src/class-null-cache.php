<?php
/**
 * Null_Cache class.
 *
 * @package soter-core
 */

namespace Soter_Core;

/**
 * Defines the null cache class.
 */
class Null_Cache implements Cache_Interface {
	/**
	 * Flush the cache.
	 *
	 * @return boolean
	 */
	public function flush() {
		return false;
	}

	/**
	 * Flush expired entries from the cache.
	 *
	 * @return boolean
	 */
	public function flush_expired() {
		return false;
	}

	/**
	 * Remove an entry from the cache.
	 *
	 * @param  string $key The cache key.
	 *
	 * @return boolean
	 */
	public function forget( $key ) {
		return false;
	}

	/**
	 * Get an entry from the cache.
	 *
	 * @param  string $key The cache key.
	 *
	 * @return mixed       The cached value, null if it is not set.
	 */
	public function get( $key ) {
		return null;
	}

	/**
	 * Put an entry in the cache.
	 *
	 * @param  string  $key     The cache key.
	 * @param  mixed   $value   The cache value.
	 * @param  integer $seconds Time to cache expiration in seconds.
	 *
	 * @return boolean
	 */
	public function put( $key, $value, $seconds = 0 ) {
		return false;
	}
}
