<?php

use Soter_Core\WP_Http_Client;

class WP_Http_Client_Test extends WP_UnitTestCase {
	/** @test */
	function it_converts_wp_error_to_exception() {
		$this->expectException( 'RuntimeException' );

		$client = new WP_Http_Client( 'Soter Core Test Suite' );
		$client->get( 'not://a.real/url' );
	}

	/** @test */
	function it_provides_response_in_expected_format() {
		$client = new WP_Http_Client( 'Soter Core Test Suite' );
		$response = $client->get( 'http://example.org' );

		$this->assertTrue( is_array( $response ) );
		$this->assertSame( 3, count( $response ) );
		$this->assertSame( 200, $response[0] );
		$this->assertTrue( isset( $response[1]['content-type'] ) );
		$this->assertEquals( 'text/html', $response[1]['content-type'] );
		$this->assertTrue( is_string( $response[2] ) );
	}
}
