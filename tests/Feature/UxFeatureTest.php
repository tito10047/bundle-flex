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
use Yceruto\BundleFlex\Blueprint\Partial;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Feature\UxFeature;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class UxFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new UxFeature();

        self::assertSame('ux', $feature->getName());
        self::assertSame(FeatureGroup::Features, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
        self::assertFalse($feature->isEnabledByDefault(new FeatureSelection()));
    }

    public function testContributesAStimulusControllerExposedToAssetMapper(): void
    {
        $blueprint = $this->contribute(controllers: false, twig: false);

        self::assertEquals([
            'assets/package.json' => new FileTemplate('features/ux/assets/package.json.tpl', 'assets/package.json'),
            'assets/src/hello_controller.js' => new FileTemplate('features/ux/assets/src/controller.js.tpl', 'assets/src/hello_controller.js'),
            'tests/Integration/AssetMapperTest.php' => new FileTemplate('features/ux/tests/Integration/AssetMapperTest.php.tpl', 'tests/Integration/AssetMapperTest.php'),
        ], $blueprint->getFiles());
        self::assertSame(['symfony-ux'], $blueprint->composer->getKeywords());
        self::assertSame([], $blueprint->composer->getRequire());
        self::assertSame(['symfony/asset-mapper' => '^7.4 || ^8.0'], $blueprint->composer->getRequireDev());
        self::assertSame(['Symfony\Component\AssetMapper\AssetMapperInterface'], $blueprint->slots->getValues(ListSlot::BundleUses));
        self::assertEquals([new Partial('features/ux/partials/bundle_prepend_extension.php.tpl')], $blueprint->slots->getPartials(Slot::BundlePrependExtension));
        self::assertEquals([new Partial('features/ux/partials/readme_step.md.tpl')], $blueprint->slots->getPartials(Slot::ReadmeInstallationSteps));
        self::assertEquals([new Partial('features/ux/partials/docs.md.tpl')], $blueprint->slots->getPartials(Slot::DocsSections));
        self::assertSame([], $blueprint->slots->getValues(ListSlot::TestKernelBundles));
    }

    public function testRequiresStimulusBundleForTheTemplates(): void
    {
        $included = $this->contribute(controllers: false, twig: true);
        $rendered = $this->contribute(controllers: true, twig: true);

        self::assertSame(UxFeature::STIMULUS_BUNDLE_CONSTRAINT, $included->composer->getRequireDev()['symfony/stimulus-bundle'] ?? null);
        self::assertSame(UxFeature::STIMULUS_BUNDLE_CONSTRAINT, $rendered->composer->getRequire()['symfony/stimulus-bundle'] ?? null);
        self::assertSame(['Symfony\UX\StimulusBundle\StimulusBundle'], $rendered->slots->getValues(ListSlot::TestKernelBundles));
    }

    public function testDerivesTheNames(): void
    {
        $naming = OptionsFactory::create('my_vendor/foo-bar-bundle')->naming;

        self::assertSame('@my_vendor/foo-bar-bundle', UxFeature::packageName($naming));
        self::assertSame('my_vendor/foo-bar-bundle/hello', UxFeature::controllerName($naming));
        self::assertSame('my-vendor--foo-bar-bundle--hello', UxFeature::controllerIdentifier($naming));
    }

    private function contribute(bool $controllers, bool $twig): BundleBlueprint
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['controllers' => $controllers, 'twig' => $twig, 'ux' => true]));

        (new UxFeature())->contribute($blueprint);

        return $blueprint;
    }
}
