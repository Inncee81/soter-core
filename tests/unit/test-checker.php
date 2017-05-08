<?php

use Soter_Core\Checker;

class Checker_Test extends PHPUnit_Framework_TestCase {
	function setUp() {
		WP_Mock::setUp();
	}

	function tearDown() {
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

		$checker = new Checker( Mockery::mock( 'Soter_Core\\Api_Client' ) );
		$packages = $checker->get_packages();
		$slugs = array_map( function( Soter_Core\Package $package ) {
			return $package->get_slug();
		}, $packages );

		$this->assertSame( 8, $checker->get_package_count() );
		$this->assertEquals( array(
			'akismet',
			'contact-form-7',
			'debug-bar',
			'hello.php',
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

		$checker = new Checker( Mockery::mock( 'Soter_Core\\Api_Client' ) );
		$plugins = $checker->get_plugins();
		$slugs = array_map( function( Soter_Core\Package $package ) {
			return $package->get_slug();
		}, $plugins );

		array_walk( $plugins, [ $this, 'assert_package_is_plugin' ] );
		$this->assertSame( 4, $checker->get_plugin_count() );
		$this->assertEquals(
			// @todo Need to revisit single-file plugins - hello.php should be hello-dolly.
			array( 'akismet', 'contact-form-7', 'debug-bar', 'hello.php' ),
			$slugs
		);
	}

	/** @test */
	function it_can_provide_a_list_of_all_installed_themes() {
		WP_Mock::userFunction( 'wp_get_themes', array(
			'return' => static::wp_get_themes(),
			'times' => 1,
		) );

		$checker = new Checker( Mockery::mock( 'Soter_Core\\Api_Client' ) );
		$themes = $checker->get_themes();
		$slugs = array_map( function( Soter_Core\Package $package ) {
			return $package->get_slug();
		}, $themes );

		array_walk( $themes, [ $this, 'assert_package_is_theme' ] );
		$this->assertSame( 3, $checker->get_theme_count() );
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

		$checker = new Checker( Mockery::mock( 'Soter_Core\\Api_Client' ) );
		$wordpresses = $checker->get_wordpress();
		$slugs = array_map( function( Soter_Core\Package $package ) {
			return $package->get_slug();
		}, $wordpresses );

		array_walk( $wordpresses, [ $this, 'assert_package_is_wordpress' ] );
		$this->assertSame( 1, $checker->get_wordpress_count() );
		$this->assertEquals( array( '474' ), $slugs );
	}

	protected static function get_plugins() {
		$plugins = include __DIR__ . '/../fixtures/get-plugins.php';

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
