<?php

use WP_Mock\Tools\TestCase;
use Soter_Core\WP_Package_Manager;

class WP_Package_Manager_Test extends TestCase {
	function setUp() : void {
		WP_Mock::setUp();
	}

	function tearDown() : void {
		WP_Mock::tearDown();
	}

	// Not technically unit tests? Needs to be in the unit suite to allow mocking
	// core functions to be able to return a known state.

	/** @test */
	function it_can_provide_a_list_of_all_installed_packages() {
		WP_Mock::userFunction( 'get_plugins', array(
			'return' => static::get_plugins(),
			'times' => 1,
		) );
		WP_Mock::userFunction( 'wp_get_themes', array(
			'return' => static::wp_get_themes(),
			'times' => 1,
		) );
		WP_Mock::userFunction( 'get_bloginfo', array(
			'args' => 'version',
			'return' => '4.7.4',
			'times' => 1,
		) );

		$manager = new WP_Package_Manager;
		$packages = $manager->get_packages();
		$slugs = array_map( function( Soter_Core\Package $package ) {
			return $package->get_slug();
		}, $packages );

		$this->assertSame( 8, count( $packages ) );
		$this->assertEquals( array(
			'akismet',
			'contact-form-7',
			'debug-bar',
			'hello',
			'twentyfifteen',
			'twentysixteen',
			'twentyseventeen',
			'474',
		), $slugs );
	}

	/** @test */
	function it_can_provide_a_list_of_all_installed_plugins() {
		WP_Mock::userFunction( 'get_plugins', array(
			'return' => static::get_plugins(),
			'times' => 1,
		) );

		$manager = new WP_Package_Manager;
		$plugins = $manager->get_plugins();
		$slugs = array_map( function( Soter_Core\Package $package ) {
			return $package->get_slug();
		}, $plugins );

		array_walk( $plugins, array( $this, 'assert_package_is_plugin' ) );
		$this->assertSame( 4, count( $plugins ) );
		$this->assertEquals(
			// @todo Need to revisit single-file plugins - hello should be hello-dolly.
			array( 'akismet', 'contact-form-7', 'debug-bar', 'hello' ),
			$slugs
		);
	}

	/** @test */
	function it_can_provide_a_list_of_all_installed_themes() {
		WP_Mock::userFunction( 'wp_get_themes', array(
			'return' => static::wp_get_themes(),
			'times' => 1,
		) );

		$manager = new WP_Package_Manager;
		$themes = $manager->get_themes();
		$slugs = array_map( function( Soter_Core\Package $package ) {
			return $package->get_slug();
		}, $themes );

		array_walk( $themes, array( $this, 'assert_package_is_theme' ) );
		$this->assertSame( 3, count( $themes ) );
		$this->assertEquals(
			array( 'twentyfifteen', 'twentysixteen', 'twentyseventeen' ),
			$slugs
		);
	}

	/** @test */
	function it_can_provide_a_list_of_all_installed_wordpress_versions() {
		WP_Mock::userFunction( 'get_bloginfo', array(
			'args' => 'version',
			'return' => '4.7.4',
			'times' => 1,
		) );

		$manager = new WP_Package_Manager;
		$wordpresses = $manager->get_wordpresses();
		$slugs = array_map( function( Soter_Core\Package $package ) {
			return $package->get_slug();
		}, $wordpresses );

		array_walk( $wordpresses, array( $this, 'assert_package_is_wordpress' ) );
		$this->assertSame( 1, count( $wordpresses ) );
		$this->assertEquals( array( '474' ), $slugs );
	}

	protected static function get_plugins() {
		$plugins = include __DIR__ . '/fixtures/get-plugins.php';

		return $plugins;
	}

	protected static function wp_get_themes() {
		$twentyfifteen = Mockery::mock( 'WP_Theme' )
			->shouldReceive( 'get_stylesheet' )
			->once()
			->andReturn( 'twentyfifteen' )
			->shouldReceive( 'get' )
			->with( 'Version' )
			->once()
			->andReturn( '1.7' )
			->getMock();
		$twentysixteen = Mockery::mock( 'WP_Theme' )
			->shouldReceive( 'get_stylesheet' )
			->once()
			->andReturn( 'twentysixteen' )
			->shouldReceive( 'get' )
			->with( 'Version' )
			->once()
			->andReturn( '1.3' )
			->getMock();
		$twentyseventeen = Mockery::mock( 'WP_Theme' )
			->shouldReceive( 'get_stylesheet' )
			->once()
			->andReturn( 'twentyseventeen' )
			->shouldReceive( 'get' )
			->with( 'Version' )
			->once()
			->andReturn( '1.2' )
			->getMock();

		return compact( 'twentyfifteen', 'twentysixteen', 'twentyseventeen' );
	}

	protected function assert_package_is_plugin( Soter_Core\Package $package ) {
		$this->assertEquals( 'plugin', $package->get_type() );
	}

	protected function assert_package_is_theme( Soter_Core\Package $package ) {
		$this->assertEquals( 'theme', $package->get_type() );
	}

	protected function assert_package_is_wordpress( Soter_Core\Package $package ) {
		$this->assertEquals( 'wordpress', $package->get_type() );
	}
}
