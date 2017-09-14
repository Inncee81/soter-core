<?php
/**
 * Vulnerabilities class.
 *
 * @package soter-core
 */

namespace Soter_Core;

use Closure;
use Countable;
use ArrayIterator;
use IteratorAggregate;

/**
 * Vulnerabilities collection class.
 *
 * The do_action() function does not play nicely/consistently with arrays of objects, so this class
 * is used as a list object to simplify interactions with vulnerabilities from within listeners.
 *
 * @link https://github.com/ssnepenthe/soter/issues/21
 */
class Vulnerabilities implements IteratorAggregate, Countable {
	/**
	 * List of vulnerability objects.
	 *
	 * @var array
	 */
	protected $vulnerabilities = array();

	/**
	 * Class constructor.
	 *
	 * @param array $vulnerabilities List of vulnerability objects.
	 */
	public function __construct( array $vulnerabilities = [] ) {
		$this->add_many( $vulnerabilities );
	}

	/**
	 * Add a vulnerability to the list.
	 *
	 * @param Vulnerability $vulnerability Vulnerability object.
	 *
	 * @return $this
	 */
	public function add( Vulnerability $vulnerability ) {
		// Keyed by ID to prevent duplicates.
		$this->vulnerabilities[ $vulnerability->id ] = $vulnerability;

		return $this;
	}

	/**
	 * Add many vulnerability objects to the list.
	 *
	 * @param array $vulnerabilities List of vulnerability objects.
	 *
	 * @return $this
	 */
	public function add_many( array $vulnerabilities ) {
		foreach ( $vulnerabilities as $vulnerability ) {
			$this->add( $vulnerability );
		}

		return $this;
	}

	/**
	 * Get the underlying array of vulnerabilities.
	 *
	 * @return array
	 */
	public function all() {
		return $this->vulnerabilities;
	}

	/**
	 * Get the vulnerabilities count.
	 *
	 * @return integer
	 */
	public function count() {
		return count( $this->vulnerabilities );
	}

	/**
	 * Call an anonymous function on each value in the vulnerability list. Returns an array of
	 * the values that are returned by the anonymous function.
	 *
	 * @param  Closure $callback Callback to apply to each vulnerability.
	 *
	 * @return array
	 */
	public function each( Closure $callback ) {
		$return = array();

		foreach ( $this->vulnerabilities as $vulnerability ) {
			// Ideally would pass index as well but in this case index === $vulnerability->id.
			$return[] = $callback( $vulnerability );
		}

		return $return;
	}

	/**
	 * Create a filtered-down vulnerabilites collection.
	 *
	 * @param  Closure $callback Filter callback.
	 *
	 * @return static
	 */
	public function filter( Closure $callback ) {
		$filtered = array_filter( $this->vulnerabilities, $callback );

		return new static( $filtered );
	}

	/**
	 * Generate a hash of the current vulnerabilities list.
	 *
	 * @return string
	 *
	 * @todo Allow user to override algo?
	 */
	public function hash() {
		$ids = array_keys( $this->vulnerabilities );
		sort( $ids );

		return hash( 'sha1', implode( ':', $ids ) );
	}

	/**
	 * Check whether the vulnerabilities list is empty.
	 *
	 * @return boolean
	 */
	public function is_empty() {
		return empty( $this->vulnerabilities );
	}

	/**
	 * Create a new vulnerabilities list containing the vulnerabilites from two other lists.
	 *
	 * @param  Vulnerabilities $collection Vulnerabilities instance.
	 *
	 * @return static
	 */
	public function merge( Vulnerabilities $collection ) {
		$combined = array_merge( $this->vulnerabilities, $collection->all() );

		return new static( $combined );
	}

	/**
	 * Merge the vulnerabilities from another instance into this instance.
	 *
	 * @param  Vulnerabilities $collection Vulnerabilities instance.
	 *
	 * @return $this
	 */
	public function merge_in( Vulnerabilities $collection ) {
		$this->add_many( $collection->all() );

		return $this;
	}

	/**
	 * Check that the vulnerabilities list is not empty.
	 *
	 * @return boolean
	 */
	public function not_empty() {
		return ! empty( $this->vulnerabilities );
	}

	/**
	 * Pluck a single field from the vulnerabilities list.
	 *
	 * @param  integer|string $field The field to pluck from each vulnerability.
	 *
	 * @return array
	 */
	public function pluck( $field ) {
		return wp_list_pluck( $this->vulnerabilities, $field );
	}

	/**
	 * Get an iterator for looping over the vulnerabilities list.
	 *
	 * @return Traversable
	 */
	public function getIterator() {
		return new ArrayIterator( $this->vulnerabilities );
	}
}
