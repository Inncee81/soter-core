<?php
/**
 * Cache provider interface.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Cache;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * Simple cache provider interface.
 */
interface Cache_Interface {
	/**
	 * Determine whether a given key exists in the cache.
	 *
	 * @param  string $key Cach key.
	 *
	 * @return bool
	 */
	public function contains( $key );

	/**
	 * Get the specified entry from the cache if it exists.
	 *
	 * @param  string $key Cache key.
	 *
	 * @return mixed       Value if it exists and has not expired, false otherwise.
	 */
	public function fetch( $key );

	/**
	 * Save a value to the cache.
	 *
	 * @param  string $key      Cache key.
	 * @param  mixed  $data     The data to save.
	 * @param  int    $lifetime How long in seconds the entry is good for.
	 *
	 * @return bool
	 */
	public function save( $key, $data, $lifetime );
}
