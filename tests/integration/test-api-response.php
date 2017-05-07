<?php

use Soter_Core\Api_Response;

class Api_Response_Test extends WP_UnitTestCase {
	use Gets_Http_Fixtures_Trait;

	/** @test */
	function it_generates_error_array_for_non_200_responses() {
		list( $status, $headers, $body ) = static::get_http_fixture_array(
			'/non-200-response'
		);

		$response = new Api_Response( $status, $headers, $body );

		$this->assertEquals(
			'Non-200 status code received',
			$response->error['message']
		);
	}

	/** @test */
	function it_generates_error_array_for_non_json_responses() {
		list( $status, $headers, $body ) = static::get_http_fixture_array(
			'/non-json-response'
		);

		$response = new Api_Response( $status, $headers, $body );

		$this->assertEquals(
			'Received non-JSON response',
			$response->error['message']
		);
	}

	/** @test */
	function it_generates_error_array_for_invalid_json_responses() {
		list( $status, $headers, $body ) = static::get_http_fixture_array(
			'/invalid-json-response'
		);

		$response = new Api_Response( $status, $headers, $body );

		$this->assertEquals(
			'Response does not appear to be valid JSON',
			$response->error['message']
		);
	}

	/** @test */
	function it_generates_proper_data_array_from_a_valid_response() {
		list( $status, $headers, $body ) = static::get_http_fixture_array(
			'/api/v2/plugins/contact-form-7'
		);

		$response = new Api_Response( $status, $headers, $body );

		$this->assertEquals( '4.7', $response->latest_version );
		$this->assertEquals(
			new DateTime( '2017-03-03T19:28:00.000Z' ),
			$response->last_updated
		);
		$this->assertTrue( $response->popular );
		$this->assertSame( 2, count( $response->vulnerabilities ) );
		$this->assertEquals( 'contact-form-7', $response->slug );
	}

	/** @test */
	function it_provides_access_to_raw_body() {
		list( $status, $headers, $body ) = static::get_http_fixture_array(
			'/api/v2/plugins/contact-form-7'
		);

		$response = new Api_Response( $status, $headers, $body );

		$this->assertEquals(
			'{"contact-form-7":{"latest_version":"4.7","last_updated":"2017-03-03T19:28:00.000Z","popular":true,"vulnerabilities":[{"id":7020,"title":"Contact Form 7 <= 3.7.1 - Security Bypass ","created_at":"2014-08-01T10:59:06.000Z","updated_at":"2015-05-15T13:48:25.000Z","published_date":null,"references":{"url":["http://www.securityfocus.com/bid/66381/"],"cve":["2014-2265"]},"vuln_type":"AUTHBYPASS","fixed_in":"3.7.2"},{"id":7022,"title":"Contact Form 7 <= 3.5.2 - File Upload Remote Code Execution","created_at":"2014-08-01T10:59:07.000Z","updated_at":"2015-05-15T13:48:25.000Z","published_date":null,"references":{"url":["http://packetstormsecurity.com/files/124154/"]},"vuln_type":"UPLOAD","fixed_in":"3.5.3"}]}}',
			$response->get_body()
		);
	}

	/** @test */
	function it_provides_access_to_raw_data() {
		list( $status, $headers, $body ) = static::get_http_fixture_array(
			'/api/v2/plugins/contact-form-7'
		);

		$response = new Api_Response( $status, $headers, $body );

		$this->assertEquals( '4.7', $response->get_data()['latest_version'] );
		$this->assertEquals(
			new DateTime( '2017-03-03T19:28:00.000Z' ),
			$response->get_data()['last_updated']
		);
		$this->assertTrue( $response->get_data()['popular'] );
		$this->assertSame( 2, count( $response->get_data()['vulnerabilities'] ) );
		$this->assertEquals( 'contact-form-7', $response->get_data()['slug'] );
	}

