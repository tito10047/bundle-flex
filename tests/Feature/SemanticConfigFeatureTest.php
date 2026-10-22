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
use Yceruto\BundleFlex\Blueprint\Partial;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Feature\SemanticConfigFeature;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class SemanticConfigFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new SemanticConfigFeature();

        self::assertSame('config', $feature->getName());
        self::assertSame(FeatureGroup::Features, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
        self::assertTrue($feature->isEnabledByDefault(new FeatureSelection()));
    }

    public function testContributesTheDefinitionAndItsImport(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['config' => true]));

        (new SemanticConfigFeature())->contribute($blueprint);

        self::assertEquals(['config/definition.php' => new FileTemplate('features/config/definition.php.tpl', 'config/definition.php')], $blueprint->getFiles());
        self::assertEquals([new Partial('features/config/partials/bundle_configure.php.tpl')], $blueprint->slots->getPartials(Slot::BundleConfigure));
    }
}
