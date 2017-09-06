<?php

use Soter_Core\Package;
use Soter_Core\Vulnerabilities;
use Soter_Core\Vulnerability;

class Vulnerabilities_Test extends WP_UnitTestCase {
	protected $vulnerabilities;

	public function setUp() {
		parent::setUp();

		$response = sct_get_http_fixture_array( '/api/v2/plugins/contact-form-7' );
		$package = new Package( 'contact-form-7', Package::TYPE_PLUGIN, '3.7' );

		$decoded = json_decode( $response[2], true );
		$body = array_shift( $decoded );

		$this->vulnerabilities = array_map( function( $vulnerability ) use ( $package ) {
			return new Vulnerability( $package, $vulnerability );
		}, $body['vulnerabilities'] );
	}

	public function tearDown() {
		parent::tearDown();

		$this->vulnerabilities = null;
	}
	/** @test */
	function it_allows_vulenrabilities_to_be_added() {
		$vulnerabilities = new Vulnerabilities();

		foreach ( $this->vulnerabilities as $vulnerability ) {
			$vulnerabilities->add( $vulnerability );
		}

		$this->assertCount( 2, $vulnerabilities );
	}

	/** @test */
	function it_allows_multiple_vulnerabilities_to_be_added_at_once() {
		$vulnerabilities = new Vulnerabilities();

		$vulnerabilities->add_many( $this->vulnerabilities );

		$this->assertCount( 2, $vulnerabilities );
	}

	/** @test */
	function it_provides_access_to_underlying_vulnerabilities_array() {
		$vulnerabilities = new Vulnerabilities( $this->vulnerabilities );

		$this->assertTrue( is_array( $vulnerabilities->all() ) );
		$this->assertCount( 2, $vulnerabilities->all() );
	}

	/** @test */
	function it_is_countable() {
		$vulnerabilities = new Vulnerabilities( $this->vulnerabilities );

		$this->assertSame( 2, count( $vulnerabilities ) );
	}

	/** @test */
	function it_is_filterable() {
		$vulnerabilities = new Vulnerabilities( $this->vulnerabilities );
		$filtered = $vulnerabilities->filter( function( Vulnerability $vulnerability ) {
			return $vulnerability->affects_version( '3.7' );
		} );

		// It creates a new instance.
		$this->assertNotSame( $vulnerabilities, $filtered );

		// And underlying vulnerabilities are different.
		$this->assertCount( 2, $vulnerabilities );
		$this->assertCount( 1, $filtered );
	}

	/** @test */
	function it_is_hashable() {
		$none = new Vulnerabilities();
		$vulnerabilities = new Vulnerabilities( $this->vulnerabilities );
		$out_of_order = new Vulnerabilities( [
			$this->vulnerabilities[1],
			$this->vulnerabilities[0],
		] );

		// Produces a hash even when there are no vulnerabilities;
		$this->assertEquals( 'da39a3ee5e6b4b0d3255bfef95601890afd80709', $none->hash() );

		// Produces a hash when there are vulnerabilities.
		$this->assertEquals( '7a9c3e81c45ffeb5719632651a76ff65686b0690', $vulnerabilities->hash() );

		// Vulnerabilities are sorted before hashing to ensure same hash every time.
		$this->assertEquals( $vulnerabilities->hash(), $out_of_order->hash() );
	}

	/** @test */
	function it_knows_whether_it_is_empty() {
		$is_empty = new Vulnerabilities();
		$not_empty = new Vulnerabilities( $this->vulnerabilities );

		$this->assertTrue( $is_empty->is_empty() );
		$this->assertFalse( $is_empty->not_empty() );

		$this->assertTrue( $not_empty->not_empty() );
		$this->assertFalse( $not_empty->is_empty() );
	}

	/** @test */
	function it_can_be_merged_with_another_vulnerabilities_instance() {
		$one = new Vulnerabilities( [ $this->vulnerabilities[0] ] );
		$two = new Vulnerabilities( [ $this->vulnerabilities[1] ] );

		$three = $one->merge( $two );

		$this->assertNotSame( $one, $three );
		$this->assertNotSame( $two, $three );

		$this->assertCount( 2, $three );
	}

	/** @test */
	function it_allows_another_vulnerabilities_instance_to_be_merged_in() {
		$one = new Vulnerabilities( [ $this->vulnerabilities[0] ] );
		$two = new Vulnerabilities( [ $this->vulnerabilities[1] ] );

		$this->assertCount( 1, $one );

		$three = $one->merge_in( $two );

		$this->assertSame( $one, $three );
		$this->assertCount( 2, $three );
	}

	/** @test */
	function it_allows_plucking_fields_from_vulnerabilities() {
		$vulnerabilities = new Vulnerabilities( $this->vulnerabilities );

		$this->assertEquals( [ 7020, 7022 ], array_values( $vulnerabilities->pluck( 'id' ) ) );
	}

	/** @test */
	function it_is_iterable() {
		$vulnerabilities = new Vulnerabilities( $this->vulnerabilities );

		foreach ( $vulnerabilities as $vulnerability ) {
			$this->assertInstanceOf( 'Soter_Core\\Vulnerability', $vulnerability );
		}
	}
}
