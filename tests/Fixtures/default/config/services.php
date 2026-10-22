<?php

declare(strict_types=1);

use Acme\BlogBundle\Greeter;
use Acme\BlogBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_blog." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('acme_blog.greeter', Greeter::class)
            ->args([
                param('acme_blog.greeting'),
            ])

        ->alias(GreeterInterface::class, 'acme_blog.greeter')
    ;
};
