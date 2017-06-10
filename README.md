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
    new Soter_Core\Wp_Http_Client( 'Some user agent string' )
);
```

**Note:** There is also a `Soter_Core\Cached_Http_Client` class that should generally be used in place of the `Wp_Http_Client` shown above. Check `src/class-cached-http-client.php` for more info.

The API client has a method for each of the three API endpoints:

```
$plugin_response = $client->plugins( 'contact-form-7' );
$theme_response = $client->themes( 'twentyfifteen' );
$wordpress_response = $client->wordpresses( '475' );
```

Responses will be an instance of `Soter_Core\Api_Response`. You can check package vulnerabilities using the following methods:

`->has_vulnerabilities()` - Returns a boolean value indicating whether there are any recorded vulnerabilities for a given package.

`->get_vulnerabilities()` - Returns an array of `Soter_Core\Api_Vulnerability` objects representing all vulnerabilities that have ever affected a given package.

`->get_vulnerabilities_by_version( string $version = null )` - Returns an array of vulnerability objects which affect a given package at the given version.

### Checker
```
$checker = new Soter_Core\Checker(
    new Soter_Core\Api_Client(
        new Soter_Core\Wp_Http_Client( 'Some user agent string' )
    ),
    new Soter_Core\Wp_Package_Manager
);
```

Your interaction with a checker instance should be through the following methods:

`->check_site( array $ignored = array() )` - Checks the current version of all installed packages (plugins, themes and core) and returns an array of vulnerability objects. An optional array of package slugs that should not be checked can be provided.

`->check_plugins( array $ignored = array() )` - Checks the current version of all installed plugins and returns an array of vulnerability objects. An optional array of plugin slugs that should not be checked can be provided.

`->check_themes( array $ignored = array() )` - Checks the current version of all installed themes and returns an array of vulnerability objects. An optional array of theme slugs that should not be checked can be provided.

`->check_wordpress( array $ignored = array() )` - Checks the current version of WordPress and returns an array of vulnerability objects. An optional array of WordPress "slugs" that should not be checked can be provided. Keep in mind that the slug used for WordPress is the version stripped of non-numeric characters (e.g. '475' for version 4.7.5).
