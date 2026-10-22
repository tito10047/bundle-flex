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
- Generated code must be valid on PHP 8.2 and clean for the generated
  PHP-CS-Fixer configuration (`@Symfony`, `@Symfony:risky`, `@PHP8x2Migration`,
  `@PHP8x2Migration:risky`, the PHPUnit migration set). `GeneratedBundleTest`
  checks every snapshot with it. Put file-level comments in `/* */`, not `/** */`
  (`phpdoc_to_comment`).
- Generated code must also pass PHPStan (level max, strict and deprecation
  rules) and Rector without changes, on Symfony 7.4 and 8.x. The end-to-end
  tests check it (see below). Generated tests call the PHPUnit assertions
  statically (`self::assertSame()`), as the strict rules require; `rector.php`
  skips the Rector rule that would turn them into `$this->assertSame()`.

## The contract: `BundleBlueprint`

Contributors receive the blueprint and only describe the bundle; they never
write files. Everything is validated as early as possible, with explicit
exceptions.

```php
// Files
$blueprint->addFile(string $template, string $destination, array $parameters = []);
$blueprint->addEmptyDirectory(string $path);                 // adds <path>/.gitkeep
$blueprint->hasFile(string $destination): bool;
$blueprint->hasDirectory(string $path): bool;                // a contributed file is inside, e.g. "tests"
$blueprint->hasFeature(string $name): bool;                  // shortcut for $blueprint->options->hasFeature()

// composer.json (Blueprint\ComposerManifest)
$blueprint->composer->require(string $package, string $constraint);     // e.g. Constraint::SYMFONY
$blueprint->composer->requireDev(string $package, string $constraint);
$blueprint->composer->addKeyword(string $keyword);
$blueprint->composer->addScript(string $name, string|array $commands, ?string $description = null, bool $qa = false);
$blueprint->composer->allowPlugin(string $package);
$blueprint->composer->runScriptAfterInstall(string $script);            // e.g. "cs:fix"
$blueprint->composer->getConstraint(string $package): ?string;          // null when not required
$blueprint->composer->hasScript(string $name): bool;

// config/services.{php,yaml} (Blueprint\ServiceConfiguration)
$blueprint->services->setParameter(string $name, scalar|list|null $value);  // "<alias>.<name>"
$blueprint->services->add(new ServiceDefinition($id, $class, $arguments, $tags, public: false));
$blueprint->services->addAlias(new ServiceAlias($id, $target, public: false));

// config/routes.{php,yaml} (Blueprint\RoutingConfiguration)
$blueprint->routes->add(new RouteDefinition($name, $path, $controller, $methods, $defaults, $requirements));

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
  `ComposerManifest::TEST_SCRIPT` (`test`) runs the test suite: the generated CI
  runs it on every PHP and Symfony version, and every other `qa` script in its
  "Quality assurance" job.
- **Services.** Services are defined explicitly: no autowiring, no
  autoconfiguration, private unless `public: true`. Ids must be
  `<alias>.<name>`, or `.<alias>.<name>` for internal services. Aliases are
  either a namespaced class or interface name, or a prefixed id, and must
  target a service of the bundle. Arguments are positional:
  `ServiceArgument::service($id)`, `ServiceArgument::parameter($name)` or
  `ServiceArgument::value($scalarOrList)`. Literal values are escaped for you.
- **Routes.** Route names must be `<alias>_<name>`. The controller is a service
  id, either an invokable controller or `id::method`. Defaults and requirements
  are only accepted for the placeholders of the path, and requirements must be
  valid regular expressions.
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
| `php-array` | `services.php`, `routes.php` | returns a YAML-shaped array (Symfony 7.4+, [symfony/symfony#61894](https://github.com/symfony/symfony/pull/61894)), with `'@id'` and `'%name%'` references like YAML: the `service()`/`param()` functions cannot be autoloaded by static analysis |
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
  `$slots->renderEach(Slot::X)` returns them one by one, for master templates
  that lay them out themselves (e.g. numbered steps).
- **`ListSlot`** cases receive unique strings, kept in registration order. The
  master template decides how to sort and render them.

| Slot | Content | Rendered by |
|---|---|---|
| `Slot::BundleConfigure` | statements of `configure(DefinitionConfigurator $definition)`; the method only exists when the slot is not empty | `core/src/Bundle.php.tpl` |
| `Slot::BundleLoadExtension` | statements of `loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder)`, after the services import | `core/src/Bundle.php.tpl` |
| `Slot::BundlePrependExtension` | statements of `prependExtension(ContainerConfigurator $container, ContainerBuilder $builder)`; the method only exists when the slot is not empty | `core/src/Bundle.php.tpl` |
| `Slot::BundleBuild` | statements of `build(ContainerBuilder $container)`, after `parent::build()`; the method only exists when the slot is not empty | `core/src/Bundle.php.tpl` |
| `ListSlot::BundleUses` | FQCNs used by the code of the `Bundle*` partials | `core/src/Bundle.php.tpl` |
| `ListSlot::ReadmeBadges` | Markdown badges shown under the title of `README.md`, one per value | `core/README.md.tpl` |
| `Slot::ReadmeInstallationSteps` | additional installation steps ("Step 3", "Step 4"...) of `README.md`, e.g. importing the routes; one partial per step, see below | `core/README.md.tpl` |
| `Slot::ReadmeSections` | Markdown sections (`## ...`) of `README.md`, between the installation instructions and the "Documentation" section | `core/README.md.tpl` |
| `Slot::DocsSections` | Markdown sections (`## ...`) of `docs/index.md`, after the installation section | `core/docs/index.md.tpl` |
| `Slot::TestKernelConfigureContainer` | statements of the test kernel's `configureContainer(ContainerConfigurator $container)`, after the FrameworkBundle configuration | `core/tests/App/TestKernel.php.tpl` |
| `Slot::TestKernelConfigureRoutes` | statements of the test kernel's `configureRoutes(RoutingConfigurator $routes)`; the method only exists when the slot is not empty | `core/tests/App/TestKernel.php.tpl` |
| `ListSlot::TestKernelBundles` | FQCNs of the bundles registered by the test kernel, besides FrameworkBundle and the bundle itself | `core/tests/App/TestKernel.php.tpl` |
| `ListSlot::TestKernelUses` | FQCNs used by the code of the `TestKernel*` partials | `core/tests/App/TestKernel.php.tpl` |
| `ListSlot::GitAttributesExportIgnore` | paths excluded from the archive, e.g. `/phpstan.dist.neon` (sorted) | `core/gitattributes.tpl` |
| `ListSlot::GitIgnore` | `.gitignore` patterns, e.g. `/.php-cs-fixer.cache` (sorted) | `core/gitignore.tpl` |
| `Slot::PhpStanParameters` | entries of the `parameters` section of `phpstan.dist.neon`, e.g. the configuration of a PHPStan extension | `features/phpstan/phpstan.dist.neon.tpl` |
| `ListSlot::CiPhpExtensions` | PHP extensions installed in every job of the CI workflow, e.g. `pdo_sqlite` | `features/ci/.github/workflows/ci.yml.tpl` |
| `Slot::CiTestServices` | service containers of the `tests` and `coverage` jobs (entries of `services:`), e.g. a database | `features/ci/.github/workflows/ci.yml.tpl` |
| `Slot::CiTestSteps` | steps (YAML list items) run before the tests in the `tests` and `coverage` jobs | `features/ci/.github/workflows/ci.yml.tpl` |

