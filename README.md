# soter-core
Soter Core is a simple library for interacting with the [WPScan Vulnerability Database](https://wpvulndb.com/) API.

It contains the core logic for [Soter](https://github.com/ssnepenthe/soter) and [Soter Command](https://github.com/ssnepenthe/soter-command).

## Requirements
This package requires Composer. It *should* work down to PHP 5.3, however it is only properly tested down to PHP 5.6 since that is now the minimum required version for [10up/WP_Mock](https://github.com/10up/wp_mock).

## Installation
```
composer require ssnepenthe/soter-core
```

## Usage
Depending on your use-case, you should be interacting with either the `Api_Client` class or the `Checker` class.

### API Client
```PHP
$client = new Soter_Core\Api_Client(
    new Soter_Core\Cached_Http_Client(
        new Soter_Core\WP_Http_Client( 'Some user agent string' ),
        new Soter_Core\WP_Transient_Cache( 'unique-prefix', HOUR_IN_SECONDS )
    )
);
```

The API client exposes a `->check()` method which can be used to check a `Soter_Core\Package` instance against the API:

```PHP
$plugin = new Soter_Core\Package( 'contact-form-7', Soter_Core\Package::TYPE_PLUGIN, '4.9' );
$response = $client->check( $plugin );

$theme = new Soter_Core\Package( 'twentyfifteen', Soter_Core\Package::TYPE_THEME, '1.8' );
$response = $client->check( $theme );

// WordPress "slug" is the version string stripped of periods.
$wordpress = new Soter_Core\Package( '481', Soter_Core\Package::TYPE_WORDPRESS, '4.8.1' );
$response = $client->check( $wordpress );
```

Responses will be an instance of `Soter_Core\Response`. You can check package vulnerabilities using the following methods:

`->has_vulnerabilities()` - Returns a boolean value indicating whether there are any recorded vulnerabilities for a given package.

`->get_vulnerabilities()` - Returns an instance of `Soter_Core\Vulnerabilities` representing all vulnerabilities that have ever affected a given package.

`->get_vulnerabilities_by_version( string $version = null )` - Returns an instance of `Soter_Core\Vulnerabilities` representing all vulnerabilities which affect a given package at the given version.

`->get_vulnerabilities_for_current_version()` - Returns an instance of `Soter_Core\Vulnerabilities` representing all vulnerabilities which affect a given package at the version checked against the API.

### Checker
```PHP
$checker = new Soter_Core\Checker(
    new Soter_Core\Api_Client(
        new Soter_Core\Cached_Http_Client(
            new Soter_Core\WP_Http_Client( 'Some user agent string' ),
            new Soter_Core\WP_Transient_Cache( 'unique-prefix', HOUR_IN_SECONDS )
        )
    ),
    new Soter_Core\WP_Package_Manager()
);
```

The following methods are available on a checker instance:

`->check_site( array $ignored = array() )` - Checks the current version of all installed packages (plugins, themes and core) and returns an instance of `Soter_Core\Vulnerabilities`. An optional array of package slugs that should not be checked can be provided.

`->check_plugins( array $ignored = array() )` - Checks the current version of all installed plugins and returns an instance of `Soter_Core\Vulnerabilities`. An optional array of plugin slugs that should not be checked can be provided.

`->check_themes( array $ignored = array() )` - Checks the current version of all installed themes and returns an instance of `Soter_Core\Vulnerabilities`. An optional array of theme slugs that should not be checked can be provided.

`->check_wordpress( array $ignored = array() )` - Checks the current version of WordPress and returns an instance of `Soter_Core\Vulnerabilities`. An optional array of WordPress "slugs" that should not be checked can be provided. Keep in mind that the slug used for WordPress is the version string stripped of periods (e.g. '475' for version 4.7.5).

You can also add any number of callbacks to be run after each package is checked.

Each callback will be called with a `Soter_Core\Vulnerabilities` instance and a `Soter_Core\Response` instance.

As a simple example, you might do something like the following to log error responses for debugging purposes:

```PHP
$checker->add_post_check_callback( function( $vulnerabilities, $response ) {
    if ( ! $response->is_error() ) {
        return;
    }

    // Ex: "Error checking plugin not-a-real-plugin with message: Non-200 status code received"
    $this->logger->debug( 'Error checking {type} {slug} with message: {message}', [
        'message' => $response->error['message'],
        'slug' => $response->get_package()->get_slug(),
        'type' => $response->get_package()->get_type(),
    ] );
} );
```

## Testing

To run the tests first ensure you have PHPUnit and WP_Mock installed globally:

```
composer global require phpunit/phpunit="^8.0" 10up/wp_mock="^0.4"
```

And then run the tests from the soter-core directory:

```
phpunit
```

Coding style is enforced with WPCS - first ensure you have phpcs and wpcs installed globally:

```
composer global require squizlabs/php_codesniffer="^3.4" wp-coding-standards/wpcs="^2.1"
```

Then configure phpcs to load wpcs:

```
phpcs --config-set installed_paths $HOME/.composer/vendor/wp-coding-standards/wpcs
```

And finally run phpcs from the soter-core directory:

```
phpcs
```
