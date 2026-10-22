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
use Yceruto\BundleFlex\Blueprint\ComposerScript;
use Yceruto\BundleFlex\Blueprint\FileTemplate;
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Feature\PhpStanFeature;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class PhpStanFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new PhpStanFeature();

        self::assertSame('phpstan', $feature->getName());
        self::assertSame(FeatureGroup::QualityAssurance, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
        self::assertTrue($feature->isEnabledByDefault(new FeatureSelection()));
    }

    public function testContributesTheConfigurationAndTheScript(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['phpstan' => true]));

        (new PhpStanFeature())->contribute($blueprint);

        self::assertSame([
            'phpstan/extension-installer' => '^1.4',
            'phpstan/phpstan' => '^2.1',
            'phpstan/phpstan-deprecation-rules' => '^2.0',
            'phpstan/phpstan-strict-rules' => '^2.0',
            'phpstan/phpstan-symfony' => '^2.0.9',
        ], $blueprint->composer->getRequireDev());
        self::assertSame(['phpstan/extension-installer'], $blueprint->composer->getAllowedPlugins());
        self::assertEquals(['phpstan' => new ComposerScript('phpstan', ['phpstan analyse'], 'Runs the static analysis (PHPStan)', true)], $blueprint->composer->getScripts());
        self::assertEquals(['phpstan.dist.neon' => new FileTemplate('features/phpstan/phpstan.dist.neon.tpl', 'phpstan.dist.neon')], $blueprint->getFiles());
        self::assertSame(['/phpstan.dist.neon'], $blueprint->slots->getValues(ListSlot::GitAttributesExportIgnore));
        self::assertSame(['/phpstan.neon'], $blueprint->slots->getValues(ListSlot::GitIgnore));
    }

    public function testAddsThePhpUnitExtensionWhenTheBundleUsesPhpUnit(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['phpstan' => true]));
        $blueprint->composer->requireDev('phpunit/phpunit', '^11.5 || ^12.0');

        (new PhpStanFeature())->contribute($blueprint);

        self::assertSame('^2.0', $blueprint->composer->getConstraint('phpstan/phpstan-phpunit'));
    }
}
