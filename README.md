# soter-core
Soter Core is a simple library for interacting with the [WPScan Vulnerability Database](https://wpvulndb.com/) API.

It contains the core logic used by my [Soter Plugin](https://github.com/ssnepenthe/soter) and [Soter WP-CLI Command](https://github.com/ssnepenthe/soter-command).

## Requirements
Composer and PHP 5.3 or greater.

## Installation
```
composer require ssnepenthe/soter-core
```

## Usage
Depending on your use-case, you should be interacting with either the `Api_Client` class or the `Checker` class.

### API Client
```
$client = new Soter_Core\Api_Client(
    new Soter_Core\Cached_Http_Client(
        new Soter_Core\WP_Http_Client( 'Some user agent string' ),
        new Soter_Core\WP_Transient_Cache( 'unique-prefix', HOUR_IN_SECONDS )
    )
);
```

The API client can check a `Soter_Core\Package` instance against the API:

```
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

`->get_vulnerabilities()` - Returns an instance of the `Soter_Core\Vulnerabilities` collection object representing all vulnerabilities that have ever affected a given package.

`->get_vulnerabilities_by_version( string $version = null )` - Returns an instance of the `Soter_Core\Vulnerabilities` collection object representing all vulnerabilities which affect a given package at the given version.

`->get_vulnerabilities_for_current_version()` - Returns an instance of the `Soter_Core\Vulnerabilities` collection object representing all vulnerabilities which affect a given package at the version checked against the API.

### Checker
```
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

Your interaction with a checker instance should be through the following methods:

`->check_site( array $ignored = array() )` - Checks the current version of all installed packages (plugins, themes and core) and returns an instance of `Soter_Core\Vulnerabilities`. An optional array of package slugs that should not be checked can be provided.

`->check_plugins( array $ignored = array() )` - Checks the current version of all installed plugins and returns an instance of `Soter_Core\Vulnerabilities`. An optional array of plugin slugs that should not be checked can be provided.

`->check_themes( array $ignored = array() )` - Checks the current version of all installed themes and returns an instance of `Soter_Core\Vulnerabilities`. An optional array of theme slugs that should not be checked can be provided.

`->check_wordpress( array $ignored = array() )` - Checks the current version of WordPress and returns an instance of `Soter_Core\Vulnerabilities`. An optional array of WordPress "slugs" that should not be checked can be provided. Keep in mind that the slug used for WordPress is the version string stripped of periods (e.g. '475' for version 4.7.5).
