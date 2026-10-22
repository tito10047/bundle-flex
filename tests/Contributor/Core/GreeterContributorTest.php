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

namespace Yceruto\BundleFlex\Tests\Contributor\Core;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Blueprint\ArgumentType;
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Blueprint\Partial;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class GreeterContributorTest extends TestCase
{
    public function testContributesTheGreeterAndItsTests(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['config' => true]));

        (new GreeterContributor())->contribute($blueprint);

        self::assertSame([
            'src/GreeterInterface.php',
            'src/Greeter.php',
            'src/Exception/ExceptionInterface.php',
            'src/Exception/InvalidArgumentException.php',
            'tests/GreeterTest.php',
        ], array_keys($blueprint->getFiles()));

        $service = $blueprint->services->getServices()['acme_blog.greeter'] ?? null;
        self::assertNotNull($service);
        self::assertSame('Acme\BlogBundle\Greeter', $service->class);
        self::assertFalse($service->public);
        self::assertCount(1, $service->arguments);
        self::assertSame(ArgumentType::Parameter, $service->arguments[0]->type);
        self::assertSame('acme_blog.greeting', $service->arguments[0]->getReference());

        $alias = $blueprint->services->getAliases()['Acme\BlogBundle\GreeterInterface'] ?? null;
        self::assertNotNull($alias);
        self::assertSame('acme_blog.greeter', $alias->target);
        self::assertFalse($alias->public);

        self::assertSame(['Acme\BlogBundle\GreeterInterface'], $blueprint->slots->getValues(ListSlot::TestKernelUses));
        self::assertEquals([new Partial('core/tests/partials/kernel_greeter_alias.php.tpl')], $blueprint->slots->getPartials(Slot::TestKernelConfigureContainer));
        self::assertEquals([new Partial('core/docs/partials/usage.md.tpl')], $blueprint->slots->getPartials(Slot::DocsSections));
    }

    public function testTheSemanticConfigurationSetsTheGreeting(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['config' => true]));

        (new GreeterContributor())->contribute($blueprint);

        self::assertTrue(GreeterContributor::isConfigurable($blueprint));
        self::assertSame([], $blueprint->services->getParameters());
    }

    /**
     * @return iterable<string, array{array<string, bool>}>
     */
    public static function provideFeaturesWithoutSemanticConfiguration(): iterable
    {
        yield 'disabled' => [['config' => false]];
        yield 'not registered' => [[]];
    }

    /**
     * @param array<string, bool> $features
     */
    #[DataProvider('provideFeaturesWithoutSemanticConfiguration')]
    public function testAParameterSetsTheGreetingWithoutSemanticConfiguration(array $features): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: $features));

        (new GreeterContributor())->contribute($blueprint);

        self::assertFalse(GreeterContributor::isConfigurable($blueprint));
        self::assertSame(['acme_blog.greeting' => 'Hello'], $blueprint->services->getParameters());
    }

    public function testNames(): void
    {
        $naming = OptionsFactory::create('my-vendor/foo-bundle')->naming;

        self::assertSame('my_vendor_foo.greeter', GreeterContributor::serviceId($naming));
        self::assertSame('my_vendor_foo.greeting', GreeterContributor::greetingParameter($naming));
        self::assertSame('MyVendor\FooBundle\GreeterInterface', GreeterContributor::interfaceName($naming));
    }
}
