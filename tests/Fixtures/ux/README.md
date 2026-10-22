# AcmeUxBundle

AcmeUxBundle for Symfony

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
composer require acme/ux-bundle
```

Then, follow the instructions from *Step 3* below.

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require acme/ux-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Acme\UxBundle\AcmeUxBundle::class => ['all' => true],
];
```

#### Step 3: Enable the Stimulus Controller

The bundle provides a [Stimulus](https://stimulus.hotwired.dev/) controller, so
your application needs [StimulusBundle](https://symfony.com/bundles/StimulusBundle/current/index.html).
Symfony Flex enables the controller for you. Without Flex, enable it in the
`assets/controllers.json` file of your application:

```json
{
    "controllers": {
        "@acme/ux-bundle": {
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
(`"@acme/ux-bundle": "file:vendor/acme/ux-bundle/assets"`), then
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

The changes of each version are listed in the [changelog](CHANGELOG.md).

## License

AcmeUxBundle is released under the MIT License, see the [LICENSE](LICENSE) file.
