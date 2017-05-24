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
	const MAX_KEY_LENGTH = 172;

	/**
	 * WordPress database instance.
	 *
	 * @var wpdb
	 */
	protected $db;

	/**
	 * Default cache entry lifetime.
	 *
	 * @var integer
	 */
	protected $default_lifetime;

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
	 * @param wpdb         $db               WordPress database instance.
	 * @param string       $prefix           Cache prefix string.
	 * @param null|integer $default_lifetime Default cache entry lifetime.
	 *
	 * @throws \InvalidArgumentException When length of $prefix exceeds max allowed.
	 */
	public function __construct( wpdb $db, $prefix = '', $default_lifetime = null ) {
		// 40 for length of sha1, additional 1 for "_" separator.
		if ( self::MAX_KEY_LENGTH - 40 - 1 < strlen( $prefix ) ) {
			throw new \InvalidArgumentException( sprintf(
				'Provided prefix [%s, length of %s] exceeds maximum length of %s',
				$prefix,
				strlen( $prefix ),
				self::MAX_KEY_LENGTH
			) );
		}

		$this->db = $db;
		$this->prefix = (string) $prefix;
		$this->default_lifetime = max( 0, intval( $default_lifetime ) );
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
			WHERE option_name LIKE %s
			OR option_name LIKE %s";

		$prefix = $this->cache_prefix();
		$option = $this->db->esc_like( "_transient_{$prefix}" ) . '%';
		$timeout = $this->db->esc_like( "_transient_timeout_{$prefix}" ) . '%';

		$count = $this->db->query( $this->db->prepare( $sql, $option, $timeout ) );

		if ( false === $count ) {
			return false;
		}

		wp_cache_flush();

		return true;
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
		$prefix = $this->cache_prefix();
		$option = "_transient_{$prefix}";
		$timeout = "_transient_timeout_{$prefix}";
		$length = strlen( $option ) + 1;

		$sql = "DELETE a, b FROM {$this->db->options} a, {$this->db->options} b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( %s, SUBSTRING( a.option_name, %d ) )
			AND b.option_value < %d";

		$count = $this->db->query( $this->db->prepare(
			$sql,
			$this->db->esc_like( $option ) . '%',
			$this->db->esc_like( $timeout ) . '%',
			$timeout,
			$length,
			$now
		) );

		if ( false === $count ) {
			return false;
		}

		wp_cache_flush();

		return true;
	}

	/**
	 * Remove an entry from the cache.
	 *
	 * @param  string $key The cache key.
	 *
	 * @return boolean
	 */
	public function forget( $key ) {
		return delete_transient( $this->item_key( $key ) );
	}

	/**
	 * Get an entry from the cache.
	 *
	 * @param  string $key The cache key.
	 *
	 * @return mixed       The cached value, null if it is not set.
	 */
	public function get( $key ) {
		$value = get_transient( $this->item_key( $key ) );

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
	 * Get the default cache entry lifetime.
	 *
	 * @return integer
	 */
	public function get_default_lifetime() {
		return $this->default_lifetime;
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
	 * @param  string       $key      The cache key.
	 * @param  mixed        $value    The cache value.
	 * @param  null|integer $lifetime Time to cache expiration in seconds.
	 *
	 * @return boolean
	 */
	public function put( $key, $value, $lifetime = null ) {
		if ( is_null( $value ) || 0 === $lifetime ) {
			return $this->forget( $key );
		}

		$lifetime = $this->item_lifetime( $lifetime );

		if ( 0 > $lifetime ) {
			return $this->forget( $key );
		}

		return set_transient( $this->item_key( $key ), $value, $lifetime );
	}

	/**
	 * Generate the actual cache key.
	 *
	 * @param  string $key The user defined cache key.
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException When length of $key is 0.
	 */
	protected function item_key( $key ) {
		$key = (string) $key;

		if ( ! isset( $key[0] ) ) {
			throw new \InvalidArgumentException(
				'Cache key length must be greater than zero'
			);
		}

		$prefix = $this->cache_prefix();
		$new_key = $prefix . $key;

		if ( strlen( $new_key ) <= self::MAX_KEY_LENGTH ) {
			return $new_key;
		}

		return $prefix . hash( 'sha1', $key );
	}

	/**
	 * Generate the actual cache lifetime.
	 *
	 * @param  null|integer $lifetime The cache lifetime.
	 *
	 * @return integer
	 */
	protected function item_lifetime( $lifetime ) {
		if ( is_null( $lifetime ) ) {
			return $this->default_lifetime;
		}

		$lifetime = intval( $lifetime );

		return 0 < $lifetime ? $lifetime : -1;
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
