<?php

declare(strict_types=1);

use Acme\BackendPhpBundle\Command\HelloCommand;
use Acme\BackendPhpBundle\EventListener\GreetingHeaderListener;
use Acme\BackendPhpBundle\Greeter;
use Acme\BackendPhpBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_backend_php." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('acme_backend_php.greeter', Greeter::class)
            ->args([
                param('acme_backend_php.greeting'),
            ])

        ->set('acme_backend_php.command.hello', HelloCommand::class)
            ->args([
                service('acme_backend_php.greeter'),
            ])
            ->tag('console.command', ['command' => 'acme-backend-php:hello'])

        ->set('acme_backend_php.event_listener.greeting_header', GreetingHeaderListener::class)
            ->args([
                service('acme_backend_php.greeter'),
            ])
            ->tag('kernel.event_listener', ['event' => 'kernel.response', 'method' => 'onKernelResponse', 'priority' => 0])

        ->alias(GreeterInterface::class, 'acme_backend_php.greeter')
    ;
};
