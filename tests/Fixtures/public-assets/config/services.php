<?php

declare(strict_types=1);

use Acme\PublicAssetsBundle\Greeter;
use Acme\PublicAssetsBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_public_assets." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('acme_public_assets.greeting', 'Hello')
    ;

    $container->services()
        ->set('acme_public_assets.greeter', Greeter::class)
            ->args([
                param('acme_public_assets.greeting'),
            ])

        ->alias(GreeterInterface::class, 'acme_public_assets.greeter')
    ;
};
