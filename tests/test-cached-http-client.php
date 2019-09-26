<?php

use WP_Mock\Tools\TestCase;
use Soter_Core\Http_Interface;
use Soter_Core\Cache_Interface;
use Soter_Core\Cached_Http_Client;

/**
 * @todo Link version in cache key to current package version.
 */
class Cached_Http_Client_Test extends TestCase {
	/** @test */
	function it_checks_for_cached_response_first() {
		$http = Mockery::mock( Http_Interface::class );
		$cache = Mockery::mock( Cache_Interface::class )
			->shouldReceive( 'get' )
			->with( 'soter_core:v0.2.0:http:get:testing' )
			->once()
			->andReturn( 'cached-response' )
			->getMock();

		$client = new Cached_Http_Client( $http, $cache );

		$this->assertEquals( 'cached-response', $client->get( 'testing' ) );
	}

	/** @test */
	function it_falls_back_to_http_get_and_saves_response_to_cache() {
		$http = Mockery::mock( 'Soter_Core\\Http_Interface' )
			->shouldReceive( 'get' )
			->with( 'testing' )
			->once()
			->andReturn( 'fresh-response' )
			->getMock();
		$cache = Mockery::mock( 'Soter_Core\\Cache_Interface' )
			->shouldReceive( 'get' )
			->with( 'soter_core:v0.2.0:http:get:testing' )
			->once()
			->andReturnNull()
			->shouldReceive( 'put' )
			->with( 'soter_core:v0.2.0:http:get:testing', 'fresh-response' )
			->once()
			->getMock();

		$client = new Cached_Http_Client( $http, $cache );

		$this->assertEquals( 'fresh-response', $client->get( 'testing' ) );
	}
}
