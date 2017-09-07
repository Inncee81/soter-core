<?php

use Soter_Core\WP_Transient_Cache;

class WP_Transient_Cache_Test extends WP_Mock\Tools\TestCase {
	/** @test */
	function it_throws_for_prefix_greater_than_131_characters() {
		$this->expectException( InvalidArgumentException::class );

		$cache = new WP_Transient_Cache( str_repeat( 'a', 132 ) );
	}

	/** @test */
	function it_limits_default_lifetime_to_positive_numbers() {
		$cache = new WP_Transient_Cache( '', -5 );

		$this->assertSame( 0, $cache->get_default_lifetime() );
	}

	/** @test */
	function it_can_remove_an_entry_from_the_cache() {
		WP_Mock::userFunction( 'delete_transient', [
			'args' => 'key',
			'return_in_order' => [ true, false ],
			'times' => 2,
		] );

		$cache = new WP_Transient_Cache();

		// Returns same value as delete_transient().
		$this->assertTrue( $cache->forget( 'key' ) );
		$this->assertFalse( $cache->forget( 'key' ) );
	}

	/** @test */
	function it_can_remove_a_prefixed_entry_from_the_cache() {
		WP_Mock::userFunction( 'delete_transient', [
			'args' => 'pfx_key',
			'return_in_order' => [ true, false ],
			'times' => 2,
		] );

		$cache = new WP_Transient_Cache( 'pfx' );

		// Returns same value as delete_transient().
		$this->assertTrue( $cache->forget( 'key' ) );
		$this->assertFalse( $cache->forget( 'key' ) );
	}

	/** @test */
	function it_can_get_an_entry_from_the_cache() {
		WP_Mock::userFunction( 'get_transient', [
			'args' => 'key',
			'return_in_order' => [ 'value', false ],
			'times' => 2,
		] );

		$cache = new WP_Transient_Cache();

		$this->assertEquals( 'value', $cache->get( 'key' ) );

		// WP returns false if entry does not exists - should be converted to null.
		$this->assertNull( $cache->get( 'key' ) );
	}

	/** @test */
	function it_can_get_a_prefixed_entry_from_the_cache() {
		WP_Mock::userFunction( 'get_transient', [
			'args' => 'pfx_key',
			'return_in_order' => [ 'value', false ],
			'times' => 2,
		] );

		$cache = new WP_Transient_Cache( 'pfx' );

		$this->assertEquals( 'value', $cache->get( 'key' ) );

		// WP returns false if entry does not exists - should be converted to null.
		$this->assertNull( $cache->get( 'key' ) );
	}

	/** @test */
	function it_can_save_an_entry_to_the_cache() {
		WP_Mock::userFunction( 'set_transient', [
			'args' => [ 'key', 'value', 0 ],
			'return_in_order' => [ true, false ],
			'times' => 2,
		] );

		$cache = new WP_Transient_Cache();

		// Returns same value as set_transient().
		$this->assertTrue( $cache->put( 'key', 'value' ) );
		$this->assertFalse( $cache->put( 'key', 'value' ) );
	}

	/** @test */
	function it_can_save_a_prefixed_entry_to_the_cache() {
		WP_Mock::userFunction( 'set_transient', [
			'args' => [ 'pfx_key', 'value', 0 ],
			'return_in_order' => [ true, false ],
			'times' => 2,
		] );

		$cache = new WP_Transient_Cache( 'pfx' );

		// Returns same value as set_transient().
		$this->assertTrue( $cache->put( 'key', 'value' ) );
		$this->assertFalse( $cache->put( 'key', 'value' ) );
	}

	/** @test */
	function it_deletes_an_entry_when_null_is_value() {
		WP_Mock::userFunction( 'delete_transient', [
			'args' => 'key',
			'return' => true,
			'times' => 1,
		] );

		$cache = new WP_Transient_Cache();

		$this->assertTrue( $cache->put( 'key', null ) );
	}

	/** @test */
	function it_deletes_an_entry_when_zero_is_lifetime() {
		WP_Mock::userFunction( 'delete_transient', [
			'args' => 'key',
			'return' => true,
			'times' => 1,
		] );

		$cache = new WP_Transient_Cache();

		$this->assertTrue( $cache->put( 'key', 'value', 0 ) );
	}

	/** @test */
	function it_deletes_an_entry_when_lifetime_is_less_than_zero() {
		WP_Mock::userFunction( 'delete_transient', [
			'args' => 'key',
			'return' => true,
			'times' => 1,
		] );

		$cache = new WP_Transient_Cache();

		$this->assertTrue( $cache->put( 'key', 'value', -5 ) );
	}

	/** @test */
	function it_throws_when_key_is_empty_string() {
		$this->expectException( InvalidArgumentException::class );

		$cache = new WP_Transient_Cache();

		$cache->get( '' );
	}

	/** @test */
	function it_hashes_key_when_longer_than_allowed_length() {
		WP_Mock::userFunction( 'get_transient', [
			'args' => '18db5093a476179652c91dedc3cb1478478076a0',
			'return' => true,
			'times' => 1,
		] );

		$cache = new WP_Transient_Cache();
		$key = str_repeat( 'a', 173 );

		$this->assertTrue( $cache->get( $key ) );
	}

	/** @test */
	function it_hashes_prefixed_key_when_longer_than_allowed_length() {
		WP_Mock::userFunction( 'get_transient', [
			'args' => 'pfx_e7b41d4fb4cc6c5eb5afda9416c2718e273034bf',
			'return' => true,
			'times' => 1,
		] );

		$cache = new WP_Transient_Cache( 'pfx' );
		$key = str_repeat( 'a', 169 );

		$this->assertTrue( $cache->get( $key ) );
	}

	/** @test */
	function it_returns_default_lifetime_when_given_a_null_lifetime() {
		WP_Mock::userFunction( 'set_transient', [
			'args' => [ 'key', 'value', 60 ],
			'return' => true,
			'times' => 1,
		] );

		$cache = new WP_Transient_Cache( '', 60 );

		$this->assertTrue( $cache->put( 'key', 'value' ) );
	}
}
