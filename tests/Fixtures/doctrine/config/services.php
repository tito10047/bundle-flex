<?php

declare(strict_types=1);

use Acme\DoctrineEntityBundle\Greeter;
use Acme\DoctrineEntityBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_doctrine_entity." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('acme_doctrine_entity.greeting', 'Hello')
    ;

    $container->services()
        ->set('acme_doctrine_entity.greeter', Greeter::class)
            ->args([
                param('acme_doctrine_entity.greeting'),
            ])

        ->alias(GreeterInterface::class, 'acme_doctrine_entity.greeter')
    ;
};
