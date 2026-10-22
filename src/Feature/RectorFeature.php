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

use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\ListSlot;

/**
 * Automated refactoring and upgrades with Rector (rector.php, "composer rector" and "composer rector:fix").
 *
 * The PHP and Composer-based (Symfony, PHPUnit, Doctrine, Twig) rule sets target the lowest versions
 * allowed by composer.json, so the suggested changes never break a supported version.
 *
 * @see https://getrector.com/documentation
 */
final class RectorFeature implements FeatureInterface
{
    public const NAME = 'rector';

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
        return 'Add Rector (automated refactoring and upgrades)?';
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return true;
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        $composer = $blueprint->composer;

        // 2.6.4+ applies the version-specific rules of a library for the lowest versions it supports
        $composer->requireDev('rector/rector', '^2.6.4');
        $composer->addScript('rector', 'rector process --dry-run', 'Checks the code for automated refactorings (Rector)', qa: true);
        $composer->addScript('rector:fix', 'rector process', 'Applies the automated refactorings (Rector)');

        $blueprint->addFile('features/rector/rector.php.tpl', 'rector.php');
        $blueprint->slots->addValue(ListSlot::GitAttributesExportIgnore, '/rector.php');
    }
}
