<?php

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
	protected $vulnerabilities = array();

	public function __construct( array $vulnerabilities = [] ) {
		$this->add_many( $vulnerabilities );
	}

	public function add( Vulnerability_Interface $vulnerability ) {
		// Keyed by ID to prevent duplicates.
		$this->vulnerabilities[ $vulnerability->id ] = $vulnerability;

		return $this;
	}

	public function add_many( array $vulnerabilities ) {
		foreach ( $vulnerabilities as $vulnerability ) {
			$this->add( $vulnerability );
		}

		return $this;
	}

	public function all() {
		return $this->vulnerabilities;
	}

	public function count() {
		return count( $this->vulnerabilities );
	}

	public function filter( Closure $callback ) {
		$filtered = array_filter( $this->vulnerabilities, $callback );

		return new static( $filtered );
	}

	/**
	 * @todo Allow user to override algo?
	 */
	public function hash() {
		$ids = array_keys( $this->vulnerabilities );
		sort( $ids );

		return hash( 'sha1', implode( ':', $ids ) );
	}

	public function is_empty() {
		return empty( $this->vulnerabilities );
	}

	public function merge( Vulnerabilities $collection ) {
		$combined = array_merge( $this->vulnerabilities, $collection->all() );

		return new static( $combined );
	}

	public function merge_in( Vulnerabilities $collection ) {
		$this->add_many( $collection->all() );

		return $this;
	}

	public function not_empty() {
		return ! empty( $this->vulnerabilities );
	}

	public function pluck( $field ) {
		return wp_list_pluck( $this->vulnerabilities, $field );
	}

	public function getIterator() {
		return new ArrayIterator( $this->vulnerabilities );
	}
}
