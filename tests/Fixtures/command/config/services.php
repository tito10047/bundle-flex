<?php

declare(strict_types=1);

use Acme\CommandBundle\Command\HelloCommand;
use Acme\CommandBundle\Greeter;
use Acme\CommandBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_command." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('acme_command.greeting', 'Hello')
    ;

    $container->services()
        ->set('acme_command.greeter', Greeter::class)
            ->args([
                param('acme_command.greeting'),
            ])

        ->set('acme_command.command.hello', HelloCommand::class)
            ->args([
                service('acme_command.greeter'),
            ])
            ->tag('console.command', ['command' => 'acme-command:hello'])

        ->alias(GreeterInterface::class, 'acme_command.greeter')
    ;
};
