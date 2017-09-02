<?php

use Soter_Core\Package;

class Package_Test extends WP_UnitTestCase {
	/** @test */
	function it_can_be_created_from_plugin_data() {
		$plugins = include __DIR__ . '/../fixtures/get-plugins.php';

		$plugin = current( $plugins );
		$file = key( $plugins );

		if ( false === strpos( $file, '/' ) ) {
			$slug = basename( $file, '.php' );
		} else {
			$slug = dirname( $file );
		}

		$package = Package::from_plugin_array( $file, $plugin );

		$this->assertEquals( $slug, $package->get_slug() );
		$this->assertEquals( Package::TYPE_PLUGIN, $package->get_type() );
		$this->assertEquals( $plugin['Version'], $package->get_version() );
	}

	/** @test */
	function it_can_be_created_from_a_theme_object() {
		$themes = wp_get_themes();

		if ( ! isset( $themes['twentyseventeen'] ) ) {
			$this->fail( 'twentyseventeen theme does not seem to be installed' );
		}

		$theme = $themes['twentyseventeen'];

		$package = Package::from_theme_object( $theme );

		$this->assertEquals( 'twentyseventeen', $package->get_slug() );
		$this->assertEquals( Package::TYPE_THEME, $package->get_type() );
		$this->assertEquals( $theme->get( 'Version' ), $package->get_version() );
	}

	/** @test */
	function it_can_be_created_from_current_wp_environment() {
		$version = get_bloginfo( 'version' );
		$slug = str_replace( '.', '', $version );

		$package = Package::from_wordpress_env();

		$this->assertEquals( $slug, $package->get_slug() );
		$this->assertEquals( Package::TYPE_WORDPRESS, $package->get_type() );
		$this->assertEquals( $version, $package->get_version() );
	}
}
