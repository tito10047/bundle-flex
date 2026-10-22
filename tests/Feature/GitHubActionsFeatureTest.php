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

namespace Yceruto\BundleFlex\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\FileTemplate;
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Feature\GitHubActionsFeature;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class GitHubActionsFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new GitHubActionsFeature();

        self::assertSame('ci', $feature->getName());
        self::assertSame(FeatureGroup::QualityAssurance, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
        self::assertTrue($feature->isEnabledByDefault(new FeatureSelection()));
    }

    public function testContributesTheWorkflowDependabotAndTheBadge(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['ci' => true]));

        (new GitHubActionsFeature())->contribute($blueprint);

        self::assertEquals([
            '.github/workflows/ci.yml' => new FileTemplate('features/ci/.github/workflows/ci.yml.tpl', '.github/workflows/ci.yml'),
            '.github/dependabot.yml' => new FileTemplate('features/ci/.github/dependabot.yml.tpl', '.github/dependabot.yml'),
        ], $blueprint->getFiles());
        self::assertSame(
            ['[![CI](https://github.com/acme/blog-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/acme/blog-bundle/actions/workflows/ci.yml)'],
            $blueprint->slots->getValues(ListSlot::ReadmeBadges),
        );
        self::assertSame(['/.github'], $blueprint->slots->getValues(ListSlot::GitAttributesExportIgnore));
        self::assertSame([], $blueprint->composer->getRequireDev());
    }
}
