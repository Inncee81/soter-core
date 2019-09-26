<?php

use Soter_Core\Package;
use WP_Mock\Tools\TestCase;

class Package_Test extends TestCase {
	/** @test */
	function it_can_be_created_from_plugin_data() {
		$file = 'akismet/akismet.php';
		$headers = [
			'Name' => 'Akismet Anti-Spam',
			'PluginURI' => 'https://akismet.com/',
			'Version' => '3.3.1',
			'Description' => 'Used by millions, Akismet is quite possibly the best way in the world to <strong>protect your blog from spam</strong>. It keeps your site protected even while you sleep. To get started: activate the Akismet plugin and then go to your Akismet Settings page to set up your API key.',
			'Author' => 'Automattic',
			'AuthorURI' => 'https://automattic.com/wordpress-plugins/',
			'TextDomain' => 'akismet',
			'DomainPath' => '',
			'Network' => false,
			'Title' => 'Akismet Anti-Spam',
			'AuthorName' => 'Automattic',
		];

		$package = Package::from_plugin_array( $file, $headers );

		$this->assertEquals( 'akismet', $package->get_slug() );
		$this->assertEquals( Package::TYPE_PLUGIN, $package->get_type() );
		$this->assertEquals( '3.3.1', $package->get_version() );
	}

	/** @test */
	function it_can_be_created_from_a_theme_object() {
		$theme = Mockery::mock( 'WP_Theme' )
			->shouldReceive( 'get_stylesheet' )
			->once()
			->andReturn( 'twentyseventeen' )
			->shouldReceive( 'get' )
			->once()
			->with( 'Version' )
			->andReturn( '1.3' )
			->getMock();

		$package = Package::from_theme_object( $theme );

		$this->assertEquals( 'twentyseventeen', $package->get_slug() );
		$this->assertEquals( Package::TYPE_THEME, $package->get_type() );
		$this->assertEquals( '1.3', $package->get_version() );
	}

	/** @test */
	function it_can_be_created_from_current_wp_environment() {
		WP_Mock::userFunction( 'get_bloginfo', [
			'args' => 'version',
			'return' => '4.8.1',
			'times' => 1,
		] );

		$package = Package::from_wordpress_env();

		$this->assertEquals( '481', $package->get_slug() );
		$this->assertEquals( Package::TYPE_WORDPRESS, $package->get_type() );
		$this->assertEquals( '4.8.1', $package->get_version() );
	}
}
