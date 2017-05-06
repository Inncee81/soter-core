<?php
/**
 * WP Transient Cache implementation.
 *
 * @package soter
 */

namespace SSNepenthe\Soter\Cache;

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

/**
 * This class wraps the transient cache API to implement Cache_Interface.
 */
class WP_Transient_Cache implements Cache_Interface {
	/**
	 * Prefix to use for all cache keys.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Class constructor.
	 *
	 * Prior to WP 4.4, max transient ID length is 45 characters. MD5 accounts
	 * for 32 of those, this ensures our generated ID maxes out at 45.
	 *
	 * @param string $prefix Prefix to use for all cache keys.
	 */
	public function __construct( $prefix ) {
		$this->prefix = substr( (string) $prefix, 0, 12 ) . '_';
	}

	/**
	 * Check whether a given entry exists in the cache.
	 *
	 * @param  string $key Cache key.
	 *
	 * @return bool
	 */
	public function contains( $key ) {
		return false !== get_transient( $this->generate_id( $key ) );
	}

	/**
	 * Get the specified entry from the cache if it exists.
	 *
	 * @param  string $key Cache key.
	 *
	 * @return mixed
	 */
	public function fetch( $key ) {
		return get_transient( $this->generate_id( $key ) );
	}

	/**
	 * Save a value to the cache.
	 *
	 * @param  string $key      Cache key.
	 * @param  mixed  $data     The data to save.
	 * @param  int    $lifetime How long in seconds the entry is good for.
	 *
	 * @return bool
	 */
	public function save( $key, $data, $lifetime = 0 ) {
		return set_transient( $this->generate_id( $key ), $data, $lifetime );
	}

	/**
	 * Generates an ID to be used as the cache key.
	 *
	 * @param  string $key Cache key provided by user.
	 *
	 * @return string
	 */
	protected function generate_id( $key ) {
		return $this->prefix . hash( 'md5', (string) $key );
	}
}
