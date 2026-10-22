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
use Yceruto\BundleFlex\Feature\PublicAssetsFeature;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class PublicAssetsFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new PublicAssetsFeature();

        self::assertSame('public-assets', $feature->getName());
        self::assertSame(FeatureGroup::Features, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
        self::assertFalse($feature->isEnabledByDefault(new FeatureSelection()));
    }

    public function testContributesTheStylesheetAndItsInstallation(): void
    {
        $blueprint = $this->contribute(controllers: false, twig: false);

        self::assertEquals([
            'public/css/acme-blog.css' => new FileTemplate('features/public-assets/public/css/stylesheet.css.tpl', 'public/css/acme-blog.css'),
            'tests/Integration/PublicAssetsTest.php' => new FileTemplate('features/public-assets/tests/Integration/PublicAssetsTest.php.tpl', 'tests/Integration/PublicAssetsTest.php'),
        ], $blueprint->getFiles());
        self::assertSame([], $blueprint->composer->getRequire());
        self::assertSame(['symfony/console' => '^7.4 || ^8.0'], $blueprint->composer->getRequireDev());
        self::assertEquals([new Partial('features/public-assets/partials/readme_step.md.tpl')], $blueprint->slots->getPartials(Slot::ReadmeInstallationSteps));
        self::assertEquals([new Partial('features/public-assets/partials/docs.md.tpl')], $blueprint->slots->getPartials(Slot::DocsSections));
    }

    public function testRequiresTheAssetComponentForTheTemplates(): void
    {
        self::assertSame('^7.4 || ^8.0', $this->contribute(controllers: false, twig: true)->composer->getRequireDev()['symfony/asset'] ?? null);
        self::assertSame('^7.4 || ^8.0', $this->contribute(controllers: true, twig: true)->composer->getRequire()['symfony/asset'] ?? null);
    }

    public function testDerivesThePaths(): void
    {
        $naming = OptionsFactory::create('my-vendor/foo-bar-bundle')->naming;

        self::assertSame('css/my-vendor-foo-bar.css', PublicAssetsFeature::stylesheetPath($naming));
        self::assertSame('bundles/myvendorfoobar/css/my-vendor-foo-bar.css', PublicAssetsFeature::stylesheetPublicPath($naming));
    }

    private function contribute(bool $controllers, bool $twig): BundleBlueprint
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['controllers' => $controllers, 'twig' => $twig, 'public-assets' => true]));

        (new PublicAssetsFeature())->contribute($blueprint);

        return $blueprint;
    }
}
