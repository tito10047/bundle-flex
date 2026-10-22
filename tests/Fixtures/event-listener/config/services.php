<?php

declare(strict_types=1);

use Acme\EventListenerBundle\EventListener\GreetingHeaderListener;
use Acme\EventListenerBundle\Greeter;
use Acme\EventListenerBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_event_listener." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('acme_event_listener.greeting', 'Hello')
    ;

    $container->services()
        ->set('acme_event_listener.greeter', Greeter::class)
            ->args([
                param('acme_event_listener.greeting'),
            ])

        ->set('acme_event_listener.event_listener.greeting_header', GreetingHeaderListener::class)
            ->args([
                service('acme_event_listener.greeter'),
            ])
            ->tag('kernel.event_listener', ['event' => 'kernel.response', 'method' => 'onKernelResponse', 'priority' => 0])

        ->alias(GreeterInterface::class, 'acme_event_listener.greeter')
    ;
};
