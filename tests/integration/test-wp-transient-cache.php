<?php

use Soter_Core\WP_Transient_Cache;

class WP_Transient_Cache_Test extends WP_UnitTestCase {
	/** @test */
	function it_knows_if_an_entry_is_saved_to_cache() {
		$cache = new WP_Transient_Cache;
		set_transient( md5( 'test1' ), 'value' );

		$this->assertTrue( $cache->contains( 'test1' ) );
		$this->assertFalse( $cache->contains( 'test2' ) );
	}

	/** @test */
	function it_can_get_an_entry_from_the_cache() {
		$cache = new WP_Transient_Cache;
		set_transient( md5( 'test1' ), 'value' );

		$this->assertEquals( 'value', $cache->fetch( 'test1' ) );

		// WP returns false if entry does not exists - should convert to null.
		$this->assertNull( $cache->fetch( 'test2' ) );
	}

	/** @test */
	function it_can_save_an_entry_to_the_cache() {
		$cache = new WP_Transient_Cache;

		// Sanity.
		$this->assertFalse( get_transient( md5( 'test1' ) ) );

		$cache->save( 'test1', 'value' );

		$this->assertEquals( 'value', get_transient( md5( 'test1' ) ) );
	}

	/** @test */
	function it_prefixes_cache_keys_when_appropriate() {
		$cache = new WP_Transient_Cache( 'pfx' );

		set_transient( 'pfx_' . md5( 'test1' ), 'prefixed value' );

		$this->assertTrue( $cache->contains( 'test1' ) );
		$this->assertEquals( 'prefixed value', $cache->fetch( 'test1' ) );

		$cache->save( 'test1', 'updated value' );

		$this->assertEquals( 'updated value', $cache->fetch( 'test1' ) );
	}
}
