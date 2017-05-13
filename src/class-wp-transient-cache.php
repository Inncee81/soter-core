<?php
/**
 * WP_Transient_Cache class.
 *
 * @package soter-core
 */

namespace Soter_Core;

use wpdb;

/**
 * Defines the WP transient cache class.
 */
class WP_Transient_Cache implements Cache_Interface {
	/**
	 * WordPress database instance.
	 *
	 * @var wpdb
	 */
	protected $db;

	/**
	 * Cache prefix.
	 *
	 * @var string
	 */
	protected $prefix;

	/**
	 * Class constructor.
	 *
	 * Requires WP >= 4.4 for transient key length of 172 characters.
	 *
	 * @param wpdb   $db     WordPress database instance.
	 * @param string $prefix Cache prefix string.
	 */
	public function __construct( wpdb $db, $prefix = '' ) {
		$this->db = $db;

		// SHA1 plus "_" take 41 characters which leaves us with 131 for our prefix.
		if ( 131 < strlen( $prefix ) ) {
			$prefix = substr( $prefix, 0, 131 );
		}

		$this->prefix = $prefix;
	}

	/**
	 * Flush the cache.
	 *
	 * Fails automatically when site is using external object cache.
	 *
	 * @return boolean
	 */
	public function flush() {
		if ( wp_using_ext_object_cache() ) {
			return false;
		}

		$sql = "DELETE FROM {$this->db->options}
			WHERE option_name LIKE %s";

		$count = $this->db->query( $this->db->prepare(
			$sql,
			$this->db->esc_like( "_transient_{$this->cache_prefix()}" ) . '%'
		) );

		return (bool) $count;
	}

	/**
	 * Flush expired entries from the cache.
	 *
	 * Fails automatically when site is using external object cache.
	 *
	 * @return boolean
	 */
	public function flush_expired() {
		if ( wp_using_ext_object_cache() ) {
			return false;
		}

		$now = time();
		$transient_prefix = "_transient_{$this->cache_prefix()}";
		$timeout_prefix = "_transient_timeout_{$this->cache_prefix()}";
		$length = strlen( $transient_prefix ) + 1;

		$sql = "DELETE a, b FROM {$this->db->options} a, {$this->db->options} b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( %s, SUBSTRING( a.option_name, %d ) )
			AND b.option_value < %d";

		$count = $this->db->query( $this->db->prepare(
			$sql,
			$this->db->esc_like( $transient_prefix ) . '%',
			$this->db->esc_like( $timeout_prefix ) . '%',
			$timeout_prefix,
			$length,
			$now
		) );

		return (bool) $count;
	}

	/**
	 * Remove an entry from the cache.
	 *
	 * @param  string $key The cache key.
	 *
	 * @return boolean
	 */
	public function forget( $key ) {
		return delete_transient( $this->cache_key( $key ) );
	}

	/**
	 * Get an entry from the cache.
	 *
	 * @param  string $key The cache key.
	 *
	 * @return mixed       The cached value, null if it is not set.
	 */
	public function get( $key ) {
		$value = get_transient( $this->cache_key( $key ) );

		return false === $value ? null : $value;
	}

	/**
	 * Get the WordPress database instance.
	 *
	 * @return wpdb
	 */
	public function get_db() {
		return $this->db;
	}

	/**
	 * Get the raw cache prefix.
	 *
	 * @return string
	 */
	public function get_prefix() {
		return $this->prefix;
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
		return set_transient(
			$this->cache_key( $key ),
			$value,
			max( 0, intval( $seconds ) )
		);
	}

	/**
	 * Generate the actual cache key.
	 *
	 * @param  string $key The user defined cache key.
	 *
	 * @return string
	 */
	protected function cache_key( $key ) {
		return $this->cache_prefix() . hash( 'sha1', strval( $key ) );
	}

	/**
	 * Generate the actual cache prefix.
	 *
	 * @return string
	 */
	protected function cache_prefix() {
		return $this->prefix ? "{$this->prefix}_" : '';
	}
}
