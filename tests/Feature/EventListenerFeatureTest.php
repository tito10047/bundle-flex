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

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\Constraint;
use Yceruto\BundleFlex\Blueprint\FileTemplate;
use Yceruto\BundleFlex\Blueprint\Partial;
use Yceruto\BundleFlex\Blueprint\ServiceArgument;
use Yceruto\BundleFlex\Blueprint\ServiceDefinition;
use Yceruto\BundleFlex\Blueprint\ServiceTag;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Feature\EventListenerFeature;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Naming\BundleNaming;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class EventListenerFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new EventListenerFeature();

        self::assertSame('event-listener', $feature->getName());
        self::assertSame(FeatureGroup::Features, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
        self::assertFalse($feature->isEnabledByDefault(new FeatureSelection()));
    }

    public function testContributesTheListenerItsTestsAndItsDocumentation(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['event-listener' => true]));

        (new EventListenerFeature())->contribute($blueprint);

        self::assertSame(['symfony/http-foundation' => Constraint::SYMFONY], $blueprint->composer->getRequire());
        self::assertSame(['symfony/event-dispatcher' => Constraint::SYMFONY], $blueprint->composer->getRequireDev());
        self::assertEquals([
            'src/EventListener/GreetingHeaderListener.php' => new FileTemplate('features/event-listener/src/EventListener/GreetingHeaderListener.php.tpl', 'src/EventListener/GreetingHeaderListener.php'),
            'tests/EventListener/GreetingHeaderListenerTest.php' => new FileTemplate('features/event-listener/tests/EventListener/GreetingHeaderListenerTest.php.tpl', 'tests/EventListener/GreetingHeaderListenerTest.php'),
            'tests/Integration/EventListener/GreetingHeaderListenerTest.php' => new FileTemplate('features/event-listener/tests/Integration/EventListener/GreetingHeaderListenerTest.php.tpl', 'tests/Integration/EventListener/GreetingHeaderListenerTest.php'),
        ], $blueprint->getFiles());
        self::assertEquals([new Partial('features/event-listener/partials/docs.md.tpl')], $blueprint->slots->getPartials(Slot::DocsSections));
    }

    public function testRegistersTheListenerExplicitly(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['event-listener' => true]));

        (new EventListenerFeature())->contribute($blueprint);

        self::assertEquals([
            'acme_blog.event_listener.greeting_header' => new ServiceDefinition(
                'acme_blog.event_listener.greeting_header',
                'Acme\BlogBundle\EventListener\GreetingHeaderListener',
                [ServiceArgument::service('acme_blog.greeter')],
                [new ServiceTag('kernel.event_listener', ['event' => 'kernel.response', 'method' => 'onKernelResponse', 'priority' => 0])],
            ),
        ], $blueprint->services->getServices());
    }

    #[DataProvider('provideHeaderNames')]
    public function testNamesTheHeaderAfterTheBundle(string $packageName, string $headerName, string $serviceId): void
    {
        $naming = new BundleNaming($packageName);

        self::assertSame($headerName, EventListenerFeature::headerName($naming));
        self::assertSame($serviceId, EventListenerFeature::serviceId($naming));
    }

    /**
     * @return iterable<string, array{string, string, string}>
     */
    public static function provideHeaderNames(): iterable
    {
        yield 'single word' => ['acme/blog-bundle', 'X-Acme-Blog-Greeting', 'acme_blog.event_listener.greeting_header'];
        yield 'several words' => ['my-vendor/foo-bar-bundle', 'X-My-Vendor-Foo-Bar-Greeting', 'my_vendor_foo_bar.event_listener.greeting_header'];
    }
}