	/** @test */
	function it_provides_access_to_raw_headers() {
		list( $status, $headers, $body ) = static::get_http_fixture_array(
			'/api/v2/plugins/contact-form-7'
		);

		$response = new Api_Response( $status, $headers, $body );

		$this->assertEqualSetsWithIndex(
			[
				"server" => "Sucuri/Cloudproxy",
				"date" => "Sat, 06 May 2017 20:12:53 GMT",
				"content-type" => "application/json; charset=utf-8",
				"vary" => "Accept-Encoding",
				"cache-control" => "max-age=0, private, must-revalidate",
				"x-request-id" => "8f2a9d1a-ad51-42c1-b38c-286188ec7606",
				"strict-transport-security" => "max-age=63072000; includeSubDomains; preload",
				"x-frame-options" => "SAMEORIGIN",
				"x-xss-protection" => "1; mode=block",
				"x-content-type-options" => "nosniff",
				"x-download-options" => "noopen",
				"x-permitted-cross-domain-policies" => "none",
				"content-security-policy" => "default-src 'self'; object-src 'none'; child-src 'self' https://rpm.newrelic.com; frame-src 'self' https://rpm.newrelic.com https://www.google.com/recaptcha/; script-src 'self' https://www.google.com/recaptcha/ https://apis.google.com https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/; img-src 'self' https://ssl.gstatic.com/; style-src 'self' 'unsafe-inline'; upgrade-insecure-requests; block-all-mixed-content; report-uri https://firefart.report-uri.io/r/default/csp/enforce;",
				"x-content-security-policy" => "default-src 'self'; object-src 'none'; child-src 'self' https://rpm.newrelic.com; frame-src 'self' https://rpm.newrelic.com https://www.google.com/recaptcha/; script-src 'self' https://www.google.com/recaptcha/ https://apis.google.com https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/; img-src 'self' https://ssl.gstatic.com/; style-src 'self' 'unsafe-inline'; upgrade-insecure-requests; block-all-mixed-content; report-uri https://firefart.report-uri.io/r/default/csp/enforce;",
				"x-webkit-csp" => "default-src 'self'; object-src 'none'; child-src 'self' https://rpm.newrelic.com; frame-src 'self' https://rpm.newrelic.com https://www.google.com/recaptcha/; script-src 'self' https://www.google.com/recaptcha/ https://apis.google.com https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/; img-src 'self' https://ssl.gstatic.com/; style-src 'self' 'unsafe-inline'; upgrade-insecure-requests; block-all-mixed-content; report-uri https://firefart.report-uri.io/r/default/csp/enforce;",
				"content-encoding" => "gzip",
				"x-sucuri-cache" => "MISS",
				"x-sucuri-id" => "11018",
			],
			$response->get_headers()
		);
	}

	/** @test */
	function it_provides_access_to_raw_status() {
		list( $status, $headers, $body ) = static::get_http_fixture_array(
			'/api/v2/plugins/contact-form-7'
		);

		$response = new Api_Response( $status, $headers, $body );

		$this->assertSame( 200, $response->get_status() );
	}

	/** @test */
	function it_provides_access_to_vulnerabilities() {
		list( $status, $headers, $body ) = static::get_http_fixture_array(
			'/api/v2/plugins/contact-form-7'
		);

		$response = new Api_Response( $status, $headers, $body );

		$this->assertSame( 2, count( $response->get_vulnerabilities() ) );
	}

	/** @test */
	function it_returns_an_empty_array_when_there_are_no_vulnerabilities() {
		list( $status, $headers, $body ) = static::get_http_fixture_array(
			'/non-200-response'
		);

		$response = new Api_Response( $status, $headers, $body );

		$this->assertSame( [], $response->get_vulnerabilities() );
	}

	/** @test */
	function it_provides_access_to_vulnerabilities_by_package_version() {
		list( $status, $headers, $body ) = static::get_http_fixture_array(
			'/api/v2/plugins/contact-form-7'
		);

		$response = new Api_Response( $status, $headers, $body );

		// No version specified - Should return all vulnerabilities.
		$this->assertSame( 2, count( $response->get_vulnerabilities_by_version() ) );

		$this->assertSame(
			2,
			count( $response->get_vulnerabilities_by_version( '3.5' ) )
		);
		$this->assertSame(
			1,
			count( $response->get_vulnerabilities_by_version( '3.7' ) )
		);
		$this->assertSame(
			0,
			count( $response->get_vulnerabilities_by_version( '4.7' ) )
		);
	}

	/** @test */
	function it_knows_when_there_are_no_vulnerabilities() {
		list( $status, $headers, $body ) = static::get_http_fixture_array(
			'/non-200-response'
		);

		$response = new Api_Response( $status, $headers, $body );

		$this->assertFalse( $response->has_vulnerabilities() );
	}

	/** @test */
	function it_knows_when_there_has_been_an_error() {
		list( $status, $headers, $body ) = static::get_http_fixture_array(
			'/non-200-response'
		);

		$response = new Api_Response( $status, $headers, $body );

		$this->assertTrue( $response->is_error() );
	}
}
