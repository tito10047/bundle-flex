# AcmeQualityAssuranceBundle

AcmeQualityAssuranceBundle for Symfony

[![CI](https://github.com/acme/quality-assurance-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/acme/quality-assurance-bundle/actions/workflows/ci.yml)

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
composer require acme/quality-assurance-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require acme/quality-assurance-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Acme\QualityAssuranceBundle\AcmeQualityAssuranceBundle::class => ['all' => true],
];
```

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

AcmeQualityAssuranceBundle is released under the MIT License, see the [LICENSE](LICENSE) file.
