<?php

use Soter_Core\Checker;
use Soter_Core\Package;
use Soter_Core\Response;
use Soter_Core\Api_Client;
use Soter_Core\Vulnerabilities;
use Soter_Core\Package_Manager_Interface;

/**
 * @todo Add tests for type-specific methods.
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
						->getMock()
				)
				->shouldReceive( 'check' )
				->once()
				->with( $p_two )
				->andReturn(
					Mockery::mock( Response::class )
						->shouldReceive( 'get_vulnerabilities_for_current_version' )
						->once()
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
						->getMock()
				)
				->getMock(),
			Mockery::mock( Package_Manager_Interface::class )
		);

		// No assertions - checking that everything is called for first package, not second.
		$vulns = $checker->check_packages( $packages, array( 'twentyfifteen' ) );
	}
}
