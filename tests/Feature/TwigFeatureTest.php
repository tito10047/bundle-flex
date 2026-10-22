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
use Yceruto\BundleFlex\Feature\TwigFeature;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class TwigFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new TwigFeature();

        self::assertSame('twig', $feature->getName());
        self::assertSame(FeatureGroup::Features, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
    }

    public function testIsEnabledByDefaultWhenTheBundleHasControllers(): void
    {
        $feature = new TwigFeature();

        self::assertTrue($feature->isEnabledByDefault(new FeatureSelection(['controllers' => true])));
        self::assertFalse($feature->isEnabledByDefault(new FeatureSelection(['controllers' => false])));
    }

    public function testContributesTheTemplateAndItsTest(): void
    {
        $blueprint = $this->contribute(controllers: false);

        self::assertEquals([
            'templates/hello.html.twig' => new FileTemplate('features/twig/templates/hello.html.twig.tpl', 'templates/hello.html.twig'),
            'tests/Integration/TemplateTest.php' => new FileTemplate('features/twig/tests/Integration/TemplateTest.php.tpl', 'tests/Integration/TemplateTest.php'),
        ], $blueprint->getFiles());
        self::assertSame(['Symfony\Bundle\TwigBundle\TwigBundle'], $blueprint->slots->getValues(ListSlot::TestKernelBundles));
        self::assertEquals([new Partial('features/twig/partials/test_kernel.php.tpl')], $blueprint->slots->getPartials(Slot::TestKernelConfigureContainer));
        self::assertEquals([new Partial('features/twig/partials/docs.md.tpl')], $blueprint->slots->getPartials(Slot::DocsSections));
    }

    public function testTwigIsOnlyNeededByTheTestsWhenTheApplicationRendersTheTemplates(): void
    {
        $blueprint = $this->contribute(controllers: false);

        self::assertFalse(TwigFeature::isRenderedByTheBundle($blueprint));
        self::assertSame([], $blueprint->composer->getRequire());
        self::assertSame(['symfony/twig-bundle' => '^7.4 || ^8.0'], $blueprint->composer->getRequireDev());
    }

    public function testTwigIsRequiredWhenTheBundleRendersTheTemplates(): void
    {
        $blueprint = $this->contribute(controllers: true);

        self::assertTrue(TwigFeature::isRenderedByTheBundle($blueprint));
        self::assertSame(['symfony/twig-bundle' => '^7.4 || ^8.0', 'twig/twig' => TwigFeature::TWIG_CONSTRAINT], $blueprint->composer->getRequire());
        self::assertSame([], $blueprint->composer->getRequireDev());
    }

    public function testRequiresWhatTheTemplatesNeed(): void
    {
        $rendered = new BundleBlueprint(OptionsFactory::create(features: ['controllers' => true, 'twig' => true]));
        $included = new BundleBlueprint(OptionsFactory::create(features: ['controllers' => false, 'twig' => true]));
        $none = new BundleBlueprint(OptionsFactory::create(features: ['controllers' => true, 'twig' => false]));

        TwigFeature::requireForTemplates($rendered, 'symfony/asset', '^7.4 || ^8.0');
        TwigFeature::requireForTemplates($included, 'symfony/asset', '^7.4 || ^8.0');

        self::assertSame(['symfony/asset' => '^7.4 || ^8.0'], $rendered->composer->getRequire());
        self::assertSame(['symfony/asset' => '^7.4 || ^8.0'], $included->composer->getRequireDev());
        self::assertSame([], $included->composer->getRequire());
        self::assertFalse(TwigFeature::isRenderedByTheBundle($none));
    }

    public function testDerivesTheNames(): void
    {
        $naming = OptionsFactory::create('my-vendor/foo-bar-bundle')->naming;

        self::assertSame('@MyVendorFooBar/hello.html.twig', TwigFeature::templateName($naming));
        self::assertSame('templates/bundles/MyVendorFooBarBundle/', TwigFeature::overrideDirectory($naming));
    }

    private function contribute(bool $controllers): BundleBlueprint
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['controllers' => $controllers, 'twig' => true]));

        (new TwigFeature())->contribute($blueprint);

        return $blueprint;
    }
}
