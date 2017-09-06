<?php

use Soter_Core\Checker;
use Soter_Core\Package;
use Soter_Core\Api_Client;
use Soter_Core\WP_Package_Manager;

class Checker_Test extends WP_UnitTestCase {
	protected $checker;

	public function setUp() {
		parent::setUp();

		$http = new Filesystem_Http_Client;
		$client = new Api_Client( $http );
		$manager = new WP_Package_Manager;

		$this->checker = new Checker( $client, $manager );
	}

	public function tearDown() {
		parent::tearDown();

		$this->checker = null;
	}

	/** @test */
	function it_can_check_a_package() {
		$package = new Package( 'contact-form-7', 'plugin', '3.5' );

		$vulns = $this->checker->check_package( $package );

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
		$packages = array(
			new Package( 'contact-form-7', 'plugin', '3.7' ),
			new Package( 'twentyfifteen', 'theme', '1.1' ),
		);

		$vulns = $this->checker->check_packages( $packages );

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
		$packages = array(
			new Package( 'contact-form-7', 'plugin', '3.7' ),
			new Package( 'twentyfifteen', 'theme', '1.1' ),
		);

		$vulns = $this->checker->check_packages( $packages, array( 'twentyfifteen' ) );

		$this->assertEqualSets(
			array( 'Contact Form 7 <= 3.7.1 - Security Bypass' ),
			wp_list_pluck( $vulns, 'title' )
		);
	}

	/** @test */
	function it_only_returns_unique_vulnerabilities() {
		$packages = array(
			new Package( 'contact-form-7', 'plugin', '3.5' ),
			new Package( 'contact-form-7', 'plugin', '3.7' ),
		);

		$vulns = $this->checker->check_packages( $packages );

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
		$this->assertInstanceOf( 'Soter_Core\\Api_Client', $this->checker->get_client() );
	}

	/** @test */
	function it_can_check_plugin_theme_and_wordpress_types() {
		$packages = array(
			new Package( 'contact-form-7', 'plugin', '3.7' ),
			new Package( 'twentyfifteen', 'theme', '1.1' ),
			new Package( '474', 'wordpress', '4.7.4' ),
		);

		$vulns = $this->checker->check_packages( $packages );

		$this->assertEqualSets(
			array(
				'Contact Form 7 <= 3.7.1 - Security Bypass',
				'Twenty Fifteen Theme <= 1.1 - DOM Cross-Site Scripting (XSS)',
				'WordPress 2.3-4.7.4 - Host Header Injection in Password Reset',
			),
			wp_list_pluck( $vulns, 'title' )
		);
	}
}
