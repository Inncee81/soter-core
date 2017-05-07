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
	 * Determine whether a given key exists in the cache.
	 *
	 * @param  string $key Cache key.
	 *
	 * @return boolean
	 */
	public function contains( $key );

	/**
	 * Get the specified entry from the cache if it exists.
	 *
	 * @param  string $key Cache key.
	 *
	 * @return mixed       Value if it exists and has not expired, null otherwise.
	 */
	public function fetch( $key );

	/**
	 * Save a value to the cache.
	 *
	 * @param  string  $key      Cache key.
	 * @param  mixed   $data     The data to save.
	 * @param  integer $lifetime How long in seconds the entry is good for.
	 *
	 * @return boolean
	 */
	public function save( $key, $data, $lifetime );
}
