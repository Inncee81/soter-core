<?php

use Soter_Core\Package;
use Soter_Core\Composer_Package_Manager;
use SSNepenthe\ComposerUtilities\WordPress\Lock;

/**
 * These tests are only meant to be run locally, not on Travis.
 *
 * In order for them to run you must install ssnepenthe/composer-utilities and then
 * run the "local-integration" test suite.
 */
class Composer_Package_Manager_Test extends WP_UnitTestCase {
	protected $manager;

	function setUp() {
		$this->manager = new Composer_Package_Manager(
			new Lock( __DIR__ . '/../fixtures/test-composer.lock' )
		);
	}

	function tearDown() {
		$this->manager = null;
	}

	/** @test */
	function it_can_get_all_packages() {
		$packages = $this->manager->get_packages();

		$this->assertSame( 7, count( $packages ) );
		array_walk( $packages, [ $this, 'assert_package_instance' ] );
	}

	/** @test */
	function it_can_get_all_plugins() {
		$plugins = $this->manager->get_plugins();

		$this->assertSame( 5, count( $plugins ) );

		// Test all for type.
		array_walk( $plugins, [ $this, 'assert_package_is_plugin' ] );

		// Spot test slug and version.
		$this->assertEquals( 'debug-bar', $plugins[0]->get_slug() );
		$this->assertEquals( '0.8.4', $plugins[0]->get_version() );
	}

	/** @test */
	function it_can_get_all_themes() {
		$themes = $this->manager->get_themes();

		$this->assertSame( 1, count( $themes ) );

		$this->assertEquals( 'theme', $themes[0]->get_type() );
		$this->assertEquals( 'hew', $themes[0]->get_slug() );
		$this->assertEquals( '1.0.4', $themes[0]->get_version() );
	}

	/** @test */
	function it_can_get_all_wordpresses() {
		// @todo johnpbloch/wordpress has a 4.7.4.1 tag which isn't a real version...
		$wordpresses = $this->manager->get_wordpresses();

		$this->assertSame( 1, count( $wordpresses ) );

		$this->assertEquals( 'wordpress', $wordpresses[0]->get_type() );
		$this->assertEquals( '474', $wordpresses[0]->get_slug() );
		$this->assertEquals( '4.7.4', $wordpresses[0]->get_version() );
	}

	protected function assert_package_instance( $package ) {
		$this->assertInstanceOf( 'Soter_Core\\Package_Interface', $package );
	}

	protected function assert_package_is_plugin( Package $package ) {
		$this->assertEquals( 'plugin', $package->get_type() );
	}
}
