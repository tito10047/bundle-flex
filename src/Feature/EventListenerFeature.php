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

namespace Yceruto\BundleFlex\Feature;

use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\Constraint;
use Yceruto\BundleFlex\Blueprint\ServiceArgument;
use Yceruto\BundleFlex\Blueprint\ServiceDefinition;
use Yceruto\BundleFlex\Blueprint\ServiceTag;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Naming\BundleNaming;

/**
 * An event listener adding a greeting header to the responses of the application
 * (src/EventListener/GreetingHeaderListener.php), e.g. "X-Acme-Blog-Greeting: Hello World!".
 *
 * It listens to "kernel.response", so it works without controllers nor routes, and is registered
 * explicitly with a "kernel.event_listener" tag naming its event, method and priority: no
 * #[AsEventListener] attribute, which only works with autoconfiguration.
 *
 * @see https://symfony.com/doc/current/event_dispatcher.html
 */
final class EventListenerFeature implements FeatureInterface
{
    public const NAME = 'event-listener';

    /** Event the listener listens to, KernelEvents::RESPONSE. */
    public const EVENT = 'kernel.response';

    /** Priority of the listener: the default one, before the listeners of the framework with a negative priority. */
    public const PRIORITY = 0;

    public function getName(): string
    {
        return self::NAME;
    }

    public function getGroup(): FeatureGroup
    {
        return FeatureGroup::Features;
    }

    public function getQuestion(): string
    {
        return 'Add an event listener?';
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return false;
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        $naming = $blueprint->naming;

        // the listener uses the Request and Response of the event; the "kernel.event_listener" tag is
        // processed by the EventDispatcher component, which the tests use directly
        $blueprint->composer->require('symfony/http-foundation', Constraint::SYMFONY);
        $blueprint->composer->requireDev('symfony/event-dispatcher', Constraint::SYMFONY);

        $blueprint->addFile('features/event-listener/src/EventListener/GreetingHeaderListener.php.tpl', 'src/EventListener/GreetingHeaderListener.php');
        $blueprint->addFile('features/event-listener/tests/EventListener/GreetingHeaderListenerTest.php.tpl', 'tests/EventListener/GreetingHeaderListenerTest.php');
        $blueprint->addFile('features/event-listener/tests/Integration/EventListener/GreetingHeaderListenerTest.php.tpl', 'tests/Integration/EventListener/GreetingHeaderListenerTest.php');

        $blueprint->services->add(new ServiceDefinition(
            self::serviceId($naming),
            $naming->namespace.'\EventListener\GreetingHeaderListener',
            [ServiceArgument::service(GreeterContributor::serviceId($naming))],
            [new ServiceTag('kernel.event_listener', ['event' => self::EVENT, 'method' => 'onKernelResponse', 'priority' => self::PRIORITY])],
        ));

        $blueprint->slots->addPartial(Slot::DocsSections, 'features/event-listener/partials/docs.md.tpl');
    }

    /**
     * Id of the listener service, e.g. "acme_blog.event_listener.greeting_header".
     */
    public static function serviceId(BundleNaming $naming): string
    {
        return $naming->alias.'.event_listener.greeting_header';
    }

    /**
     * Name of the header added to the responses, e.g. "X-Acme-Blog-Greeting" (prefixed with the
     * bundle name, so it cannot collide with the headers of the application or of other bundles).
     */
    public static function headerName(BundleNaming $naming): string
    {
        return 'X-'.implode('-', array_map(ucfirst(...), explode('-', $naming->slug))).'-Greeting';
    }
}
