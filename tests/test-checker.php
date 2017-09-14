<?php

use Soter_Core\Checker;
use Soter_Core\Package;
use Soter_Core\Response;
use Soter_Core\Api_Client;
use Soter_Core\Vulnerabilities;
use Soter_Core\Package_Manager_Interface;

/**
 * @todo Add tests for type-specific methods.
 *       These mock chains are a bit ridiculous...
 */
class Checker_Test extends WP_Mock\Tools\TestCase {
	/** @test */
	function it_can_check_a_package() {
		$package = Mockery::mock( Package::class );
		$collection = Mockery::mock( Vulnerabilities::class );
		$checker = new Checker(
			Mockery::mock( Api_Client::class )
				->shouldReceive( 'check' )
				->once()
				->with( $package )
				->andReturn(
					Mockery::mock( Response::class )
						->shouldReceive( 'get_vulnerabilities_for_current_version' )
						->once()
						->andReturn( $collection )
						->getMock()
				)
				->getMock(),
			Mockery::mock( Package_Manager_Interface::class )
		);

		// Test that we call client->check()->get_vulnerabilities_for_current_version().
		$this->assertSame( $collection, $checker->check_package( $package ) );
	}

	/** @test */
	function it_can_check_multiple_packages() {
		$p_one = Mockery::mock( Package::class );
		$p_two = Mockery::mock( Package::class );

		$checker = new Checker(
			Mockery::mock( Api_Client::class )
				->shouldReceive( 'check' )
				->once()
				->with( $p_one )
				->andReturn(
					Mockery::mock( Response::class )
						->shouldReceive( 'get_vulnerabilities_for_current_version' )
						->once()
						->andReturn(
							Mockery::mock( Vulnerabilities::class )
								->shouldReceive( 'all' )
								->once()
								->andReturn( [] )
								->getMock()
						)
						->getMock()
				)
				->shouldReceive( 'check' )
				->once()
				->with( $p_two )
				->andReturn(
					Mockery::mock( Response::class )
						->shouldReceive( 'get_vulnerabilities_for_current_version' )
						->once()
						->andReturn(
							Mockery::mock( Vulnerabilities::class )
								->shouldReceive( 'all' )
								->once()
								->andReturn( [] )
								->getMock()
						)
						->getMock()
				)
				->getMock(),
			Mockery::mock( Package_Manager_Interface::class )
		);

		// No assertions - just testing that everything is actually called...
		$vulns = $checker->check_packages( [ $p_one, $p_two ] );
	}

	/** @test */
	function it_can_ignore_some_packages_when_checking_many() {
		$packages = array(
			Mockery::mock( Package::class )
				->shouldReceive( 'get_slug' )
				->once()
				->andReturn( 'contact-form-7' )
				->getMock(),
			Mockery::mock( Package::class )
				->shouldReceive( 'get_slug' )
				->once()
				->andReturn( 'twentyfifteen' )
				->getMock(),
		);

		$checker = new Checker(
			Mockery::mock( Api_Client::class )
				->shouldReceive( 'check' )
				->once()
				->with( $packages[0] )
				->andReturn(
					Mockery::mock( Response::class )
						->shouldReceive( 'get_vulnerabilities_for_current_version' )
						->once()
						->andReturn(
							Mockery::mock( Vulnerabilities::class )
								->shouldReceive( 'all' )
								->once()
								->andReturn( [] )
								->getMock()
						)
						->getMock()
				)
				->getMock(),
			Mockery::mock( Package_Manager_Interface::class )
		);

		// No assertions - checking that everything is called for first package, not second.
		$vulns = $checker->check_packages( $packages, array( 'twentyfifteen' ) );
	}

	/** @test */
	function it_triggers_callback_after_every_package_check() {
		$vulnerabilities = Mockery::mock( Vulnerabilities::class );
		$response = Mockery::mock( Response::class )
			->shouldReceive( 'get_vulnerabilities_for_current_version' )
			->twice()
			->andReturn( $vulnerabilities )
			->getMock();
		$checker = new Checker(
			Mockery::mock( Api_Client::class )
				->shouldReceive( 'check' )
				->twice()
				->andReturn( $response )
				->getMock(),
			Mockery::mock( Package_Manager_Interface::class )
		);
		$package1 = Mockery::mock( Package::class );
		$package2 = Mockery::mock( Package::class );
		$call_count = 0;

		$checker->add_post_check_callback(
			function( $vulns, $resp ) use ( $vulnerabilities, $response, &$call_count ) {
				$call_count++;
				$this->assertSame( $vulnerabilities, $vulns );
				$this->assertSame( $response, $resp );
			}
		);
		$checker->check_package( $package1 );
		$checker->check_package( $package2 );

		$this->assertSame( 2, $call_count );
	}

	/** @test */
	function it_can_trigger_multiple_callbacks() {
		$vulnerabilities = Mockery::mock( Vulnerabilities::class );
		$response = Mockery::mock( Response::class )
			->shouldReceive( 'get_vulnerabilities_for_current_version' )
			->once()
			->andReturn( $vulnerabilities )
			->getMock();
		$checker = new Checker(
			Mockery::mock( Api_Client::class )
				->shouldReceive( 'check' )
				->once()
				->andReturn( $response )
				->getMock(),
			Mockery::mock( Package_Manager_Interface::class )
		);
		$package = Mockery::mock( Package::class );

		$checker->add_post_check_callback( function( $vulns ) use ( $vulnerabilities ) {
			$this->assertSame( $vulnerabilities, $vulns );
		} );
		$checker->add_post_check_callback( function( $_, $resp ) use ( $response ) {
			$this->assertSame( $response, $resp );
		} );

		$checker->check_package( $package );
	}
}
