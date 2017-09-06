<?php

use Soter_Core\Package;
use Soter_Core\Api_Client;

class Api_Client_Test extends WP_UnitTestCase {
	protected $api_client;

	public function setUp() {
		parent::setUp();

		$this->api_client = new Api_Client( new Filesystem_Http_Client );
	}

	public function tearDown() {
		parent::tearDown();

		$this->api_client = null;
	}

	/** @test */
	function it_can_check_plugins() {
		$plugins = include __DIR__ . '/../fixtures/get-plugins.php';

		$response = $this->api_client->check( Package::from_plugin_array(
			'contact-form-7',
			$plugins['contact-form-7/wp-contact-form-7.php']
		) );

		$this->assertInstanceOf( 'Soter_Core\\Api_Response', $response );
		$this->assertEquals(
			'{"contact-form-7":{"latest_version":"4.7","last_updated":"2017-03-03T19:28:00.000Z","popular":true,"vulnerabilities":[{"id":7020,"title":"Contact Form 7 <= 3.7.1 - Security Bypass ","created_at":"2014-08-01T10:59:06.000Z","updated_at":"2015-05-15T13:48:25.000Z","published_date":null,"references":{"url":["http://www.securityfocus.com/bid/66381/"],"cve":["2014-2265"]},"vuln_type":"AUTHBYPASS","fixed_in":"3.7.2"},{"id":7022,"title":"Contact Form 7 <= 3.5.2 - File Upload Remote Code Execution","created_at":"2014-08-01T10:59:07.000Z","updated_at":"2015-05-15T13:48:25.000Z","published_date":null,"references":{"url":["http://packetstormsecurity.com/files/124154/"]},"vuln_type":"UPLOAD","fixed_in":"3.5.3"}]}}',
			$response->get_body()
		);
	}

	/** @test */
	function it_can_check_themes() {
		$themes = wp_get_themes();

		if ( ! isset( $themes['twentyfifteen'] ) ) {
			$this->fail( 'twentyfifteen theme does not appear to be isntalled' );
		}

		$response = $this->api_client->check(
			Package::from_theme_object( $themes['twentyfifteen'] )
		);

		$this->assertInstanceOf( 'Soter_Core\\Api_Response', $response );
		$this->assertEquals(
			'{"twentyfifteen":{"latest_version":"1.7","last_updated":"2016-12-06T00:00:00.000Z","popular":true,"vulnerabilities":[{"id":7965,"title":"Twenty Fifteen Theme <= 1.1 - DOM Cross-Site Scripting (XSS)","created_at":"2015-05-06T17:22:10.000Z","updated_at":"2015-05-15T13:49:28.000Z","published_date":"2015-05-06T00:00:00.000Z","references":{"url":["https://blog.sucuri.net/2015/05/jetpack-and-twentyfifteen-vulnerable-to-dom-based-xss-millions-of-wordpress-websites-affected-millions-of-wordpress-websites-affected.html","http://packetstormsecurity.com/files/131802/","http://seclists.org/fulldisclosure/2015/May/41"],"cve":["2015-3429"]},"vuln_type":"XSS","fixed_in":"1.2"}]}}',
			$response->get_body()
		);
	}

	/** @test */
	function it_can_check_wordpresses() {
		$response = $this->api_client->check(
			new Package( '474', Package::TYPE_WORDPRESS, '4.7.4' )
		);

		$this->assertInstanceOf( 'Soter_Core\\Api_Response', $response );
		$this->assertEquals(
			'{"4.7.4":{"release_date":"2017-04-20","changelog_url":"https://codex.wordpress.org/Version_4.7.4","vulnerabilities":[{"id":8807,"title":"WordPress 2.3-4.7.4 - Host Header Injection in Password Reset","created_at":"2017-05-05T09:47:44.000Z","updated_at":"2017-05-05T09:48:40.000Z","published_date":"2017-05-03T00:00:00.000Z","references":{"url":["https://exploitbox.io/vuln/WordPress-Exploit-4-7-Unauth-Password-Reset-0day-CVE-2017-8295.html","http://blog.dewhurstsecurity.com/2017/05/04/exploitbox-wordpress-security-advisories.html"],"cve":["2017-8295"]},"vuln_type":"UNKNOWN","fixed_in":null}]}}',
			$response->get_body()
		);
	}

	/** @test */
	function it_throws_for_unknown_package_types() {
		$this->setExpectedException( 'InvalidArgumentException' );

		$response = $this->api_client->check( new Package( 'test', 'fake', '0.1.0' ) );
	}
}
