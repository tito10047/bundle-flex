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
- [x] Generates every file the best practices ask for: `README.md` with the standard installation instructions,
      `docs/index.md`, `LICENSE` (full text of the chosen license), `CHANGELOG.md`, `.gitattributes`, `.gitignore`
      and `.editorconfig`.
- [x] Generates a working example (a greeter service, its interface and the exceptions of the bundle) and a
      PHPUnit test suite with a test kernel, fully covered and failing on deprecations caused by the bundle.
- [x] Writes a complete `composer.json` and installs the dependencies in one `composer update`.
- [x] Runs without interaction, every answer can be preset with an environment variable.

### Optional features

Each feature is a question of the assistant, and a `BUNDLE_FLEX_<FEATURE>` environment variable:

| Feature | Variable | Default | Generates |
|---|---|---|---|
| Semantic configuration | `BUNDLE_FLEX_CONFIG` | yes | `config/definition.php`, its documentation and tests |
| Controller | `BUNDLE_FLEX_CONTROLLERS` | no | an invokable controller, its route (`config/routes.*`, imported by applications) and its functional test |
| Twig template | `BUNDLE_FLEX_TWIG` | yes with a controller | `templates/hello.html.twig` (`@AcmeBlog` namespace, no layout), rendered by the controller |
| Translations | `BUNDLE_FLEX_TRANSLATIONS` | no | `translations/AcmeBlog.en.xlf`, in the domain of the bundle, used by the template |
| Public assets | `BUNDLE_FLEX_PUBLIC_ASSETS` | no | a stylesheet in `public/`, installed with `assets:install` and linked by the template |
| Symfony UX | `BUNDLE_FLEX_UX` | no | a Stimulus controller (`assets/package.json`, `assets/src/`) exposed to AssetMapper, used by the template |
| PHPStan | `BUNDLE_FLEX_PHPSTAN` | yes | `phpstan.dist.neon` (level max, strict, deprecation, Symfony and PHPUnit rules) |
| PHP-CS-Fixer | `BUNDLE_FLEX_CS_FIXER` | yes | `.php-cs-fixer.dist.php` (Symfony coding standards) |
| Rector | `BUNDLE_FLEX_RECTOR` | yes | `rector.php` |
| GitHub Actions | `BUNDLE_FLEX_CI` | yes | a CI workflow testing every supported PHP and Symfony version, and Dependabot |

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
