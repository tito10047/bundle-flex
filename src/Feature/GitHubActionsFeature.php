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
 * Continuous integration with GitHub Actions (.github/workflows/ci.yml) and Dependabot (.github/dependabot.yml).
 *
 * The workflow follows the best practices: tests on every supported PHP version and Symfony
 * major version (Symfony Flex with SYMFONY_REQUIRE), with the lowest dependencies, and the
 * quality checks ("qa" scripts) on the latest versions.
 *
 * Features add PHP extensions (ListSlot::CiPhpExtensions), service containers (Slot::CiTestServices)
 * and setup steps (Slot::CiTestSteps) needed by their tests; quality checks are Composer scripts
 * flagged with "qa: true", the workflow runs each of them.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#continuous-integration
 */
final class GitHubActionsFeature implements FeatureInterface
{
    public const NAME = 'ci';

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
        return 'Add a GitHub Actions workflow (every supported PHP and Symfony version) and Dependabot?';
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return true;
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->addFile('features/ci/.github/workflows/ci.yml.tpl', '.github/workflows/ci.yml');
        $blueprint->addFile('features/ci/.github/dependabot.yml.tpl', '.github/dependabot.yml');

        $url = 'https://github.com/'.$blueprint->naming->packageName.'/actions/workflows/ci.yml';
        $blueprint->slots->addValue(ListSlot::ReadmeBadges, \sprintf('[![CI](%s/badge.svg)](%s)', $url, $url));
        $blueprint->slots->addValue(ListSlot::GitAttributesExportIgnore, '/.github');
    }
}
