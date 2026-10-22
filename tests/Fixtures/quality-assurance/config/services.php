<?php

declare(strict_types=1);

use Acme\QualityAssuranceBundle\Greeter;
use Acme\QualityAssuranceBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_quality_assurance." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('acme_quality_assurance.greeting', 'Hello')
    ;

    $container->services()
        ->set('acme_quality_assurance.greeter', Greeter::class)
            ->args([
                param('acme_quality_assurance.greeting'),
            ])

        ->alias(GreeterInterface::class, 'acme_quality_assurance.greeter')
    ;
};
