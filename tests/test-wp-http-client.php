<?php

use WP_Mock\Tools\TestCase;
use Soter_Core\WP_Http_Client;

class WP_Http_Client_Test extends TestCase {
	/** @test */
	function it_converts_wp_error_to_exception() {
		$this->expectException( RuntimeException::class );

		$url = 'not://a.real/url';
		$user_agent = 'Soter Core Test Suite';

		$wp_error = Mockery::mock( 'WP_Error' )
			->shouldReceive( 'get_error_message' )
			->once()
			->andReturn( 'Some Error Message' )
			->getMock();
		WP_Mock::userFunction( 'wp_safe_remote_get', [
			'args' => [ $url, [ 'user-agent' => $user_agent ] ],
			'return' => $wp_error,
			'times' => 1,
		] );
		WP_Mock::userFunction( 'is_wp_error', [
			'args' => [ $wp_error ],
			'return' => true,
			'times' => 1,
		] );

		$client = new WP_Http_Client( $user_agent );

		$client->get( $url );
	}

	/** @test */
	function it_provides_response_in_expected_format() {
		$url = 'http://example.org';
		$user_agent = 'Soter Core Test Suite';
		$wp_response = [ 'doesn\'t', 'matter', 'this', 'isn\'t', 'what', 'we\'re', 'testing' ];
		$raw_headers = [ 'array' => 'of', 'response' => 'headers' ];

		$headers = Mockery::mock( 'Requests_Utility_CaseInsensitiveDictionary' )
			->shouldReceive( 'getAll' )
			->once()
			->andReturn( $raw_headers )
			->getMock();

		WP_Mock::userFunction( 'wp_safe_remote_get', [
			'args' => [ $url, [ 'user-agent' => $user_agent ] ],
			'return' => $wp_response,
			'times' => 1,
		] );
		WP_Mock::userFunction( 'is_wp_error', [
			'return' => false,
			'times' => 1,
		] );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code', [
			'args' => [ $wp_response ],
			'return' => 200,
			'times' => 1,
		] );
		WP_Mock::userFunction( 'wp_remote_retrieve_headers', [
			'args' => [ $wp_response ],
			'return' => $headers,
			'times' => 1,
		] );
		WP_Mock::userFunction( 'wp_remote_retrieve_body', [
			'args' => [ $wp_response ],
			'return' => 'response body',
			'times' => 1,
		] );

		$client = new WP_Http_Client( $user_agent );
		$response = $client->get( $url );

		$this->assertTrue( is_array( $response ) );
		$this->assertSame( 3, count( $response ) );
		$this->assertSame( 200, $response[0] );
		$this->assertEquals( $raw_headers, $response[1] );
		$this->assertEquals( 'response body', $response[2] );
	}
}
