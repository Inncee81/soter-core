<?php

use Soter_Core\Checker;
use Soter_Core\Package;
use Soter_Core\Api_Client;
use Soter_Core\Null_Cache;
use Soter_Core\WP_Package_Manager;

class Checker_Test extends WP_UnitTestCase {
	/** @test */
	function it_can_check_a_package() {
		$checker = $this->make_checker();
		$package = new Package( 'contact-form-7', 'plugin', '3.5' );

		$vulns = $checker->check_package( $package );

		// @todo Verify package check action is triggered.

		$this->assertEqualSets(
			array(
				'Contact Form 7 <= 3.7.1 - Security Bypass',
				'Contact Form 7 <= 3.5.2 - File Upload Remote Code Execution',
			),
			wp_list_pluck( $vulns, 'title' )
		);
	}

	/** @test */
	function it_can_check_multiple_packages() {
		$checker = $this->make_checker();
		$packages = array(
			new Package( 'contact-form-7', 'plugin', '3.7' ),
			new Package( 'twentyfifteen', 'theme', '1.1' ),
		);

		$vulns = $checker->check_packages( $packages );

		// @todo Verify packages check action is triggered.

		$this->assertEqualSets(
			array(
				'Contact Form 7 <= 3.7.1 - Security Bypass',
				'Twenty Fifteen Theme <= 1.1 - DOM Cross-Site Scripting (XSS)',
			),
			wp_list_pluck( $vulns, 'title' )
		);
	}

	/** @test */
	function it_can_ignore_some_packages_when_checking_many() {
		$checker = $this->make_checker();
		$packages = array(
			new Package( 'contact-form-7', 'plugin', '3.7' ),
			new Package( 'twentyfifteen', 'theme', '1.1' ),
		);

		$vulns = $checker->check_packages( $packages, array( 'twentyfifteen' ) );

		$this->assertEqualSets(
			array( 'Contact Form 7 <= 3.7.1 - Security Bypass' ),
			wp_list_pluck( $vulns, 'title' )
		);
	}

	/** @test */
	function it_only_returns_unique_vulnerabilities() {
		$checker = $this->make_checker();
		$packages = array(
			new Package( 'contact-form-7', 'plugin', '3.5' ),
			new Package( 'contact-form-7', 'plugin', '3.7' ),
		);

		$vulns = $checker->check_packages( $packages );

		$this->assertEqualSets(
			array(
				'Contact Form 7 <= 3.5.2 - File Upload Remote Code Execution',
				'Contact Form 7 <= 3.7.1 - Security Bypass',
			),
			wp_list_pluck( $vulns, 'title' )
		);
	}

	/** @test */
	function it_provides_access_to_api_client() {
		$checker = $this->make_checker();

		$this->assertInstanceOf( 'Soter_Core\\Api_Client', $checker->get_client() );
	}

	/** @test */
	function it_can_check_plugin_theme_and_wordpress_types() {
		$checker = $this->make_checker();
		$packages = array(
			new Package( 'contact-form-7', 'plugin', '3.7' ),
			new Package( 'twentyfifteen', 'theme', '1.1' ),
			new Package( '474', 'wordpress', '4.7.4' ),
		);

		$vulns = $checker->check_packages( $packages );

		$this->assertEqualSets(
			array(
				'Contact Form 7 <= 3.7.1 - Security Bypass',
				'Twenty Fifteen Theme <= 1.1 - DOM Cross-Site Scripting (XSS)',
				'WordPress 2.3-4.7.4 - Host Header Injection in Password Reset',
			),
			wp_list_pluck( $vulns, 'title' )
		);
	}

	protected function make_checker() {
		$http = new Filesystem_Http_Client;
		$client = new Api_Client( $http );
		$manager = new WP_Package_Manager;

		return new Checker( $client, $manager );
	}
}
