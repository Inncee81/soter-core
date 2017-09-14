<?php

use Soter_Core\Vulnerability;
use Soter_Core\Vulnerabilities;

/**
 * @todo Verify that a vuln can't be added twice - aka unique vulns.
 */
class Vulnerabilities_Test extends WP_Mock\Tools\TestCase {
	protected $vulnerabilities;

	public function setUp() {
		parent::setUp();

		$one = Mockery::mock( Vulnerability::class );
		$one->id = 100;

		$two = Mockery::mock( Vulnerability::class );
		$two->id = 200;

		$this->vulnerabilities = [ $one, $two ];
	}

	public function tearDown() {
		parent::tearDown();

		$this->vulnerabilities = null;
	}

	/** @test */
	function it_allows_vulenrabilities_to_be_added() {
		$collection = new Vulnerabilities();

		foreach ( $this->vulnerabilities as $vulnerability ) {
			$collection->add( $vulnerability );
		}

		$this->assertCount( 2, $collection );
	}

	/** @test */
	function it_allows_multiple_vulnerabilities_to_be_added_at_once() {
		$collection = new Vulnerabilities();

		$collection->add_many( $this->vulnerabilities );

		$this->assertCount( 2, $collection );
	}

	/** @test */
	function it_provides_access_to_underlying_vulnerabilities_array() {
		$collection = new Vulnerabilities( $this->vulnerabilities );

		$this->assertTrue( is_array( $collection->all() ) );
		$this->assertCount( 2, $collection->all() );
	}

	/** @test */
	function it_is_countable() {
		$collection = new Vulnerabilities( $this->vulnerabilities );

		$this->assertInstanceOf( Countable::class, $collection );
	}

	/** @test */
	function it_can_return_custom_array_values_for_each_vulnerability() {
		$collection = new Vulnerabilities( $this->vulnerabilities );

		// Bad example - you would use ->pluck() instead. But it makes the point fine for testing.
		$ids = $collection->each( function( $vulnerability ) {
			return $vulnerability->id;
		} );

		$this->assertEquals( [ 100, 200 ], $ids );
	}

	/** @test */
	function it_is_filterable() {
		$one = Mockery::mock( Vulnerability::class )
			->shouldReceive( 'affects_version' )
			->once()
			->with( '3.7' )
			->andReturn( true )
			->getMock();
		$one->id = 100;

		$two = Mockery::mock( Vulnerability::class )
			->shouldReceive( 'affects_version' )
			->once()
			->with( '3.7' )
			->andReturn( false )
			->getMock();
		$two->id = 200;

		$collection = new Vulnerabilities( [ $one, $two ] );
		$filtered = $collection->filter( function( $vulnerability ) {
			return $vulnerability->affects_version( '3.7' );
		} );

		// It creates a new instance.
		$this->assertNotSame( $collection, $filtered );

		// And underlying vulnerabilities are different.
		$this->assertCount( 2, $collection );
		$this->assertCount( 1, $filtered );
	}

	/** @test */
	function it_is_hashable() {
		$none = new Vulnerabilities();
		$some = new Vulnerabilities( $this->vulnerabilities );
		$out_of_order = new Vulnerabilities( [
			$this->vulnerabilities[1],
			$this->vulnerabilities[0],
		] );

		// Produces a hash even when there are no vulnerabilities;
		$this->assertEquals( 'da39a3ee5e6b4b0d3255bfef95601890afd80709', $none->hash() );

		// Produces a hash when there are vulnerabilities.
		$this->assertEquals( '6c295500d167055575bb7e0d8b05a9a8500b33cc', $some->hash() );

		// Vulnerabilities are sorted before hashing to ensure same hash every time.
		$this->assertEquals( $some->hash(), $out_of_order->hash() );
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
		WP_Mock::userFunction( 'wp_list_pluck', [
			'args' => [
				// Vulnerabilities get re-keyed by ID internally.
				[ 100 => $this->vulnerabilities[0], 200 => $this->vulnerabilities[1] ],
				'id'
			],
			'return' => function( $items, $field ) {
				$return = [];

				foreach ( $items as $item ) {
					$return[] = $item->{$field};
				}

				return $return;
			},
			'times' => 1,
		] );

		$collection = new Vulnerabilities( $this->vulnerabilities );

		$this->assertEquals( [ 100, 200 ], $collection->pluck( 'id' ) );
	}

	/** @test */
	function it_is_iterable() {
		$collection = new Vulnerabilities( $this->vulnerabilities );

		$this->assertInstanceOf( IteratorAggregate::class, $collection );
		$this->assertInstanceOf( ArrayIterator::class, $collection->getIterator() );

		foreach ( $collection as $item ) {
			$this->assertInstanceOf( Vulnerability::class, $item );
		}
	}
}
