<?php

use Soter_Core\WP_Transient_Cache;

class WP_Transient_Cache_Test extends WP_UnitTestCase {
	/** @test */
	function it_throws_for_prefix_greater_than_131_characters() {
		$this->setExpectedException( 'InvalidArgumentException' );

		$cache = new WP_Transient_Cache( str_repeat( 'a', 132 ) );
	}

	/** @test */
	function it_limits_default_lifetime_to_positive_numbers() {
		$cache = new WP_Transient_Cache( '', -5 );

		$this->assertSame( 0, $cache->get_default_lifetime() );
	}

	/** @test */
	function it_can_remove_an_entry_from_the_cache() {
		$cache = new WP_Transient_Cache();

		set_transient( 'key1', 'value1' );

		$this->assertTrue( $cache->forget( 'key1' ) );
		$this->assertFalse( $cache->forget( 'key2' ) );
		$this->assertFalse( get_transient( 'key1' ) );
	}

	/** @test */
	function it_can_remove_a_prefixed_entry_from_the_cache() {
		$cache = new WP_Transient_Cache( 'pfx' );

		set_transient( 'pfx_key1', 'value1' );

		$this->assertTrue( $cache->forget( 'key1' ) );
		$this->assertFalse( $cache->forget( 'key2' ) );
		$this->assertFalse( get_transient( 'pfx_key1' ) );
	}

	/** @test */
	function it_can_get_an_entry_from_the_cache() {
		$cache = new WP_Transient_Cache();

		set_transient( 'key1', 'value1' );

		$this->assertEquals( 'value1', $cache->get( 'key1' ) );

		// WP returns false if entry does not exists - should be converted to null.
		$this->assertNull( $cache->get( 'key2' ) );
	}

	/** @test */
	function it_can_get_a_prefixed_entry_from_the_cache() {
		$cache = new WP_Transient_Cache( 'pfx' );

		set_transient( 'pfx_key1', 'value1' );

		$this->assertEquals( 'value1', $cache->get( 'key1' ) );

		// WP returns false if entry does not exists - should be converted to null.
		$this->assertNull( $cache->get( 'key2' ) );
	}

	/** @test */
	function it_can_save_an_entry_to_the_cache() {
		$cache = new WP_Transient_Cache();

		// Sanity.
		$this->assertFalse( get_transient( 'key1' ) );

		$cache->put( 'key1', 'value1' );

		$this->assertEquals( 'value1', get_transient( 'key1' ) );
	}

	/** @test */
	function it_can_save_a_prefixed_entry_to_the_cache() {
		$cache = new WP_Transient_Cache( 'pfx' );

		// Sanity.
		$this->assertFalse( get_transient( 'pfx_key1' ) );

		$cache->put( 'key1', 'value1' );

		$this->assertEquals( 'value1', get_transient( 'pfx_key1' ) );
	}

	/** @test */
	function it_deletes_an_entry_when_null_is_value_or_zero_is_lifetime() {
		$cache = new WP_Transient_Cache();

		set_transient( 'key1', 'value1' );
		set_transient( 'key2', 'value2' );

		$this->assertTrue( $cache->put( 'key1', null ) );
		$this->assertTrue( $cache->put( 'key2', 'new-value2', 0 ) );
		$this->assertFalse( get_transient( 'key1' ) );
		$this->assertFalse( get_transient( 'key2' ) );
	}

	/** @test */
	function it_deletes_an_entry_when_lifetime_is_less_than_zero() {
		$cache = new WP_Transient_Cache();

		set_transient( 'key1', 'value1' );

		$this->assertTrue( $cache->put( 'key1', 'new-value1', -5 ) );
		$this->assertFalse( get_transient( 'key1' ) );
	}
}
