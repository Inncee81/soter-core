<?php

use WP_Mock\Tools\TestCase;
use Soter_Core\WP_Http_Client;

class WP_Http_Client_Test extends TestCase {
	/** @test */
	function it_accepts_a_default_user_agent() {
		$url = 'https://example.com';
		$user_agent = 'default user agent';

		WP_Mock::userFunction( 'wp_safe_remote_get', [
			'args' => [ $url, [
				'user-agent' => $user_agent,
			] ],
			'times' => 1,
		] );
		WP_Mock::userFunction( 'is_wp_error' );
		WP_Mock::userFunction( 'wp_remote_retrieve_headers' );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' );

		$client = new WP_Http_Client( $user_agent );

		$client->get( $url );

		// No assertions - verifying $client->get() is called with default user agent.
	}

	/** @test */
	function it_accepts_user_agent_overrides_per_request() {
		$url = 'https://example.com';
		$default_user_agent = 'default user agent';
		$request_user_agent = 'request user agent';

		WP_Mock::userFunction( 'wp_safe_remote_get', [
			'args' => [ $url, [
				'user-agent' => $request_user_agent,
			] ],
			'times' => 1,
		] );
		WP_Mock::userFunction( 'is_wp_error' );
		WP_Mock::userFunction( 'wp_remote_retrieve_headers' );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' );

		$client = new WP_Http_Client( $default_user_agent );

		$client->get( $url, [
			'user-agent' => $request_user_agent,
		] );

		// No assertions - verifying $client->get() is called with request user agent.
	}

	/** @test */
	function it_passes_args_on_to_wp_http_api() {
		$url = 'https://example.com';
		$args = [ 'one' => 'two' ];

		WP_Mock::userFunction( 'wp_safe_remote_get', [
			'args' => [ $url, $args ],
			'times' => 1,
		] );
		WP_Mock::userFunction( 'is_wp_error' );
		WP_Mock::userFunction( 'wp_remote_retrieve_headers' );
		WP_Mock::userFunction( 'wp_remote_retrieve_response_code' );
		WP_Mock::userFunction( 'wp_remote_retrieve_body' );

		$client = new WP_Http_Client();

		$client->get( $url, $args );

		// No assertions - verifying $client->get() is called with correct args.
	}

	/** @test */
	function it_converts_wp_error_to_exception() {
		$this->expectException( RuntimeException::class );

		$url = 'not://a.real/url';

		$wp_error = Mockery::mock( 'WP_Error' )
			->shouldReceive( 'get_error_message' )
			->once()
			->andReturn( 'Some Error Message' )
			->getMock();
		WP_Mock::userFunction( 'wp_safe_remote_get', [
			'args' => [ $url, [] ],
			'return' => $wp_error,
			'times' => 1,
		] );
		WP_Mock::userFunction( 'is_wp_error', [
			'args' => [ $wp_error ],
			'return' => true,
			'times' => 1,
		] );

		$client = new WP_Http_Client();

		$client->get( $url );
	}

	/** @test */
	function it_provides_response_in_expected_format() {
		$url = 'http://example.org';
		$wp_response = [ 'doesn\'t', 'matter', 'this', 'isn\'t', 'what', 'we\'re', 'testing' ];
		$raw_headers = [ 'array' => 'of', 'response' => 'headers' ];

		$headers = Mockery::mock( 'Requests_Utility_CaseInsensitiveDictionary' )
			->shouldReceive( 'getAll' )
			->once()
			->andReturn( $raw_headers )
			->getMock();

		WP_Mock::userFunction( 'wp_safe_remote_get', [
			'args' => [ $url, [] ],
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

		$client = new WP_Http_Client();
		$response = $client->get( $url );

		$this->assertTrue( is_array( $response ) );
		$this->assertSame( 3, count( $response ) );
		$this->assertSame( 200, $response[0] );
		$this->assertEquals( $raw_headers, $response[1] );
		$this->assertEquals( 'response body', $response[2] );
	}
}
