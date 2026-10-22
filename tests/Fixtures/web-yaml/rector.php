<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;

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
        __DIR__.'/config',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withRootFiles()
    ->withPhpSets()
    ->withComposerBased(twig: true, phpunit: true, symfony: true)
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        instanceOf: true,
        earlyReturn: true,
        phpunitCodeQuality: true,
    )
    ->withSkip([
        // the tests call the assertions of PHPUnit statically (self::assertSame()), as PHPStan's strict rules require
        PreferPHPUnitThisCallRector::class,
    ])
;
