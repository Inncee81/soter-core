<?php

use Soter_Core\WP_Transient_Cache;

class WP_Transient_Cache_Test extends WP_UnitTestCase {
	/** @test */
	function it_throws_for_prefix_greater_than_131_characters() {
		$this->expectException( 'InvalidArgumentException' );

		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'], str_repeat( 'a', 132 ) );
	}

	/** @test */
	function it_limits_default_lifetime_to_positive_numbers() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'], '', -5 );

		$this->assertSame( 0, $cache->get_default_lifetime() );
	}

	/** @test */
	function it_can_flush_all_entries_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'] );

		$cache->put( 'key1', 'value1' );
		$cache->put( 'key2', 'value2', 120 );

		set_transient( 'alt1', 'another1' );
		set_transient( 'alt2', 'another2', 120 );

		// Sanity.
		foreach ( [ 'key2', 'alt2' ] as $key ) {
			$this->assertNotFalse( get_option( "_transient_{$key}" ) );
		}

		foreach ( [ 'key2', 'alt2' ] as $key ) {
			$this->assertNotFalse( get_option( "_transient_{$key}" ) );
			$this->assertNotFalse( get_option( "_transient_timeout_{$key}" ) );
		}

		$cache->flush();

		foreach ( [ 'key1', 'key2', 'alt1', 'alt2' ] as $key ) {
			$this->assertFalse( get_option( "_transient_{$key}" ) );
			$this->assertFalse( get_option( "_transient_timeout_{$key}" ) );
		}
	}

	/** @test */
	function it_can_flush_all_prefixed_entries_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'], 'pfx' );

		$cache->put( 'key1', 'value1' );
		$cache->put( 'key2', 'value2', 120 );

		set_transient( 'alt1', 'another1' );
		set_transient( 'alt2', 'another2', 120 );

		// Sanity.
		foreach ( [ 'pfx_key1', 'alt1' ] as $key ) {
			$this->assertNotFalse( get_option( "_transient_{$key}" ) );
		}

		foreach ( [ 'pfx_key2', 'alt2' ] as $key ) {
			$this->assertNotFalse( get_option( "_transient_{$key}" ) );
			$this->assertNotFalse( get_option( "_transient_timeout_{$key}" ) );
		}

		$cache->flush();

		foreach ( [ 'key1', 'key2' ] as $key ) {
			$this->assertFalse( get_option( "_transient_{$key}" ) );
			$this->assertFalse( get_option( "_transient_timeout_{$key}" ) );
		}

		$this->assertNotFalse( get_option( '_transient_alt1' ) );
		$this->assertNotFalse( get_option( '_transient_alt2' ) );
		$this->assertNotFalse( get_option( '_transient_timeout_alt2' ) );
	}

	/** @test */
	function it_can_flush_expired_entries_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'] );

		$cache->put( 'key1', 'value1' );
		$cache->put( 'key2', 'value2', 1 );

		set_transient( 'alt1', 'another1' );
		set_transient( 'alt2', 'another2', 1 );

		// Sanity.
		foreach ( [ 'key1', 'alt1' ] as $key ) {
			$this->assertNotFalse( get_option( "_transient_{$key}" ) );
		}

		foreach ( [ 'key2', 'alt2' ] as $key ) {
			$this->assertNotFalse( get_option( "_transient_{$key}" ) );
			$this->assertNotFalse( get_option( "_transient_timeout_{$key}" ) );
		}

		sleep(2);

		$cache->flush_expired();

		foreach ( [ 'key1', 'alt1' ] as $key ) {
			$this->assertNotFalse( get_option( "_transient_{$key}" ) );
		}

		foreach ( [ 'key2', 'alt2' ] as $key ) {
			$this->assertFalse( get_option( "_transient_{$key}" ) );
			$this->assertFalse( get_option( "_transient_timeout_{$key}" ) );
		}
	}

	/** @test */
	function it_can_flush_prefixed_expired_entries_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'], 'pfx' );

		$cache->put( 'key1', 'value1' );
		$cache->put( 'key2', 'value2', 1 );

		set_transient( 'alt1', 'another1' );
		set_transient( 'alt2', 'another2', 1 );

		// Sanity.
		foreach ( [ 'pfx_key1', 'alt1' ] as $key ) {
			$this->assertNotFalse( get_option( "_transient_{$key}" ) );
		}

		foreach ( [ 'pfx_key2', 'alt2' ] as $key ) {
			$this->assertNotFalse( get_option( "_transient_{$key}" ) );
			$this->assertNotFalse( get_option( "_transient_timeout_{$key}" ) );
		}

		sleep(2);

		$cache->flush_expired();

		foreach ( [ 'pfx_key1', 'alt1', 'alt2' ] as $key ) {
			$this->assertNotFalse( get_option( "_transient_{$key}" ) );
		}

		$this->assertNotFalse( get_option( '_transient_timeout_alt2' ) );

		$this->assertFalse( get_option( '_transient_pfx_key2' ) );
		$this->assertFalse( get_option( '_transient_timeout_key2' ) );
	}

	/** @test */
	function it_can_remove_an_entry_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'] );

		set_transient( 'key1', 'value1' );

		$this->assertTrue( $cache->forget( 'key1' ) );
		$this->assertFalse( $cache->forget( 'key2' ) );
		$this->assertFalse( get_transient( 'key1' ) );
	}

	/** @test */
	function it_can_remove_a_prefixed_entry_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'], 'pfx' );

		set_transient( 'pfx_key1', 'value1' );

		$this->assertTrue( $cache->forget( 'key1' ) );
		$this->assertFalse( $cache->forget( 'key2' ) );
		$this->assertFalse( get_transient( 'pfx_key1' ) );
	}

	/** @test */
	function it_can_get_an_entry_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'] );

		set_transient( 'key1', 'value1' );

		$this->assertEquals( 'value1', $cache->get( 'key1' ) );

		// WP returns false if entry does not exists - should be converted to null.
		$this->assertNull( $cache->get( 'key2' ) );
	}

	/** @test */
	function it_can_get_a_prefixed_entry_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'], 'pfx' );

		set_transient( 'pfx_key1', 'value1' );

		$this->assertEquals( 'value1', $cache->get( 'key1' ) );

		// WP returns false if entry does not exists - should be converted to null.
		$this->assertNull( $cache->get( 'key2' ) );
	}

	/** @test */
	function it_can_save_an_entry_to_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'] );

		// Sanity.
		$this->assertFalse( get_transient( 'key1' ) );

		$cache->put( 'key1', 'value1' );

		$this->assertEquals( 'value1', get_transient( 'key1' ) );
	}

	/** @test */
	function it_can_save_a_prefixed_entry_to_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'], 'pfx' );

		// Sanity.
		$this->assertFalse( get_transient( 'pfx_key1' ) );

		$cache->put( 'key1', 'value1' );

		$this->assertEquals( 'value1', get_transient( 'pfx_key1' ) );
	}

	/** @test */
	function it_deletes_an_entry_when_null_is_value_or_zero_is_lifetime() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'] );

		set_transient( 'key1', 'value1' );
		set_transient( 'key2', 'value2' );

		$this->assertTrue( $cache->put( 'key1', null ) );
		$this->assertTrue( $cache->put( 'key2', 'new-value2', 0 ) );
		$this->assertFalse( get_transient( 'key1' ) );
		$this->assertFalse( get_transient( 'key2' ) );
	}

	/** @test */
	function it_deletes_an_entry_when_lifetime_is_less_than_zero() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'] );

		set_transient( 'key1', 'value1' );

		$this->assertTrue( $cache->put( 'key1', 'new-value1', -5 ) );
		$this->assertFalse( get_transient( 'key1' ) );
	}
}
