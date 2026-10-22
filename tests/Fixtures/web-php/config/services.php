<?php

declare(strict_types=1);

use Acme\WebPhpBundle\Controller\HelloController;
use Acme\WebPhpBundle\Greeter;
use Acme\WebPhpBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_web_php." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('acme_web_php.greeter', Greeter::class)
            ->args([
                param('acme_web_php.greeting'),
            ])

        ->set('acme_web_php.controller.hello', HelloController::class)
            ->args([
                service('acme_web_php.greeter'),
                service('twig'),
            ])
            ->tag('controller.service_arguments')

        ->alias(GreeterInterface::class, 'acme_web_php.greeter')
    ;
};
