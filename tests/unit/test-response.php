<?php

use Soter_Core\Package;
use Soter_Core\Response;
use Soter_Core\Vulnerabilities;

class Response_Test extends WP_Mock\Tools\TestCase {
	const BODY_CF7 = '{"contact-form-7":{"latest_version":"4.7","last_updated":"2017-03-03T19:28:00.000Z","popular":true,"vulnerabilities":[{"id":7020,"title":"Contact Form 7 <= 3.7.1 - Security Bypass ","created_at":"2014-08-01T10:59:06.000Z","updated_at":"2015-05-15T13:48:25.000Z","published_date":null,"references":{"url":["http://www.securityfocus.com/bid/66381/"],"cve":["2014-2265"]},"vuln_type":"AUTHBYPASS","fixed_in":"3.7.2"},{"id":7022,"title":"Contact Form 7 <= 3.5.2 - File Upload Remote Code Execution","created_at":"2014-08-01T10:59:07.000Z","updated_at":"2015-05-15T13:48:25.000Z","published_date":null,"references":{"url":["http://packetstormsecurity.com/files/124154/"]},"vuln_type":"UPLOAD","fixed_in":"3.5.3"}]}}';

	const HEADERS_HTML = [ 'content-type' => 'text/html; charset=utf-8' ];
	const HEADERS_JSON = [ 'content-type' => 'application/json; charset=utf-8' ];

	const STATUS_FAILURE = 404;
	const STATUS_SUCCESS = 200;

	/** @test */
	function it_generates_error_array_for_non_200_responses() {
		$response = new Response( self::STATUS_FAILURE, [], '', Mockery::mock( Package::class ) );

		$this->assertEquals( 'Non-200 status code received', $response->error['message'] );
	}

	/** @test */
	function it_generates_error_array_for_non_json_responses() {
		$response = new Response(
			self::STATUS_SUCCESS,
			self::HEADERS_HTML,
			'',
			Mockery::mock( Package::class )
		);

		$this->assertEquals( 'Received non-JSON response', $response->error['message'] );
	}

	/** @test */
	function it_generates_error_array_for_invalid_json_responses() {
		$response = new Response(
			self::STATUS_SUCCESS,
			self::HEADERS_JSON,
			'{"test": {{}}',
			Mockery::mock( Package::class )
		);

		$this->assertEquals(
			'Error decoding response JSON: Syntax error',
			$response->error['message']
		);
	}

	/** @test */
	function it_generates_error_when_response_slug_does_not_match_package_slug() {
		// @todo Can also match version...
		$response = new Response(
			self::STATUS_SUCCESS,
			self::HEADERS_JSON,
			self::BODY_CF7,
			Mockery::mock( Package::class )
				->shouldReceive( 'get_slug' )
				->once()
				->andReturn( 'not-cf7' )
				->shouldReceive( 'get_version' )
				->once()
				->andReturn( 'doesnt-matter' )
				->getMock()
		);

		$this->assertEquals(
			'Response slug does not match package slug',
			$response->error['message']
		);
	}

	/** @test */
	function it_generates_proper_data_array_from_a_valid_response() {
		$response = $this->make_cf7_response();

		$this->assertEquals( '4.7', $response->latest_version );
		$this->assertEquals( new DateTime( '2017-03-03T19:28:00.000Z' ), $response->last_updated );
		$this->assertTrue( $response->popular );
		$this->assertSame( 2, $response->vulnerabilities->count() );
	}

	/** @test */
	function it_provides_access_to_raw_body() {
		$response = $this->make_cf7_response();

		$this->assertEquals( self::BODY_CF7, $response->get_body() );
	}

	/** @test */
	function it_provides_access_to_raw_data() {
		$response = $this->make_cf7_response();
		$data = $response->get_data();

		$this->assertEquals( '4.7', $data['latest_version'] );
		$this->assertEquals( new DateTime( '2017-03-03T19:28:00.000Z' ), $data['last_updated'] );
		$this->assertTrue( $data['popular'] );
		$this->assertSame( 2, $data['vulnerabilities']->count() );
	}

	/** @test */
	function it_provides_access_to_raw_headers() {
		$response = $this->make_cf7_response();

		$this->assertEquals( self::HEADERS_JSON, $response->get_headers() );
	}

	/** @test */
	function it_provides_access_to_package() {
		$package = $this->get_cf7_package_mock();
		$response = $this->make_cf7_response( $package );

		$this->assertSame( $package, $response->get_package() );
	}

	/** @test */
	function it_provides_access_to_raw_status() {
		$response = $this->make_cf7_response();

		$this->assertSame( self::STATUS_SUCCESS, $response->get_status() );
	}

	/** @test */
	function it_provides_access_to_vulnerabilities() {
		$response = $this->make_cf7_response();

		$this->assertInstanceOf( Vulnerabilities::class, $response->get_vulnerabilities() );
		$this->assertSame( 2, $response->get_vulnerabilities()->count() );
	}

	/** @test */
	function it_returns_empty_instance_when_there_are_no_vulnerabilities() {
		$response = new Response( self::STATUS_FAILURE, [], '', Mockery::mock( Package::class ) );

		$this->assertInstanceOf( Vulnerabilities::class, $response->get_vulnerabilities() );
		$this->assertCount( 0, $response->get_vulnerabilities() );
		$this->assertTrue( $response->get_vulnerabilities()->is_empty() );
	}

	/** @test */
	function it_provides_access_to_vulnerabilities_by_package_version() {
		$response = $this->make_cf7_response();

		// No version specified - Should return all vulnerabilities.
		$this->assertSame( 2, $response->get_vulnerabilities_by_version()->count() );

		$this->assertSame( 2, $response->get_vulnerabilities_by_version( '3.5' )->count() );
		$this->assertSame( 1, $response->get_vulnerabilities_by_version( '3.7' )->count() );
		$this->assertSame( 0, $response->get_vulnerabilities_by_version( '4.7' )->count() );
	}

	/** @test */
	function it_provides_access_to_vulnerabilities_of_current_package_version() {
		$package = Mockery::mock( Package::class )
			->shouldReceive( 'get_slug' )
			->once()
			->andReturn( 'contact-form-7' )
			->shouldReceive( 'get_version' )
			->once()
			->andReturn( '3.7' )
			->getMock();
		$response = $this->make_cf7_response( $package );

		$this->assertSame( 1, count( $response->get_vulnerabilities_for_current_version() ) );
	}

	/** @test */
	function it_knows_whethere_there_are_vulnerabilities() {
		$no_vulns = new Response( self::STATUS_FAILURE, [], '', Mockery::mock( Package::class ) );
		$yes_vulns = $this->make_cf7_response();

		$this->assertFalse( $no_vulns->has_vulnerabilities() );
		$this->assertTrue( $yes_vulns->has_vulnerabilities() );
	}

	/** @test */
	function it_knows_when_there_has_been_an_error() {
		$no_error = $this->make_cf7_response();
		$yes_error = new Response( self::STATUS_FAILURE, [], '', Mockery::mock( Package::class ) );

		$this->assertTrue( $yes_error->is_error() );
		$this->assertFalse( $no_error->is_error() );
	}

	protected function get_cf7_package_mock() {
		return Mockery::mock( Package::class )
			->shouldReceive( 'get_slug' )
			->once()
			->andReturn( 'contact-form-7' )
			->getMock();
	}

	protected function make_cf7_response( $package = null ) {
		return new Response(
			self::STATUS_SUCCESS,
			self::HEADERS_JSON,
			self::BODY_CF7,
			( $package ? $package : $this->get_cf7_package_mock() )
		);
	}
}
