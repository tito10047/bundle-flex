<?php
use Yceruto\BundleFlex\Blueprint\ComposerManifest;
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Blueprint\Slot;

/**
 * @var Yceruto\BundleFlex\Blueprint\BundleBlueprint $blueprint
 * @var Yceruto\BundleFlex\Template\SlotRenderer     $slots
 * @var Yceruto\BundleFlex\Template\TemplateHelper   $helper
 */
$composer = $blueprint->composer;
$hasTests = $composer->hasScript(ComposerManifest::TEST_SCRIPT);
$hasPhpUnit = null !== $composer->getConstraint('phpunit/phpunit');
$hasPhpUnitBridge = null !== $composer->getConstraint('symfony/phpunit-bridge');
$qaScripts = array_values(array_diff($composer->getQaScriptNames(), [ComposerManifest::TEST_SCRIPT]));
$extensions = $slots->values(ListSlot::CiPhpExtensions);
?>
name: CI

on:
  push:
    branches: [main]
  pull_request:
  # weekly, to catch new releases of the dependencies that break the bundle
  schedule:
    - cron: '0 6 * * 1'
  workflow_dispatch:

permissions:
  contents: read

concurrency:
  group: ${{ github.workflow }}-${{ github.ref }}
  cancel-in-progress: true

jobs:
  tests:
    name: PHP ${{ matrix.php }}, Symfony ${{ matrix.symfony }}${{ matrix.dependencies == 'lowest' && ' (lowest dependencies)' || '' }}
    runs-on: ubuntu-latest
    continue-on-error: ${{ matrix.experimental == true }}
    strategy:
      fail-fast: false
      # every supported PHP version and Symfony major version (Symfony 7.4: PHP 8.2+, Symfony 8: PHP 8.4+)
      # See https://symfony.com/doc/current/bundles/best_practices.html#continuous-integration
      matrix:
        include:
          - php: '8.2'
            symfony: '7.4.*'
            dependencies: lowest
          - php: '8.3'
            symfony: '7.4.*'
          # Symfony 8.0 is no longer maintained: its versions are affected by security advisories that are
          # only fixed in newer versions, so Composer refuses to install them. The bundle still supports
          # Symfony 8.0 (^8.0), so this job, and only this one, allows them.
          - php: '8.4'
            symfony: '8.0.*'
            allow-insecure: true
          - php: '8.5'
            symfony: '7.4.*'
          - php: '8.5'
            symfony: '8.1.*'
          # the next Symfony version, allowed to fail
          - php: '8.5'
            symfony: '8.2.*'
            stability: dev
            experimental: true
    env:
      # restricts the versions of every Symfony package, with Symfony Flex
      SYMFONY_REQUIRE: ${{ matrix.symfony }}
<?php if ($hasPhpUnitBridge): ?>
      # no direct deprecation allowed, except with the lowest dependencies
      SYMFONY_DEPRECATIONS_HELPER: ${{ matrix.dependencies == 'lowest' && 'disabled=1' || 'max[direct]=0' }}
<?php endif ?>
<?php if ($slots->has(Slot::CiTestServices)): ?>
    services:
<?= $slots->render(Slot::CiTestServices, 6) ?>
<?php endif ?>
    steps:
      - name: Checkout
        uses: actions/checkout@v7
        with:
          persist-credentials: false

      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
          coverage: none
          ini-values: zend.assertions=1
<?php if ([] !== $extensions): ?>
          extensions: <?= $helper->yaml(implode(', ', $extensions)) ?>

<?php endif ?>

      - name: Install Symfony Flex
        run: |
          composer global config --no-plugins allow-plugins.symfony/flex true
          composer global require --no-progress --no-scripts --no-plugins symfony/flex

      - name: Allow unstable dependencies
        if: matrix.stability == 'dev'
        run: |
          composer config minimum-stability dev
          composer config prefer-stable true

      - name: Allow the versions affected by security advisories
        if: matrix.allow-insecure
        run: composer config --global audit.block-insecure false

      # caches the downloaded packages (Composer cache directory), never vendor/
      - name: Install dependencies
        uses: ramsey/composer-install@v4
        with:
          dependency-versions: ${{ matrix.dependencies || 'highest' }}
<?php if ($slots->has(Slot::CiTestSteps)): ?>

<?= $slots->render(Slot::CiTestSteps, 6) ?>
<?php endif ?>
<?php if ($hasTests): ?>

      - name: Run the tests
        run: composer <?= ComposerManifest::TEST_SCRIPT ?>

<?php endif ?>

  qa:
    name: Quality assurance
    runs-on: ubuntu-latest
    steps:
      - name: Checkout
        uses: actions/checkout@v7
        with:
          persist-credentials: false

      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.5'
          coverage: none
<?php if ([] !== $extensions): ?>
          extensions: <?= $helper->yaml(implode(', ', $extensions)) ?>

<?php endif ?>

      - name: Validate composer.json
        run: composer validate --strict

      - name: Install dependencies
        uses: ramsey/composer-install@v4
        with:
          dependency-versions: highest
<?php foreach ($qaScripts as $script): ?>

      - name: composer <?= $script ?>

        run: composer <?= $script ?>

<?php endforeach ?>
<?php if ($hasPhpUnit): ?>

  coverage:
    name: Code coverage
    runs-on: ubuntu-latest
    env:
      # the best practices recommend covering at least 95% of the code, set 0 to only report it
      MIN_COVERAGE: 95
<?php if ($slots->has(Slot::CiTestServices)): ?>
    services:
<?= $slots->render(Slot::CiTestServices, 6) ?>
<?php endif ?>
    steps:
      - name: Checkout
        uses: actions/checkout@v7
        with:
          persist-credentials: false

      - name: Set up PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.5'
          coverage: pcov
          ini-values: zend.assertions=1
<?php if ([] !== $extensions): ?>
          extensions: <?= $helper->yaml(implode(', ', $extensions)) ?>

<?php endif ?>

      - name: Install dependencies
        uses: ramsey/composer-install@v4
        with:
          dependency-versions: highest
<?php if ($slots->has(Slot::CiTestSteps)): ?>

<?= $slots->render(Slot::CiTestSteps, 6) ?>
<?php endif ?>

      - name: Run the tests with code coverage
        run: vendor/bin/phpunit --coverage-clover="$RUNNER_TEMP/clover.xml" --coverage-text --only-summary-for-coverage-text

      - name: Check the code coverage
        shell: php {0}
        run: |
          <?= "<?php\n" ?>
          $metrics = simplexml_load_file(getenv('RUNNER_TEMP').'/clover.xml')->project->metrics;
          $statements = (int) $metrics['statements'];
          $coverage = 0 === $statements ? 100.0 : 100 * (int) $metrics['coveredstatements'] / $statements;
          $minimum = (float) getenv('MIN_COVERAGE');
          $summary = sprintf('Line coverage: %.2f%% (minimum: %s%%)', $coverage, $minimum);
          file_put_contents(getenv('GITHUB_STEP_SUMMARY'), "### Code coverage\n\n{$summary}\n", FILE_APPEND);
          echo $summary, PHP_EOL;
          exit($coverage < $minimum ? 1 : 0);

      - name: Upload the coverage report
        uses: actions/upload-artifact@v7
        with:
          name: coverage
          path: ${{ runner.temp }}/clover.xml
<?php endif ?>
