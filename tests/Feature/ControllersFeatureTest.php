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
use Yceruto\BundleFlex\Blueprint\RouteDefinition;
use Yceruto\BundleFlex\Blueprint\ServiceArgument;
use Yceruto\BundleFlex\Blueprint\ServiceDefinition;
use Yceruto\BundleFlex\Blueprint\ServiceTag;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Feature\ControllersFeature;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class ControllersFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new ControllersFeature();

        self::assertSame('controllers', $feature->getName());
        self::assertSame(FeatureGroup::Features, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
        self::assertFalse($feature->isEnabledByDefault(new FeatureSelection()));
    }

    public function testContributesTheControllerItsRouteAndItsTests(): void
    {
        $blueprint = $this->contribute();

        self::assertEquals([
            'src/Controller/HelloController.php' => new FileTemplate('features/controllers/src/Controller/HelloController.php.tpl', 'src/Controller/HelloController.php'),
            'tests/Functional/HelloControllerTest.php' => new FileTemplate('features/controllers/tests/Functional/HelloControllerTest.php.tpl', 'tests/Functional/HelloControllerTest.php'),
        ], $blueprint->getFiles());
        self::assertSame(['symfony/http-foundation' => '^7.4 || ^8.0'], $blueprint->composer->getRequire());
        self::assertSame(['symfony/browser-kit' => '^7.4 || ^8.0'], $blueprint->composer->getRequireDev());

        self::assertEquals([
            'acme_blog.controller.hello' => new ServiceDefinition('acme_blog.controller.hello', 'Acme\BlogBundle\Controller\HelloController', [
                ServiceArgument::service('acme_blog.greeter'),
            ], [new ServiceTag('controller.service_arguments')]),
        ], $blueprint->services->getServices());
        self::assertEquals([
            'acme_blog_hello' => new RouteDefinition('acme_blog_hello', '/hello/{name}', 'acme_blog.controller.hello', ['GET'], ['name' => 'World'], ['name' => '\w+']),
        ], $blueprint->routes->getRoutes());

        self::assertSame(['Psr\Log\NullLogger'], $blueprint->slots->getValues(ListSlot::TestKernelUses));
        self::assertEquals([new Partial('features/controllers/partials/test_kernel.php.tpl')], $blueprint->slots->getPartials(Slot::TestKernelConfigureContainer));
        self::assertEquals([new Partial('features/controllers/partials/test_kernel_routes.php.tpl')], $blueprint->slots->getPartials(Slot::TestKernelConfigureRoutes));
        self::assertEquals([new Partial('features/controllers/partials/readme_step.md.tpl')], $blueprint->slots->getPartials(Slot::ReadmeInstallationSteps));
        self::assertEquals([new Partial('features/controllers/partials/docs.md.tpl')], $blueprint->slots->getPartials(Slot::DocsSections));
    }

    public function testDerivesTheNames(): void
    {
        $naming = OptionsFactory::create('my-vendor/foo-bar-bundle')->naming;

        self::assertSame('my_vendor_foo_bar.controller.hello', ControllersFeature::serviceId($naming));
        self::assertSame('MyVendor\FooBarBundle\Controller\HelloController', ControllersFeature::className($naming));
        self::assertSame('my_vendor_foo_bar_hello', ControllersFeature::routeName($naming));
        self::assertSame('/my-vendor-foo-bar', ControllersFeature::routePrefix($naming));
    }

    private function contribute(): BundleBlueprint
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['controllers' => true]));

        (new ControllersFeature())->contribute($blueprint);

        return $blueprint;
    }
}
