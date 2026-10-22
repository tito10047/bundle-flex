<?php

declare(strict_types=1);

/*
 * This file is part of the yceruto/bundle-flex package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\BundleFlex\Feature;

use Composer\Semver\VersionParser;
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\ListSlot;

/**
 * Symfony coding standards with PHP-CS-Fixer (.php-cs-fixer.dist.php, "composer cs" and "composer cs:fix").
 *
 * "cs:fix" runs once the dependencies are installed, so the generated code always follows the rules.
 *
 * @see https://symfony.com/doc/current/contributing/code/standards.html
 */
final class PhpCsFixerFeature implements FeatureInterface
{
    public const NAME = 'cs-fixer';

    /**
     * PHPUnit migration rule sets of PHP-CS-Fixer, by PHPUnit major version (highest first).
     * The set matching the lowest supported PHPUnit version is used.
     */
    private const PHPUNIT_MIGRATION_SETS = [
        11 => '@PHPUnit11x0Migration:risky',
        10 => '@PHPUnit10x0Migration:risky',
    ];

    public function getName(): string
    {
        return self::NAME;
    }

    public function getGroup(): FeatureGroup
    {
        return FeatureGroup::QualityAssurance;
    }

    public function getQuestion(): string
    {
        return 'Add PHP-CS-Fixer (Symfony coding standards)?';
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return true;
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        $composer = $blueprint->composer;

        $composer->requireDev('friendsofphp/php-cs-fixer', '^3.92');
        $composer->addScript('cs', 'php-cs-fixer check --diff', 'Checks the coding standards (PHP-CS-Fixer)', qa: true);
        $composer->addScript('cs:fix', 'php-cs-fixer fix', 'Fixes the coding standards (PHP-CS-Fixer)');
        $composer->runScriptAfterInstall('cs:fix');

        // quality assurance features contribute last: PHPUnit is known by now if the bundle uses it
        $blueprint->addFile('features/cs-fixer/.php-cs-fixer.dist.php.tpl', '.php-cs-fixer.dist.php', [
            'usesPhpUnit' => null !== $composer->getConstraint('phpunit/phpunit'),
            'phpUnitMigrationSet' => self::getPhpUnitMigrationSet($composer->getConstraint('phpunit/phpunit')),
        ]);
        $blueprint->slots->addValue(ListSlot::GitAttributesExportIgnore, '/.php-cs-fixer.dist.php');
        // local configuration and cache
        $blueprint->slots->addValue(ListSlot::GitIgnore, '/.php-cs-fixer.php', '/.php-cs-fixer.cache');
    }

    /**
     * The PHPUnit migration rule set matching the lowest PHPUnit version allowed by the constraint,
     * or null when PHPUnit is not required or older than every set.
     */
    public static function getPhpUnitMigrationSet(?string $phpUnitConstraint): ?string
    {
        if (null === $phpUnitConstraint) {
            return null;
        }

        $lowest = (new VersionParser())->parseConstraints($phpUnitConstraint)->getLowerBound()->getVersion();
        $major = (int) $lowest;
        foreach (self::PHPUNIT_MIGRATION_SETS as $setMajor => $set) {
            if ($major >= $setMajor) {
                return $set;
            }
        }

        return null;
    }
}
