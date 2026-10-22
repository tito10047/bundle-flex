<?php
use Yceruto\BundleFlex\Blueprint\ComposerManifest;
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Options\License;

/**
 * @var Yceruto\BundleFlex\Blueprint\BundleBlueprint $blueprint
 * @var Yceruto\BundleFlex\Naming\BundleNaming       $naming
 * @var Yceruto\BundleFlex\Options\BundleOptions     $options
 * @var Yceruto\BundleFlex\Template\SlotRenderer     $slots
 */
$badges = $slots->values(ListSlot::ReadmeBadges);
$steps = $slots->renderEach(Slot::ReadmeInstallationSteps);
$composer = $blueprint->composer;
$commands = [];
if ([] !== $composer->getQaScriptNames()) {
    $commands[ComposerManifest::QA_SCRIPT] = ComposerManifest::QA_SCRIPT_DESCRIPTION;
}
foreach ($composer->getScripts() as $name => $script) {
    $commands[$name] = $script->description ?? '';
}
?>
# <?= $naming->className ?>


<?= $options->description ?>

<?php if ([] !== $badges): ?>

<?= implode("\n", $badges) ?>

<?php endif ?>

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
composer require <?= $naming->packageName ?>

```
<?php if ([] !== $steps): ?>

Then, follow the instructions from *Step 3* below.
<?php endif ?>

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require <?= $naming->packageName ?>

```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    <?= $naming->fqcn ?>::class => ['all' => true],
];
```
<?php foreach ($steps as $i => $step): ?>

#### Step <?= $i + 3 ?>: <?= $step ?>

<?php endforeach ?>
<?php if ($slots->has(Slot::ReadmeSections)): ?>

<?= $slots->render(Slot::ReadmeSections) ?>
<?php endif ?>

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
<?php foreach ($commands as $name => $description): ?>
| `composer <?= $name ?>` | <?= $description ?> |
<?php endforeach ?>

The changes of each version are listed in the [changelog](CHANGELOG.md).

## License

<?php if (License::Proprietary === $options->license): ?>
<?= $naming->className ?> is proprietary software, see the [LICENSE](LICENSE) file.
<?php else: ?>
<?= $naming->className ?> is released under the <?= $options->license->getName() ?>, see the [LICENSE](LICENSE) file.
<?php endif ?>
