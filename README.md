# Bundle Flex

Composer plugin for the [Symfony Bundle Skeleton](https://github.com/yceruto/bundle-skeleton).

Right after `composer create-project yceruto/bundle-skeleton`, it asks a few questions and
generates a bundle that follows the [bundle best practices](https://symfony.com/doc/current/bundles/best_practices.html),
for Symfony 7.4 LTS and 8.x. It then installs the bundle dependencies with a single
`composer update` and removes itself.

<img src="demo.gif" alt="demo" width="400"/>

## Features

- [x] Derives every name from the package name: `acme/blog-bundle` gives the namespace `Acme\BlogBundle`,
      the class `AcmeBlogBundle`, the extension alias `acme_blog`, the translation domain and Twig namespace `AcmeBlog`.
- [x] Generates the bundle class (`AbstractBundle`) and explicit service definitions, in PHP, PHP array or YAML format.
- [x] Writes a complete `composer.json` and installs the dependencies in one `composer update`.
- [x] Runs without interaction, every answer can be preset with an environment variable.

## Requirements

PHP 8.2+ and Composer 2.6+.

## Usage without interaction

Every answer can be preset with a `BUNDLE_FLEX_*` environment variable, which is especially useful
in CI or scripts:

```console
$ BUNDLE_FLEX_NAME=acme/blog-bundle BUNDLE_FLEX_AUTHOR_NAME="Jane Doe" BUNDLE_FLEX_CONFIG_FORMAT=yaml BUNDLE_FLEX_ALL=1 \
  composer create-project yceruto/bundle-skeleton blog-bundle --no-interaction
```

| Variable | Value |
|---|---|
| `BUNDLE_FLEX_NAME` | Composer package name, e.g. `acme/blog-bundle` |
| `BUNDLE_FLEX_DESCRIPTION` | package description |
| `BUNDLE_FLEX_AUTHOR_NAME`, `BUNDLE_FLEX_AUTHOR_EMAIL` | author (defaults to your Git configuration; `none` omits the email) |
| `BUNDLE_FLEX_LICENSE` | `MIT` (default), `Apache-2.0`, `BSD-2-Clause`, `BSD-3-Clause`, `GPL-3.0-or-later`, `LGPL-3.0-or-later`, `MPL-2.0`, `proprietary` |
| `BUNDLE_FLEX_CONFIG_FORMAT` | `php` (default), `php-array` or `yaml` |
| `BUNDLE_FLEX_ALL` | `1` enables every feature, `0` disables them all |
| `BUNDLE_FLEX_<FEATURE>` | `1` or `0` for a single feature, e.g. `BUNDLE_FLEX_CONFIG=0` |

In interactive mode, presets become the proposed default answers.

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for the architecture, how to add a feature, and the test workflow.

## License

This software is published under the [MIT License](LICENSE)