Markdown slots:

- A partial of `Slot::ReadmeInstallationSteps` is one step: its first line is
  the title (plain text, without `#` nor `Step N:`), then a blank line and the
  instructions. The README numbers the steps after "Step 2: Enable the Bundle",
  as the [best practices](https://symfony.com/doc/current/bundles/best_practices.html#installation-instructions)
  recommend, and tells Flex users to follow them too.
- `README.md` stays short: installation, a short configuration example,
  development commands and license. The usage of each feature goes to
  `docs/index.md` (`Slot::DocsSections`).
- Priorities of `Slot::ReadmeSections` and `Slot::DocsSections`: the
  configuration uses `SemanticConfigFeature::DOCS_PRIORITY` (10) to come first;
  the core "Usage" section (the greeter) has the default priority 0 and is
  registered first, so features using priority 0 come after it.
- The "Development" section of `README.md` lists every Composer script with its
  description, and `composer qa` when scripts are flagged with `qa: true`: give
  your scripts a description.

Test kernel (`tests/App/TestKernel.php`, `<Bundle>\Tests\App\TestKernel`, the
`KERNEL_CLASS` of `phpunit.dist.xml`):

- it registers FrameworkBundle (with `framework.test: true`), the bundles of
  `ListSlot::TestKernelBundles`, then the bundle;
- its project directory is `tests/App/` and its cache and logs go to the system
  temporary directory, so booting it never writes into the bundle, nor loads
  the bundle files (`config/`, `templates/`, `translations/`) as application files;
- its third constructor argument holds the configuration of extensions, applied
  last, e.g. `new TestKernel(extensionConfigs: ['acme_blog' => ['greeting' => 'Hi']])`.
  Each configuration gets its own cache directory. `tests/Integration/ConfigurationTest.php`
  shows how to pass it from `KernelTestCase::bootKernel()` by overriding `createKernel()`.

For example, a Twig feature registers TwigBundle and configures it:

```php
$blueprint->composer->requireDev('symfony/twig-bundle', Constraint::SYMFONY);
$blueprint->slots->addValue(ListSlot::TestKernelBundles, 'Symfony\Bundle\TwigBundle\TwigBundle');
$blueprint->slots->addPartial(Slot::TestKernelConfigureContainer, 'features/twig/partials/test_kernel.php.tpl');
// features/twig/partials/test_kernel.php.tpl contains:
// $container->extension('twig', ['strict_variables' => true]);
```

and a controllers feature imports its routes:

```php
$blueprint->slots->addPartial(Slot::TestKernelConfigureRoutes, 'features/controllers/partials/test_kernel_routes.php.tpl');
$blueprint->slots->addPartial(Slot::ReadmeInstallationSteps, 'features/controllers/partials/readme_step.md.tpl');
// test_kernel_routes.php.tpl: $routes->import('@<?= $naming->className ?>/config/<?= $options->configFormat->getFileName('routes') ?>');
// readme_step.md.tpl:          Import the Routes\n\nImport the routes of the bundle in ...
```

To add a slot, add a documented case to `Slot` or `ListSlot`, render it in the
master template that owns it, and cover it in `KitchenSinkFeature`. A slot that
nothing renders is a bug: `SlotsTest` checks that a core template renders every
slot, except the slots owned by a feature (the PHPStan and CI slots). Those are
documented as rendered "when the "<feature>" feature is enabled", and the test
checks that the templates of that feature (`templates/features/<feature>/`)
render them.

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

The built-in features, in registration order. Their names define the
environment variables documented for users: never rename them.

| Name | Class | Group | Default | Variable |
|---|---|---|---|---|
| `config` | `SemanticConfigFeature` | Features | yes | `BUNDLE_FLEX_CONFIG` |
| `controllers` | `ControllersFeature` | Features | no | `BUNDLE_FLEX_CONTROLLERS` |
| `twig` | `TwigFeature` | Features | yes if `controllers`, else no | `BUNDLE_FLEX_TWIG` |
| `translations` | `TranslationsFeature` | Features | no | `BUNDLE_FLEX_TRANSLATIONS` |
| `command` | `CommandFeature` | Features | no | `BUNDLE_FLEX_COMMAND` |
| `event-listener` | `EventListenerFeature` | Features | no | `BUNDLE_FLEX_EVENT_LISTENER` |
| `doctrine` | `DoctrineFeature` | Features | no | `BUNDLE_FLEX_DOCTRINE` |
| `public-assets` | `PublicAssetsFeature` | Features | no | `BUNDLE_FLEX_PUBLIC_ASSETS` |
| `ux` | `UxFeature` | Features | no | `BUNDLE_FLEX_UX` |
| `phpstan` | `PhpStanFeature` | QualityAssurance | yes | `BUNDLE_FLEX_PHPSTAN` |
| `cs-fixer` | `PhpCsFixerFeature` | QualityAssurance | yes | `BUNDLE_FLEX_CS_FIXER` |
| `rector` | `RectorFeature` | QualityAssurance | yes | `BUNDLE_FLEX_RECTOR` |
| `ci` | `GitHubActionsFeature` | QualityAssurance | yes | `BUNDLE_FLEX_CI` |

### The web features

`controllers`, `twig`, `translations`, `public-assets` and `ux` build one demo
together, and each of them also works alone:

| Feature | Generates | Uses |
|---|---|---|
| `controllers` | `src/Controller/HelloController.php` (final, invokable, no `AbstractController`), the `<alias>.controller.hello` service (private, tagged `controller.service_arguments`, which makes it public), the `<alias>_hello` route (`/hello/{name}`, default `World`, requirement `\w+`), `tests/Functional/HelloControllerTest.php`; the test kernel imports the routes like an application, `@<Bundle>/config/routes.<ext>`, and logs nothing (the default logger prints the 404 errors) | the greeter; Twig when `twig` is enabled, plain text otherwise |
| `twig` | `templates/hello.html.twig` (no layout, a `title` block), `tests/Integration/TemplateTest.php`, TwigBundle in the test kernel (`strict_variables`) | the messages of `translations`, the stylesheet of `public-assets` (`asset()`), the Stimulus controller of `ux` (`stimulus_*()`) |
| `translations` | `translations/<ShortName>.en.xlf` (XLIFF 1.2, the ids are the English messages, like Symfony's own translations, so they display without translator), `tests/Integration/TranslationsTest.php` (catalogue and XLIFF schema) | |
| `public-assets` | `public/css/<slug>.css`, `tests/Integration/PublicAssetsTest.php` (runs `assets:install`), a README step | |
| `ux` | `assets/package.json` (`@<vendor>/<package>`, the name StimulusBundle maps back to the Composer package), `assets/src/hello_controller.js` (plain ES module, no build step), the AssetMapper path prepended in `prependExtension()` when AssetMapper is available, `tests/Integration/AssetMapperTest.php`, the `symfony-ux` keyword, a README step | |

Their dependencies follow one rule:

- What the PHP code of the bundle uses is required (`require`): `symfony/http-foundation`
  for the controller, and `twig/twig` when it renders the template.
- What the templates need (`symfony/twig-bundle`, `symfony/asset` for `asset()`,
  `symfony/stimulus-bundle` for `stimulus_*()`) is required when the bundle renders
  them itself (`controllers` and `twig` are enabled), and only required for the tests
  otherwise: the application including them has them. Use
  `TwigFeature::requireForTemplates()` to follow this rule.
- What only the tests use is a dev dependency: `symfony/browser-kit`, `symfony/translation`,
  `symfony/console` (`assets:install`), `symfony/asset-mapper` (the bundle only configures
  AssetMapper when the application uses it).

### The backend features

`command`, `event-listener` and `doctrine` each build on the greeter, and work
alone or together with every other feature:

| Feature | Generates | Registers | Tests |
|---|---|---|---|
| `command` | `src/Command/HelloCommand.php`: invokable command (`#[AsCommand]`, `__invoke()` with `#[Argument]` and `#[Option]`, `SymfonyStyle`), named `<slug>:hello` | `<alias>.command.hello`, tagged `console.command` with the command name (lazy) | `CommandTester` on the command, and through the `Application` of the test kernel |
| `event-listener` | `src/EventListener/GreetingHeaderListener.php`: adds an `X-<Vendor>-<Name>-Greeting` header to the main response, unless the application set it | `<alias>.event_listener.greeting_header`, tagged `kernel.event_listener` (`kernel.response`, `onKernelResponse`, priority 0) | unit test, and the listener and its priority on the event dispatcher of the test kernel |
| `doctrine` | `src/Entity/Greeting.php` (plain class) and `config/doctrine/Greeting.orm.xml`, table `<alias>_greeting` | the mapping, in `prependExtension()`, only when DoctrineBundle is enabled | entity unit test; prepended configuration with and without DoctrineBundle; mapping, schema and persistence on in-memory SQLite |

- **Command names** use the slug as namespace (`acme-blog:hello`), like `doctrine:`
  or `make:`: `bin/console list acme-blog` lists the commands of the bundle, and
  they cannot collide with the commands of other bundles. The name is declared
  on the `console.command` tag, like everything else in the explicit service
  definitions, and in `#[AsCommand]`, which also holds the description and help
  and is read when the command is used without the container (unit tests).
  A command with a name is lazy: listing the commands does not create it.
- **Doctrine.** The entity is concrete and mapped in XML, as the best practices
  recommend: applications can override the mapping, which attributes don't
  allow. A mapped superclass would force every application to write its own
  entity before using the bundle. The mapping is registered under the bundle
  name (`doctrine.orm.mappings.<BundleClass>`), with `is_bundle: false` and an
  absolute `dir`, so it works whatever the `auto_mapping` setting (auto-mapping
  skips a bundle that already has a mapping). Dependencies: `doctrine/orm`
  `^3.4` (native lazy objects on PHP 8.4), `doctrine/doctrine-bundle`
  `^2.18 || ^3.0` as a dev dependency (2.x is the only one for PHP 8.2/8.3,
  3.x the only one for Symfony 8). The test kernel opts in to the behaviors of
  DoctrineBundle 3 when DoctrineBundle 2 is installed (`enable_lazy_ghost_objects`,
  `enable_native_lazy_objects` on PHP 8.4+, `controller_resolver.auto_mapping:
  false`) and validates the XML mapping (`validate_xml_mapping`), so neither
  version triggers deprecations. With PHPStan, `phpstan/phpstan-doctrine` reads
  the mapping from the entity manager of the test kernel (`tests/object-manager.php`).
- **Shared dependencies.** Features requiring the same package use the same
  constraint, and `require` wins over `require-dev`: `symfony/http-foundation`
  is required by `controllers` and `event-listener`, and `symfony/console`, a dev
  dependency of `public-assets` (`assets:install`), is required by `command`.

### Worked example: adding a console command feature

This is how `CommandFeature` is built (simplified):

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
           $blueprint->addFile('features/command/src/Command/HelloCommand.php.tpl', 'src/Command/HelloCommand.php');
           $blueprint->addFile('features/command/tests/Command/HelloCommandTest.php.tpl', 'tests/Command/HelloCommandTest.php');
           $blueprint->services->add(new ServiceDefinition(
               $naming->alias.'.command.hello',
               $naming->namespace.'\Command\HelloCommand',
               [ServiceArgument::service(GreeterContributor::serviceId($naming))],
               // the command name on the tag makes the command lazy
               [new ServiceTag('console.command', ['command' => $naming->slug.':hello'])],
           ));
           $blueprint->slots->addPartial(Slot::DocsSections, 'features/command/partials/docs.md.tpl');
       }
   }
   ```

2. **Its templates**, in `templates/features/command/`, mirror the destination
   paths (`src/Command/HelloCommand.php.tpl`, `tests/...`); partials go in
   `partials/` (`partials/docs.md.tpl`). The service definition is rendered for
   you, in the chosen configuration format.

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

### Quality assurance features

The quality assurance features are registered last. They read the blueprint
to adapt to everything contributed before them, so features rarely have to
know about them.

| Feature | Generates | Scripts | Adapts to |
|---|---|---|---|
| `phpstan` | `phpstan.dist.neon`: level `max`, `config/`, `src/`, `tests/` (when it has files); Symfony, PHPUnit, strict and deprecation rules registered by `phpstan/extension-installer` | `phpstan` (qa) | PHPUnit (`phpstan-phpunit`) |
| `cs-fixer` | `.php-cs-fixer.dist.php`: `@Symfony`, `@Symfony:risky`, `@PHP8x2Migration(:risky)`, the PHPUnit migration set of the lowest supported PHPUnit, `declare_strict_types`; every PHP file but `vendor/` | `cs` (qa), `cs:fix` (run after install) | PHPUnit version |
| `rector` | `rector.php`: PHP sets from `require.php`, Composer-based sets, `deadCode`, `codeQuality`, `typeDeclarations`, `privatization`, `instanceOf`, `earlyReturn` (+ `phpunitCodeQuality`) | `rector` (qa), `rector:fix` | Twig, Doctrine, PHPUnit packages |
| `ci` | `.github/workflows/ci.yml`, `.github/dependabot.yml`, a README badge | | `test` and `qa` scripts, PHPUnit, `symfony/phpunit-bridge` |

Rector (2.6.4+) applies the Composer-based sets (Symfony, PHPUnit...) for the
lowest version allowed by `composer.json`, because the bundle is a library: the
suggested changes never break a supported version.

The generated workflow follows the CI section of the best practices:

| Job | PHP | Symfony | |
|---|---|---|---|
| `tests` | 8.2 | 7.4 | `--prefer-lowest` |
| `tests` | 8.3 | 7.4 | |
| `tests` | 8.4 | 8.0 | allows the versions affected by security advisories, see below |
| `tests` | 8.5 | 7.4 | |
| `tests` | 8.5 | 8.1 | |
| `tests` | 8.5 | 8.2 (dev) | allowed to fail |
| `qa` | 8.5 | latest | `composer validate --strict`, then every `qa` script but `test` |
| `coverage` | 8.5 | latest | PCOV, Clover report as an artifact, fails under 95% of lines (`MIN_COVERAGE`) |

Symfony versions are selected with `SYMFONY_REQUIRE` and a global Symfony
Flex, and only the Composer cache is cached (`ramsey/composer-install`), never
`vendor/`. With PHPUnit's native deprecation handling, configure
`<source ignoreIndirectDeprecations="true">` in `phpunit.dist.xml`: only
deprecations triggered by the bundle fail the tests (`max[direct]=0`). When
`symfony/phpunit-bridge` is required, `SYMFONY_DEPRECATIONS_HELPER` is set
(`max[direct]=0`, `disabled=1` for the lowest dependencies).

Symfony 8.0 is no longer maintained, and every version of some of its
components is affected by a security advisory fixed in a newer version only.
Composer (2.9+) refuses to install such versions, so the 8.0 job, and only
this one, runs `composer config --global audit.block-insecure false` (understood
by Composer 2.9 and 2.10). The quality checks only run with the latest
versions: PHPStan fails on the lowest dependencies of Symfony 7.4.

To hook into them from a feature:

- **PHPStan extension**: require it when the `phpstan` feature is enabled; the
  extension installer registers it. Configure it with `Slot::PhpStanParameters`.

  ```php
  if ($blueprint->hasFeature(PhpStanFeature::NAME)) {
      $blueprint->composer->requireDev('phpstan/phpstan-doctrine', '^2.0');
      // the entity manager of the test kernel, from which the extension reads the XML mapping
      $blueprint->addFile('features/doctrine/tests/object-manager.php.tpl', 'tests/object-manager.php');
      $blueprint->slots->addPartial(Slot::PhpStanParameters, 'features/doctrine/partials/phpstan.neon.tpl');
  }
  ```

- **CI**: a quality check is a Composer script with `qa: true`, the workflow
  runs it. The tests get their PHP extensions from `ListSlot::CiPhpExtensions`,
  their service containers from `Slot::CiTestServices` and their setup steps
  from `Slot::CiTestSteps`. These slots are only rendered when the `ci` feature
  is enabled, so they need no check.

### Adding a file every bundle has

Core contributors implement `ContributorInterface`. They are registered in
`CoreContributors::createDefault()` and run before the features, in this order:

| Contributor | Files |
|---|---|
| `PackageContributor` | dependencies and keywords of `composer.json` |
| `BundleClassContributor` | `src/<Bundle>.php` |
| `GreeterContributor` | the domain example: `src/GreeterInterface.php`, `src/Greeter.php`, `src/Exception/`, `tests/GreeterTest.php`, the `<alias>.greeter` service and its interface alias |
| `TestsContributor` | `phpunit.dist.xml`, `tests/App/TestKernel.php`, `tests/Integration/<Bundle>Test.php`, PHPUnit and FrameworkBundle as dev dependencies, the `test` script |
| `DocumentationContributor` | `README.md`, `docs/index.md`, `CHANGELOG.md` |
| `LicenseContributor` | `LICENSE`, from `templates/core/licenses/<SPDX id>.tpl` |
| `RepositoryContributor` | `.editorconfig`, `.gitattributes`, `.gitignore` |

Add a file the same way: `$blueprint->addFile('core/<path>.tpl', '<path>')`,
with its template in `templates/core/`. If the file is a master template,
render its slots there and document them in the table above. Declare the files
that must stay out of the Composer archive in `ListSlot::GitAttributesExportIgnore`.

The greeter is the service features build on: get its names from
`GreeterContributor::serviceId()`, `::interfaceName()` and
`::greetingParameter()`. Its greeting comes from the semantic configuration
when the `config` feature is enabled (`GreeterContributor::isConfigurable()`),
from the `<alias>.greeting` parameter otherwise. Private services that nothing
uses are removed from the compiled container: the test kernel exposes the
greeter to the tests through the public `test.<interface>` alias.

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
| Snapshots | `tests/Fixtures/<scenario>/` holds the full expected output of each scenario in `tests/Snapshot/Scenarios.php`; the `everything-*` scenarios enable every built-in feature, like `BUNDLE_FLEX_ALL=1` |
| Working output | `GeneratedBundleTest` checks the snapshots: `php -l`, YAML syntax, `composer.json` validation, coding standards (the generated PHP-CS-Fixer configuration), and container and router loading compared to the blueprint |
| Generated workflows | the CI lints them with [actionlint](https://github.com/rhysd/actionlint) (`tests/Fixtures/*/.github/workflows/`) |
| End to end | `tests/e2e/run.sh` creates real bundles and runs their own tests and quality checks, see below |
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

### End-to-end tests

The plugin runs inside `composer create-project`, so test it end to end with
`tests/e2e/run.sh <target-dir>`. It creates a bundle from a
[bundle-skeleton](https://github.com/yceruto/bundle-skeleton) checkout and this
working copy, checks it (`composer.json`, autoloading, PHP syntax, the plugin is
gone), optionally reinstalls its dependencies with a given Symfony version and
the lowest dependencies, then runs the bundle's own scripts. A missing script
fails the run.

```console
$ git clone -b feat/symfony-8 https://github.com/tito10047/bundle-skeleton /tmp/skeleton
$ E2E_SKELETON_DIR=/tmp/skeleton BUNDLE_FLEX_NAME=acme/blog-bundle BUNDLE_FLEX_ALL=1 \
  BUNDLE_FLEX_CONFIG_FORMAT=yaml E2E_SYMFONY='8.1.*' E2E_SCRIPTS='qa' \
  tests/e2e/run.sh /tmp/e2e/blog-bundle
```

| Variable | |
|---|---|
| `E2E_SKELETON_DIR` | skeleton checkout; when empty, `E2E_SKELETON_REPOSITORY` (default: `https://github.com/tito10047/bundle-skeleton.git`) is cloned at `E2E_SKELETON_REF` (default: `feat/symfony-8`) |
| `E2E_PLUGIN_VERSION` | version given to the working copy; it must satisfy the skeleton's constraint (default: `1.0.0`) |
| `E2E_SYMFONY` | Symfony versions to install, e.g. `7.4.*`, with `SYMFONY_REQUIRE` and a global Symfony Flex like the generated CI |
| `E2E_DEPENDENCIES` | `highest` (default) or `lowest` |
| `E2E_ALLOW_INSECURE` | `1` allows the versions affected by security advisories, e.g. for Symfony 8.0 (default: `0`) |
| `E2E_SCRIPTS` | scripts of the bundle to run (default: `qa`, which runs the tests first) |
| `BUNDLE_FLEX_*` | the answers, see above |

Nothing test-specific may leak into the generated `composer.json`, so neither
`composer.json` is modified. The skeleton is exported with `git archive` (its
`export-ignore` rules apply, as for the dist archive). The plugin is copied
without `vendor/` and declared as a path repository in the global configuration
of a temporary `COMPOSER_HOME`:

```console
$ composer config --global repositories.bundle-flex \
  '{"type": "path", "url": "/path/to/bundle-flex", "options": {"symlink": false, "versions": {"yceruto/bundle-flex": "1.0.0"}}}'
$ composer create-project yceruto/bundle-skeleton /tmp/e2e/blog-bundle --no-interaction \
  --repository='{"type": "path", "url": "/path/to/skeleton", "options": {"symlink": false, "versions": {"yceruto/bundle-skeleton": "1.0.0"}}}'
```

To run it on PHP 8.2, use a `php:8.2-cli` container with Git, `unzip` and
Composer, and mount the repositories at the same paths (a Git worktree refers
to its main repository by absolute path).

The CI runs it for both feature presets (`BUNDLE_FLEX_ALL=0` and `1`) in each
configuration format: on PHP 8.2 with Symfony 7.4 and the lowest dependencies
(`test`), and on PHP 8.5 with Symfony 8.1 (`qa`); and once on PHP 8.3 with
Symfony 7.4 and on PHP 8.4 with Symfony 8.0 (`test`). The skeleton repository
and branch can be changed with the `workflow_dispatch` inputs or the
`SKELETON_REPOSITORY` and `SKELETON_REF` repository variables.

### The test suite of the generated bundle

- `phpunit/phpunit` is required as `^11.5 || ^12.0`: Composer installs the
  latest version the PHP version supports (11.5 on PHP 8.2, 12 on PHP 8.3+).
  `phpunit.dist.xml` must stay valid for both (and for PHPUnit 13, which only
  deprecates `executionOrder="depends"`), without PHPUnit deprecations.
- Deprecations: `failOnDeprecation` with `<source ignoreIndirectDeprecations="true"
  ignoreSuppressionOfDeprecations="true">` and `trigger_deprecation` declared as
  a `<deprecationTrigger>`. Only the deprecations caused by the code of the
  bundle fail the tests, which is what `SYMFONY_DEPRECATIONS_HELPER=max[direct]=0`
  did with the PHPUnit Bridge. `symfony/phpunit-bridge` is not required: with
  PHPUnit 10 and later, deprecations are reported by PHPUnit itself, and the
  extension of the bridge only registers the clock and DNS mocks and the
  `DebugClassLoader`. A feature that needs them can require it.
- Coverage: the generated code is fully covered. Keep it this way (the best
  practices ask for 95%) when adding a feature: generate the tests of its code.
- Check the generated bundle, not only the snapshots: run the end-to-end tests
  (above) on PHP 8.2 with Symfony 7.4 and the lowest dependencies (`test`), and
  on the latest PHP with the latest Symfony (`qa`: the tests, then PHPStan,
  PHP-CS-Fixer and Rector on `config/`, `src/` and `tests/`).
