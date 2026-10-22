# Contributing to Bundle Flex

Bundle Flex is the Composer plugin behind
[`yceruto/bundle-skeleton`](https://github.com/yceruto/bundle-skeleton). This guide
explains how it works and how to extend it: add a feature, add a file every bundle
gets, or add a slot to a shared template.

The generated bundle must follow the
[bundle best practices](https://symfony.com/doc/current/bundles/best_practices.html)
and support Symfony 7.4 LTS and 8.x. PHP requirements are the same as Symfony's:
PHP 8.2+ for 7.4, PHP 8.4+ for 8.x. Generated code must therefore run on PHP 8.2.

## Quick start

```console
$ composer install
$ composer qa          # coding standards, static analysis (level max) and tests
$ composer cs:fix      # fix coding standards
```

## How it works

`composer create-project yceruto/bundle-skeleton` installs this plugin as a dev
dependency of the skeleton. On `post-create-project-cmd`, `Flex` delegates to
`BundleCreator`, which:

1. removes the skeleton's own files (`BundleCreator::SKELETON_FILES`);
2. asks the questions (`BundleOptionsAsker`) and shows a summary of the derived
   names, followed by a confirmation;
3. generates the bundle (`BundleGenerator`) in two phases:
   - **contribution**: core contributors, then enabled features, describe the
     bundle in a `BundleBlueprint`. Nothing is written in this phase.
   - **rendering**: every file of the blueprint is rendered from its template
     and written. `composer.json` is then written in one go (`ComposerJsonWriter`).
4. runs a single `composer update` (`CommandRunner`). The plugin is no longer
   required, so this also uninstalls it;
5. runs the scripts that features asked for, e.g. `cs:fix`;
6. prints the next steps.

Once the update starts, the plugin files are removed from `vendor/`. Code that
runs after it may only use classes that are already loaded. Keep it to
`BundleCreator` and `CommandRunner`.

```
src/
├── Flex.php                     Composer plugin entry point
├── BundleCreator.php            orchestration (steps 1 to 6)
├── Naming/BundleNaming.php      every name derived from the package name
├── Options/                     answers: BundleOptions (immutable), the asker, env presets, validation
├── Feature/                     FeatureInterface, FeatureRegistry, FeatureSelection, the features
├── Contributor/                 ContributorInterface, CoreContributors, Core/* (files every bundle has)
├── Blueprint/                   the contract: BundleBlueprint and its value objects
├── Template/                    TemplateRenderer, TemplateHelper, SlotRenderer, PhpImports
├── Generator/BundleGenerator.php
└── Composer/                    ComposerJsonWriter, CommandRunner
templates/
├── core/                        templates of the core contributors and the shared ("master") templates
│   ├── src/Bundle.php.tpl       the bundle class (renders the Bundle* slots)
│   └── config/{php,php-array,yaml}/{services,routes}.*.tpl
└── features/<feature-name>/     templates of a feature; its partials go in partials/
tests/
├── Fixtures/<scenario>/         snapshots: the expected generated bundle of each scenario
├── Snapshot/                    scenarios, snapshot test, "the generated bundle works" test
└── Support/                     test doubles, the test-only KitchenSinkFeature and its templates
```

## Names

`BundleNaming` derives every name from the Composer package name. Templates and
contributors must use it and never compute names themselves.

| Property | `acme/blog-bundle` | `my-vendor/foo-bar-bundle` |
|---|---|---|
| `namespace` | `Acme\BlogBundle` | `MyVendor\FooBarBundle` |
| `testsNamespace` | `Acme\BlogBundle\Tests` | `MyVendor\FooBarBundle\Tests` |
| `className` / `fqcn` | `AcmeBlogBundle` | `MyVendorFooBarBundle` |
| `alias` (extension alias, prefix of service ids and parameters) | `acme_blog` | `my_vendor_foo_bar` |
| `shortName` = `translationDomain` = `twigNamespace` | `AcmeBlog` | `MyVendorFooBar` |
| `slug` (file names, command prefixes, CSS classes) | `acme-blog` | `my-vendor-foo-bar` |
| `publicAssetsDirectory` (target of `assets:install`) | `bundles/acmeblog` | `bundles/myvendorfoobar` |

The alias is computed exactly like `AbstractBundle` computes it at runtime:
`Container::underscore()` of the class name, without the `Bundle` suffix. A test
checks this against the real `AbstractBundle`. `acme/blog`, `acme/blogbundle` and
`acme/blog-bundle` all give `Acme\BlogBundle`. A name that repeats the vendor is not
doubled: `acme/acme-blog-bundle` gives the class `AcmeBlogBundle`.

## Templates

Templates are native PHP files with the `.tpl` extension: `templates/<path>.tpl`
renders `<path>`. For example, `templates/features/twig/hello.html.twig.tpl`
renders `templates/hello.html.twig` in the bundle. Why native PHP and `.tpl`:

- **No dependency.** The plugin runs inside Composer, so it can't bring a
  template engine.
- **Real logic.** Templates can use conditions and loops; the old
  `str_replace()` placeholders could not.
- **Kept out of our tooling.** The `.tpl` extension keeps templates away from
  PHP-CS-Fixer, PHPStan and `php -l`, which would otherwise choke on templates
  that output PHP code. It also maps 1:1 to the destination file name, which
  `*.tpl.php` would not (it would give `services.php.tpl.php`).

`TemplateRenderer` includes templates in a static scope (no `$this`) with output
buffering. Any notice or warning aborts the rendering, so an undefined variable
is a hard error.

### Variables available in every template and partial

| Variable | Type | |
|---|---|---|
| `$options` | `Options\BundleOptions` | answers: naming, description, author, license, `configFormat`, `features` |
| `$naming` | `Naming\BundleNaming` | shortcut for `$options->naming` |
| `$blueprint` | `Blueprint\BundleBlueprint` | everything contributed (read it, never modify it while rendering) |
| `$slots` | `Template\SlotRenderer` | `render(Slot, indent)`, `has(Slot)`, `values(ListSlot)` |
| `$helper` | `Template\TemplateHelper` | encoders and formatters, see below |

Parameters passed with `addFile()` and `addPartial()` are added as extra
variables. They cannot override the standard variables above. Start each
template with a `/** @var ... */` block that lists the variables it uses.

### Writing templates

- A template that generates PHP outputs the opening tag itself:
  `<?= "<?php\n" ?>`.
- **PHP removes the newline that directly follows `?>`.** Put control
  structures (`<?php if (...): ?>`, `<?php endif ?>`) alone on their line,
  starting at column 0, so they leave no trace in the output. After an echo
  that ends a line, add an empty line: `class: <?= $x ?>` followed by an empty
  line outputs `class: X` and a newline.
- Encode every value you did not write yourself for its output context:
  `$helper->php($v)` in PHP code, `$helper->yaml($v)` and `$helper->yamlKey($k)`
  in YAML, `$helper->xml($v)` in XML. `$helper->configValue($v, $format)` and
  `$helper->serviceArgument($arg, $format)` also escape `%` and a leading `@`
  for container configuration.
- Build `use` statements with
  `$imports = $helper->imports($classes, $namespace, $functions)`, then use
  `$imports->render()` and `$imports->name($fqcn)`. Imports are sorted like
  PHP-CS-Fixer's `ordered_imports` and short-name conflicts are handled.
- Generated code must be valid on PHP 8.2 and clean for PHP-CS-Fixer
  (`@Symfony`, `@Symfony:risky`, `@PHP82Migration`). Put file-level comments in
  `/* */`, not `/** */` (`phpdoc_to_comment`).

## The contract: `BundleBlueprint`

Contributors receive the blueprint and only describe the bundle; they never
write files. Everything is validated as early as possible, with explicit
exceptions.

```php
// Files
$blueprint->addFile(string $template, string $destination, array $parameters = []);
$blueprint->addEmptyDirectory(string $path);                 // adds <path>/.gitkeep
$blueprint->hasFile(string $destination): bool;
$blueprint->hasFeature(string $name): bool;                  // shortcut for $blueprint->options->hasFeature()

// composer.json (Blueprint\ComposerManifest)
$blueprint->composer->require(string $package, string $constraint);     // e.g. Constraint::SYMFONY
$blueprint->composer->requireDev(string $package, string $constraint);
$blueprint->composer->addKeyword(string $keyword);
$blueprint->composer->addScript(string $name, string|array $commands, ?string $description = null, bool $qa = false);
$blueprint->composer->allowPlugin(string $package);
$blueprint->composer->runScriptAfterInstall(string $script);            // e.g. "cs:fix"

// config/services.{php,yaml} (Blueprint\ServiceConfiguration)
$blueprint->services->setParameter(string $name, scalar|list|null $value);  // "<alias>.<name>"
$blueprint->services->add(new ServiceDefinition($id, $class, $arguments, $tags, public: false));
$blueprint->services->addAlias(new ServiceAlias($id, $target, public: false));

// config/routes.{php,yaml} (Blueprint\RoutingConfiguration)
$blueprint->routes->add(new RouteDefinition($name, $path, $controller, $methods));

// Shared templates (Blueprint\Slots)
$blueprint->slots->addPartial(Slot $slot, string $template, array $parameters = [], int $priority = 0);
$blueprint->slots->addValue(ListSlot $slot, string ...$values);
```

Rules:

- **Dependencies.** Use `Constraint::PHP` and `Constraint::SYMFONY` (`^7.4 || ^8.0`)
  for PHP and every Symfony package. Requiring a package twice with the same
  constraint does nothing; different constraints throw. `require()` moves a dev
  dependency to `require`, and `requireDev()` of a runtime dependency does nothing.
  `php`, `symfony/config`, `symfony/dependency-injection` and `symfony/http-kernel`
  are always required. `symfony/yaml` is added for the YAML format and
  `symfony/routing` as soon as a route exists.
- **Scripts.** Scripts with `qa: true` are aggregated, in registration order,
  into a `qa` script (the name `qa` is reserved). `runScriptAfterInstall()` runs a
  defined script after the update; a failure is only reported as a warning.
- **Services.** Services are defined explicitly: no autowiring, no
  autoconfiguration, private unless `public: true`. Ids must be
  `<alias>.<name>`, or `.<alias>.<name>` for internal services. Aliases are
  either a namespaced class or interface name, or a prefixed id, and must
  target a service of the bundle. Arguments are positional:
  `ServiceArgument::service($id)`, `ServiceArgument::parameter($name)` or
  `ServiceArgument::value($scalarOrList)`. Literal values are escaped for you.
- **Routes.** Route names must be `<alias>_<name>`. The controller is a service
  id, either an invokable controller or `id::method`.
- **Derived files.** `composer.json`, `config/services.<ext>` (always) and
  `config/routes.<ext>` (when there are routes) are generated from the
  blueprint, in the chosen `ConfigFormat`. `addFile()` refuses these
  destinations. Never write format-specific service or route templates
  yourself.

## Configuration formats

`ConfigFormat` (question "Configuration format", `BUNDLE_FLEX_CONFIG_FORMAT`)
controls how services and routes are written. The semantic configuration
(`config/definition.php`) is always PHP.

| Format | Files | Notes |
|---|---|---|
| `php` (default) | `services.php`, `routes.php` | `static function (ContainerConfigurator\|RoutingConfigurator ...)`, `service()`/`param()` helpers, no `$this` |
| `php-array` | `services.php`, `routes.php` | returns a YAML-shaped array (Symfony 7.4+, [symfony/symfony#61894](https://github.com/symfony/symfony/pull/61894)) |
| `yaml` | `services.yaml`, `routes.yaml` | requires `symfony/yaml` |

Use `$options->configFormat->getFileName('services')` to get the file name
(for example in an import) instead of hard-coding the extension.
`GeneratedBundleTest` loads the snapshots of each format into a real container
and router and checks they are identical to the blueprint.

## Slots

The bundle class and other shared files are "master" templates. Features don't
edit them: they contribute to slots.

- **`Slot`** cases receive partial templates. `$slots->render(Slot::X, $indent)`
  outputs them by priority (highest first, then registration order), separated
  by a blank line and indented. Write partials without indentation.
- **`ListSlot`** cases receive unique strings, kept in registration order. The
  master template decides how to sort and render them.

| Slot | Content | Rendered by |
|---|---|---|
| `Slot::BundleConfigure` | statements of `configure(DefinitionConfigurator $definition)`; the method only exists when the slot is not empty | `core/src/Bundle.php.tpl` |
| `Slot::BundleLoadExtension` | statements of `loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder)`, after the services import | `core/src/Bundle.php.tpl` |
| `Slot::BundlePrependExtension` | statements of `prependExtension(ContainerConfigurator $container, ContainerBuilder $builder)`; the method only exists when the slot is not empty | `core/src/Bundle.php.tpl` |
| `Slot::BundleBuild` | statements of `build(ContainerBuilder $container)`, after `parent::build()`; the method only exists when the slot is not empty | `core/src/Bundle.php.tpl` |
| `ListSlot::BundleUses` | FQCNs used by the code of the `Bundle*` partials | `core/src/Bundle.php.tpl` |
| `Slot::ReadmeSections` | Markdown sections of `README.md`, after the installation instructions | README master template |
| `Slot::DocsSections` | Markdown sections of `docs/index.md` | docs master template |
| `Slot::TestKernelConfigureContainer` | statements of the test kernel's `configureContainer(ContainerConfigurator $container)` | test kernel master template |
| `Slot::TestKernelConfigureRoutes` | statements of the test kernel's `configureRoutes(RoutingConfigurator $routes)` | test kernel master template |
| `ListSlot::TestKernelBundles` | FQCNs of the bundles registered by the test kernel, besides FrameworkBundle and the bundle itself | test kernel master template |
| `ListSlot::TestKernelUses` | FQCNs used by the code of the `TestKernel*` partials | test kernel master template |
| `ListSlot::GitAttributesExportIgnore` | paths excluded from the archive, e.g. `/phpstan.dist.neon` | `.gitattributes` master template |
| `ListSlot::GitIgnore` | `.gitignore` patterns, e.g. `/.php-cs-fixer.cache` | `.gitignore` master template |

To add a slot, add a documented case to `Slot` or `ListSlot`, render it in the
master template that owns it, and cover it in `KitchenSinkFeature`. A slot that
nothing renders is a bug.

## Features

A feature is an optional part of the bundle. It is enabled by answering "yes"
to its question, and only contributes when enabled.

```php
interface FeatureInterface extends ContributorInterface
{
    public function getName(): string;            // kebab-case, unique; env var BUNDLE_FLEX_<NAME>
    public function getGroup(): FeatureGroup;     // Features or QualityAssurance
    public function getQuestion(): string;        // "Add a console command?"
    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool;
    public function contribute(BundleBlueprint $blueprint): void;
}
```

- Features are registered in `FeatureRegistry::createDefault()`, group by
  group (`Features` first, then `QualityAssurance`). The registration order is
  both the order of the questions and the order of the contributions.
- `isEnabledByDefault()` may only read the answers of features registered
  before it. Reading a later or unknown feature throws. Example: Twig defaults
  to "yes" when controllers are enabled:
  `return $previousAnswers->isEnabled('controllers');`
- In `contribute()`, a feature can adapt to the other features with
  `$blueprint->hasFeature('twig')`, since all answers are known by then.
- Names are reserved when their environment variable is already used by
  another option (`name`, `description`, `author-name`, `author-email`,
  `license`, `config-format`, `all`).

Planned features. Use these names: they define the environment variables
documented for users.

| Name | Group | Default | Variable |
|---|---|---|---|
| `config` (implemented, `SemanticConfigFeature`) | Features | yes | `BUNDLE_FLEX_CONFIG` |
| `controllers` | Features | no | `BUNDLE_FLEX_CONTROLLERS` |
| `twig` | Features | yes if `controllers`, else no | `BUNDLE_FLEX_TWIG` |
| `translations` | Features | no | `BUNDLE_FLEX_TRANSLATIONS` |
| `command` | Features | no | `BUNDLE_FLEX_COMMAND` |
| `event-listener` | Features | no | `BUNDLE_FLEX_EVENT_LISTENER` |
| `doctrine` | Features | no | `BUNDLE_FLEX_DOCTRINE` |
| `public-assets` | Features | no | `BUNDLE_FLEX_PUBLIC_ASSETS` |
| `ux` | Features | no | `BUNDLE_FLEX_UX` |
| `phpstan` | QualityAssurance | yes | `BUNDLE_FLEX_PHPSTAN` |
| `cs-fixer` | QualityAssurance | yes | `BUNDLE_FLEX_CS_FIXER` |
| `rector` | QualityAssurance | yes | `BUNDLE_FLEX_RECTOR` |
| `ci` | QualityAssurance | yes | `BUNDLE_FLEX_CI` |

### Worked example: adding a console command feature

1. **The feature class**, `src/Feature/CommandFeature.php`:

   ```php
   final class CommandFeature implements FeatureInterface
   {
       public const NAME = 'command';

       public function getName(): string
       {
           return self::NAME;
       }

       public function getGroup(): FeatureGroup
       {
           return FeatureGroup::Features;
       }

       public function getQuestion(): string
       {
           return 'Add a console command?';
       }

       public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
       {
           return false;
       }

       public function contribute(BundleBlueprint $blueprint): void
       {
           $naming = $blueprint->naming;

           $blueprint->composer->require('symfony/console', Constraint::SYMFONY);
           $blueprint->addFile('features/command/HelloCommand.php.tpl', 'src/Command/HelloCommand.php');
           $blueprint->services->add(new ServiceDefinition(
               $naming->alias.'.command.hello',
               $naming->namespace.'\Command\HelloCommand',
               [ServiceArgument::service($naming->alias.'.greeter')],
               [new ServiceTag('console.command')],
           ));
           $blueprint->slots->addPartial(Slot::ReadmeSections, 'features/command/partials/readme.md.tpl');
       }
   }
   ```

2. **Its templates**, in `templates/features/command/`:
   `HelloCommand.php.tpl` and `partials/readme.md.tpl`. The service definition
   is rendered for you, in the chosen configuration format.

3. **Register it** in `FeatureRegistry::createDefault()`, at the right place in
   its group.

4. **Test it.**
   - A unit test in `tests/Feature/CommandFeatureTest.php` checks the question,
     the default, and what it contributes to a blueprint (use
     `Tests\Support\OptionsFactory`).
   - Add or extend a scenario in `tests/Snapshot/Scenarios.php`, run
     `UPDATE_SNAPSHOTS=1 composer test`, and review the new fixtures.
     `GeneratedBundleTest` then lints the PHP files, validates `composer.json`,
     and checks that the services and routes load in a real container in every
     format.

### Adding a file every bundle has

Core contributors implement `ContributorInterface`. They are registered in
`CoreContributors::createDefault()` and run before the features. Example: a
`LicenseContributor` would call
`$blueprint->addFile('core/LICENSE.tpl', 'LICENSE')`, with its template in
`templates/core/`. If the file is a master template, render its slots there
and document them in the table above.

## Assistant options and environment variables

Every answer can be preset with an environment variable. Presets become the
default answers: without interaction (`--no-interaction`) they are used as is,
otherwise they are proposed to the user. All values are validated: an invalid
preset aborts with a message naming the variable.

| Variable | Option | Default |
|---|---|---|
| `BUNDLE_FLEX_NAME` | Composer package name `vendor/name` (`-bundle` suffix recommended) | `<git github.user or "acme">/<directory name>-bundle` |
| `BUNDLE_FLEX_DESCRIPTION` | description | `<BundleClass> for Symfony` |
| `BUNDLE_FLEX_AUTHOR_NAME` | author name (required) | `git config user.name` |
| `BUNDLE_FLEX_AUTHOR_EMAIL` | author email, `none` to omit it | `git config user.email` |
| `BUNDLE_FLEX_LICENSE` | `MIT`, `Apache-2.0`, `BSD-2-Clause`, `BSD-3-Clause`, `GPL-3.0-or-later`, `LGPL-3.0-or-later`, `MPL-2.0`, `proprietary` | `MIT` |
| `BUNDLE_FLEX_CONFIG_FORMAT` | `php`, `php-array`, `yaml` | `php` |
| `BUNDLE_FLEX_ALL` | `1` enables every feature, `0` disables every feature | each feature's own default |
| `BUNDLE_FLEX_<FEATURE>` | enables (`1`) or disables (`0`) a feature, takes precedence over `BUNDLE_FLEX_ALL` | see the features table |

Booleans accept `1`, `true`, `yes`, `y`, `on` and `0`, `false`, `no`, `n`, `off`.

## Tests

| What | How |
|---|---|
| Unit tests | one test class per class, in the mirrored namespace |
| Snapshots | `tests/Fixtures/<scenario>/` holds the full expected output of each scenario in `tests/Snapshot/Scenarios.php` |
| Working output | `GeneratedBundleTest` checks the snapshots: `php -l`, YAML syntax, `composer.json` validation, and container and router loading compared to the blueprint |
| Every contribution point | `tests/Support/KitchenSinkFeature.php` (test only) uses the whole blueprint API |

Snapshot workflow:

```console
$ UPDATE_SNAPSHOTS=1 composer test   # regenerate tests/Fixtures (orphan fixtures are deleted)
$ git diff tests/Fixtures            # review every change before committing
```

The generated code must work on PHP 8.2 with the lowest supported Symfony
version. To check the plugin on PHP 8.2 (with Symfony 7.4):

```console
$ git clone . /tmp/flex82   # not "git archive": it omits the export-ignored tests
$ docker run --rm -v /tmp/flex82:/app -w /app composer:2 sh -c 'composer config platform.php 8.2.0 && composer update'
$ docker run --rm -v /tmp/flex82:/app -w /app php:8.2-cli vendor/bin/phpunit
```

### End-to-end trial

The plugin runs inside `composer create-project`, so test it end to end:

1. Copy the skeleton's `composer.json` to a temporary directory (for example
   `/tmp/e2e/skeleton`).
2. In that copy, require `"yceruto/bundle-flex": "*@dev"` and add this
   repository, so Composer installs your working copy instead of the release
   from Packagist:
   `{"type": "path", "url": "/path/to/bundle-flex", "options": {"symlink": false, "versions": {"yceruto/bundle-flex": "9.9.9"}}}`
3. Run:

```console
$ BUNDLE_FLEX_NAME=acme/blog-bundle BUNDLE_FLEX_ALL=1 BUNDLE_FLEX_CONFIG_FORMAT=yaml \
  composer create-project yceruto/bundle-skeleton /tmp/e2e/blog-bundle --no-interaction --stability=dev \
  --repository='{"type": "path", "url": "/tmp/e2e/skeleton", "options": {"symlink": false, "versions": {"yceruto/bundle-skeleton": "1.0.x-dev"}}}'
$ cd /tmp/e2e/blog-bundle && composer validate --strict
```

`--repository` is only used to locate the skeleton. The plugin repository must
be declared in the skeleton's `composer.json` (step 2). Use a stable version
higher than the latest release, otherwise `prefer-stable` picks the release
from Packagist.
