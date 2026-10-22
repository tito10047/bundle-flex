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
 * Static analysis with PHPStan at the maximum level (phpstan.dist.neon, "composer phpstan").
 *
 * Extensions are registered by phpstan/extension-installer: a feature only has to require one
 * (e.g. phpstan/phpstan-doctrine when this feature is enabled) and may configure it through
 * the Slot::PhpStanParameters slot.
 *
 * @see https://phpstan.org/user-guide/getting-started
 */
final class PhpStanFeature implements FeatureInterface
{
    public const NAME = 'phpstan';

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
        return 'Add PHPStan (static analysis at the maximum level)?';
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return true;
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        $composer = $blueprint->composer;

        $composer->requireDev('phpstan/phpstan', '^2.1');
        $composer->requireDev('phpstan/extension-installer', '^1.4');
        $composer->requireDev('phpstan/phpstan-deprecation-rules', '^2.0');
        $composer->requireDev('phpstan/phpstan-strict-rules', '^2.0');
        $composer->requireDev('phpstan/phpstan-symfony', '^2.0.9');
        // quality assurance features contribute last: PHPUnit is known by now if the bundle uses it
        if (null !== $composer->getConstraint('phpunit/phpunit')) {
            $composer->requireDev('phpstan/phpstan-phpunit', '^2.0');
        }
        $composer->allowPlugin('phpstan/extension-installer');
        $composer->addScript('phpstan', 'phpstan analyse', 'Runs the static analysis (PHPStan)', qa: true);

        $blueprint->addFile('features/phpstan/phpstan.dist.neon.tpl', 'phpstan.dist.neon');
        $blueprint->slots->addValue(ListSlot::GitAttributesExportIgnore, '/phpstan.dist.neon');
        // local configuration, including phpstan.dist.neon
        $blueprint->slots->addValue(ListSlot::GitIgnore, '/phpstan.neon');
    }
}
