<?php

use Soter_Core\WP_Transient_Cache;

class WP_Transient_Cache_Test extends WP_UnitTestCase {
	/** @test */
	function it_limits_prefix_length_to_131_characters() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'], str_repeat( 'a', 150 ) );

		$this->assertSame( 131, strlen( $cache->get_prefix() ) );
	}

	/** @test */
	function it_can_flush_all_entries_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'] );
		$key = sha1( 'test1' );

		set_transient( $key, 'value' );

		$cache->flush();
		// Same request so lets flush the object cache.
		wp_cache_flush();

		$this->assertFalse( get_transient( $key ) );
	}

	/** @test */
	function it_can_flush_all_prefixed_entries_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'], 'pfx' );
		$key = 'pfx_' . sha1( 'test' );

		set_transient( $key, 'value' );

		$cache->flush();
		// Same request so lets flush the object cache.
		wp_cache_flush();

		$this->assertFalse( get_transient( $key ) );
	}

	/** @test */
	function it_can_flush_expired_entries_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'] );
		$key1 = sha1( 'test1' );
		$key2 = sha1( 'test2' );

		set_transient( $key1, 'value1', -60 );
		set_transient( $key2, 'value2' );

		// Sanity.
		$this->assertEquals( 'value1', get_option( "_transient_{$key1}" ) );

		$cache->flush_expired();
		// Same request so lets flush the object cache.
		wp_cache_flush();

		$this->assertFalse( get_transient( $key1 ) );
		$this->assertEquals( 'value2', get_transient( $key2 ) );
	}

	/** @test */
	function it_can_flush_prefixed_expired_entries_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'], 'pfx' );
		$key1 = 'pfx_' . sha1( 'test1' );
		$key2 = 'pfx_' . sha1( 'test2' );

		set_transient( $key1, 'value1', -60 );
		set_transient( $key2, 'value2' );

		// Sanity.
		$this->assertEquals( 'value1', get_option( "_transient_{$key1}" ) );

		$cache->flush_expired();
		// Same request so lets flush the object cache.
		wp_cache_flush();

		$this->assertFalse( get_transient( $key1 ) );
		$this->assertEquals( 'value2', get_transient( $key2 ) );
	}

	/** @test */
	function it_can_remove_an_entry_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'] );
		$key = sha1( 'test' );

		set_transient( $key, 'value' );

		$this->assertTrue( $cache->forget( 'test' ) );
		$this->assertFalse( get_transient( $key ) );
	}

	/** @test */
	function it_can_remove_a_prefixed_entry_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'], 'pfx' );
		$key = 'pfx_' . sha1( 'test' );

		set_transient( $key, 'value' );

		$this->assertTrue( $cache->forget( 'test' ) );
		$this->assertFalse( get_transient( $key ) );
	}

	/** @test */
	function it_can_get_an_entry_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'] );
		$key = sha1( 'test' );

		set_transient( $key, 'value' );

		$this->assertEquals( 'value', $cache->get( 'test' ) );

		// WP returns false if entry does not exists - should be converted to null.
		$this->assertNull( $cache->get( 'test2' ) );
	}

	/** @test */
	function it_can_get_a_prefixed_entry_from_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'], 'pfx' );
		$key = 'pfx_' . sha1( 'test' );

		set_transient( $key, 'value' );

		$this->assertEquals( 'value', $cache->get( 'test' ) );

		// WP returns false if entry does not exists - should be converted to null.
		$this->assertNull( $cache->get( 'test2' ) );
	}

	/** @test */
	function it_can_save_an_entry_to_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'] );
		$key = sha1( 'test' );

		// Sanity.
		$this->assertFalse( get_transient( $key ) );

		$cache->put( 'test', 'value' );

		$this->assertEquals( 'value', get_transient( $key ) );
	}

	/** @test */
	function it_can_save_a_prefixed_entry_to_the_cache() {
		$cache = new WP_Transient_Cache( $GLOBALS['wpdb'], 'pfx' );
		$key = 'pfx_' . sha1( 'test' );

		// Sanity.
		$this->assertFalse( get_transient( $key ) );

		$cache->put( 'test', 'value' );

		$this->assertEquals( 'value', get_transient( $key ) );
	}
}
