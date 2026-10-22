# MyVendorWebYamlBundle

MyVendorWebYamlBundle for Symfony

[![CI](https://github.com/my-vendor/web-yaml-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/my-vendor/web-yaml-bundle/actions/workflows/ci.yml)

## Requirements

- PHP 8.2 or higher (8.4 or higher with Symfony 8)
- Symfony 7.4 LTS or 8.x

## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
composer require my-vendor/web-yaml-bundle
```

Then, follow the instructions from *Step 3* below.

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require my-vendor/web-yaml-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    MyVendor\WebYamlBundle\MyVendorWebYamlBundle::class => ['all' => true],
];
```

#### Step 3: Import the Routes

Import the routes of the bundle in your application, under the prefix of
your choice:

```yaml
# config/routes/my_vendor_web_yaml.yaml
my_vendor_web_yaml:
    resource: '@MyVendorWebYamlBundle/config/routes.yaml'
    prefix: /my-vendor-web-yaml
```

Or in PHP:

```php
// config/routes/my_vendor_web_yaml.php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('@MyVendorWebYamlBundle/config/routes.yaml')
        ->prefix('/my-vendor-web-yaml')
    ;
};
```

#### Step 4: Install the Assets

Install the web assets of the bundle in the `public/bundles/myvendorwebyaml/`
directory of your application (Symfony Flex runs this command after each
`composer require` and `composer update`):

```console
php bin/console assets:install
```

#### Step 5: Enable the Stimulus Controller

The bundle provides a [Stimulus](https://stimulus.hotwired.dev/) controller, so
your application needs [StimulusBundle](https://symfony.com/bundles/StimulusBundle/current/index.html).
Symfony Flex enables the controller for you. Without Flex, enable it in the
`assets/controllers.json` file of your application:

```json
{
    "controllers": {
        "@my-vendor/web-yaml-bundle": {
            "hello": {
                "enabled": true,
                "fetch": "lazy"
            }
        }
    },
    "entrypoints": []
}
```

With AssetMapper, also add Stimulus to the importmap of your application, if it
is not there yet:

```console
php bin/console importmap:require @hotwired/stimulus
```

With Webpack Encore, add the bundle to the dependencies of your `package.json`
(`"@my-vendor/web-yaml-bundle": "file:vendor/my-vendor/web-yaml-bundle/assets"`), then
run `npm install --force` and rebuild your assets.

## Documentation

Read the [documentation](docs/index.md) to learn how to use the bundle.

## Development

Install the dependencies and run the tests:

```console
composer install
composer test
```

| Command | Description |
|---|---|
| `composer qa` | Runs every quality check |
| `composer test` | Runs the tests (PHPUnit) |
| `composer phpstan` | Runs the static analysis (PHPStan) |
| `composer cs` | Checks the coding standards (PHP-CS-Fixer) |
| `composer cs:fix` | Fixes the coding standards (PHP-CS-Fixer) |
| `composer rector` | Checks the code for automated refactorings (Rector) |
| `composer rector:fix` | Applies the automated refactorings (Rector) |

The changes of each version are listed in the [changelog](CHANGELOG.md).

## License

MyVendorWebYamlBundle is proprietary software, see the [LICENSE](LICENSE) file.
