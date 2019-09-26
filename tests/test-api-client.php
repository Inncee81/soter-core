<?php

use Soter_Core\Package;
use Soter_Core\Api_Client;
use WP_Mock\Tools\TestCase;
use Soter_Core\Http_Interface;

class Api_Client_Test extends TestCase {
	protected $cf7_response;
	protected $twentyfifteen_response;
	protected $wordpress_response;

	public function setUp() : void {
		parent::setUp();

		$this->cf7_response = sct_get_http_fixture_array( '/api/v2/plugins/contact-form-7' );
		$this->twentyfifteen_response = sct_get_http_fixture_array( '/api/v2/themes/twentyfifteen' );
		$this->wordpress_response = sct_get_http_fixture_array( '/api/v2/wordpresses/474' );
	}

	public function tearDown() : void {
		parent::tearDown();

		$this->cf7_response = null;
		$this->twentyfifteen_response = null;
		$this->wordpress_response = null;
	}

	/** @test */
	function it_can_check_plugins() {
		$http = Mockery::mock( Http_Interface::class )
			->shouldReceive( 'get' )
			->once()
			->with( 'https://wpvulndb.com/api/v2/plugins/contact-form-7' )
			->andReturn( $this->cf7_response )
			->getMock();
		$package = Mockery::mock( Package::class )
			->shouldReceive( 'get_type' )
			->twice()
			->andReturn( 'plugin' )
			->shouldReceive( 'get_slug' )
			->twice()
			->andReturn( 'contact-form-7' )
			->getMock();

		$client = new Api_Client( $http );
		$response = $client->check( $package );

		$this->assertInstanceOf( 'Soter_Core\\Response', $response );
		$this->assertEquals(
			$this->cf7_response,
			[ $response->get_status(), $response->get_headers(), $response->get_body() ]
		);
	}

	/** @test */
	function it_can_check_themes() {
		$http = Mockery::mock( Http_Interface::class )
			->shouldReceive( 'get' )
			->once()
			->with( 'https://wpvulndb.com/api/v2/themes/twentyfifteen' )
			->andReturn( $this->twentyfifteen_response )
			->getMock();
		$package = Mockery::mock( Package::class )
			->shouldReceive( 'get_type' )
			->twice()
			->andReturn( 'theme' )
			->shouldReceive( 'get_slug' )
			->twice()
			->andReturn( 'twentyfifteen' )
			->getMock();

		$client = new Api_Client( $http );
		$response = $client->check( $package );

		$this->assertInstanceOf( 'Soter_Core\\Response', $response );
		$this->assertEquals(
			$this->twentyfifteen_response,
			[ $response->get_status(), $response->get_headers(), $response->get_body() ]
		);
	}

	/** @test */
	function it_can_check_wordpresses() {
		$http = Mockery::mock( Http_Interface::class )
			->shouldReceive( 'get' )
			->once()
			->with( 'https://wpvulndb.com/api/v2/wordpresses/474' )
			->andReturn( $this->wordpress_response )
			->getMock();
		$package = Mockery::mock( Package::class )
			->shouldReceive( 'get_type' )
			->twice()
			->andReturn( 'wordpress' )
			->shouldReceive( 'get_slug' )
			->twice()
			->andReturn( '474' )
			->shouldReceive( 'get_version' )
			->once()
			->andReturn( '4.7.4' )
			->getMock();

		$client = new Api_Client( $http );
		$response = $client->check( $package );

		$this->assertInstanceOf( 'Soter_Core\\Response', $response );
		$this->assertEquals(
			$this->wordpress_response,
			[ $response->get_status(), $response->get_headers(), $response->get_body() ]
		);
	}

	/** @test */
	function it_throws_for_unknown_package_types() {
		$this->expectException( InvalidArgumentException::class );

		$http = Mockery::mock( Http_Interface::class );
		$package = Mockery::mock( Package::class )
			->shouldReceive( 'get_type' )
			->twice()
			->andReturn( 'fake' )
			->getMock();

		$client = new Api_Client( $http );
		$response = $client->check( $package );
	}
}
