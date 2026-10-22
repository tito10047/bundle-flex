<?php
/**
 * @var Yceruto\BundleFlex\Blueprint\BundleBlueprint $blueprint
 * @var Yceruto\BundleFlex\Template\TemplateHelper   $helper
 */
$composer = $blueprint->composer;
$packages = array_keys($composer->getRequire() + $composer->getRequireDev());
$requiresVendor = static fn (string $vendor): bool => [] !== array_filter($packages, static fn (string $package): bool => str_starts_with($package, $vendor.'/'));
$hasPhpUnit = null !== $composer->getConstraint('phpunit/phpunit');
$paths = ['config', 'src', ...($blueprint->hasDirectory('tests') ? ['tests'] : [])];
// rule sets bound to the versions of the required packages, in the order of the withComposerBased() arguments
$composerBased = array_keys(array_filter([
    'twig' => $requiresVendor('twig'),
    'doctrine' => $requiresVendor('doctrine'),
    'phpunit' => $hasPhpUnit,
    'symfony' => true,
]));
$preparedSets = ['deadCode', 'codeQuality', 'typeDeclarations', 'privatization', 'instanceOf', 'earlyReturn', ...($hasPhpUnit ? ['phpunitCodeQuality'] : [])];
?>
<?= "<?php\n" ?>

declare(strict_types=1);

use Rector\Config\RectorConfig;
<?php if ($hasPhpUnit): ?>
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
<?php endif ?>

/*
 * Rector configuration: run "composer rector" to list the suggested changes and
 * "composer rector:fix" to apply them.
 *
 * The PHP and Composer-based rule sets target the lowest versions allowed by
 * composer.json ("require" and "require-dev"), so that the changes work with every
 * supported PHP and Symfony version.
 *
 * See https://getrector.com/documentation
 */

return RectorConfig::configure()
    ->withPaths([
<?php foreach ($paths as $path): ?>
        __DIR__.<?= $helper->php('/'.$path) ?>,
<?php endforeach ?>
    ])
    ->withRootFiles()
    ->withPhpSets()
    ->withComposerBased(<?= implode(', ', array_map(static fn (string $set): string => $set.': true', $composerBased)) ?>)
    ->withPreparedSets(
<?php foreach ($preparedSets as $set): ?>
        <?= $set ?>: true,
<?php endforeach ?>
    )
<?php if ($hasPhpUnit): ?>
    ->withSkip([
        // the tests call the assertions of PHPUnit statically (self::assertSame()), as PHPStan's strict rules require
        PreferPHPUnitThisCallRector::class,
    ])
<?php endif ?>
;
