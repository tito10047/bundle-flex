<?php

declare(strict_types=1);

use Acme\ControllersBundle\Controller\HelloController;
use Acme\ControllersBundle\Greeter;
use Acme\ControllersBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_controllers." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('acme_controllers.greeting', 'Hello')
    ;

    $container->services()
        ->set('acme_controllers.greeter', Greeter::class)
            ->args([
                param('acme_controllers.greeting'),
            ])

        ->set('acme_controllers.controller.hello', HelloController::class)
            ->args([
                service('acme_controllers.greeter'),
            ])
            ->tag('controller.service_arguments')

        ->alias(GreeterInterface::class, 'acme_controllers.greeter')
    ;
};
