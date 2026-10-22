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
use Yceruto\BundleFlex\Feature\RectorFeature;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class RectorFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new RectorFeature();

        self::assertSame('rector', $feature->getName());
        self::assertSame(FeatureGroup::QualityAssurance, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
        self::assertTrue($feature->isEnabledByDefault(new FeatureSelection()));
    }

    public function testContributesTheConfigurationAndTheScripts(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['rector' => true]));

        (new RectorFeature())->contribute($blueprint);

        self::assertSame(['rector/rector' => '^2.6.4'], $blueprint->composer->getRequireDev());
        self::assertEquals([
            'rector' => new ComposerScript('rector', ['rector process --dry-run'], 'Checks the code for automated refactorings (Rector)', true),
            'rector:fix' => new ComposerScript('rector:fix', ['rector process'], 'Applies the automated refactorings (Rector)'),
        ], $blueprint->composer->getScripts());
        self::assertEquals(['rector.php' => new FileTemplate('features/rector/rector.php.tpl', 'rector.php')], $blueprint->getFiles());
        self::assertSame(['/rector.php'], $blueprint->slots->getValues(ListSlot::GitAttributesExportIgnore));
    }
}
