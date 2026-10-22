<?php

declare(strict_types=1);

use Acme\MinimalBundle\Greeter;
use Acme\MinimalBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_minimal." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('acme_minimal.greeting', 'Hello')
    ;

    $container->services()
        ->set('acme_minimal.greeter', Greeter::class)
            ->args([
                param('acme_minimal.greeting'),
            ])

        ->alias(GreeterInterface::class, 'acme_minimal.greeter')
    ;
};
