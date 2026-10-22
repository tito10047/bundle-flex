<?php

declare(strict_types=1);

use Acme\PhpKitchenSinkBundle\CacheWarmer\Formatter;
use Acme\PhpKitchenSinkBundle\Controller\HelloController;
use Acme\PhpKitchenSinkBundle\Greeter;
use Acme\PhpKitchenSinkBundle\GreeterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_php_kitchen_sink." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (ContainerConfigurator $container): void {
    $container->parameters()
        ->set('acme_php_kitchen_sink.greeting', 'Hello %%name%%')
        ->set('acme_php_kitchen_sink.channels', ['email', '@sms', 'it\'s'])
        ->set('acme_php_kitchen_sink.enabled', true)
    ;

    $container->services()
        ->set('acme_php_kitchen_sink.greeter', Greeter::class)
            ->args([
                param('acme_php_kitchen_sink.greeting'),
                service('acme_php_kitchen_sink.formatter'),
                '@literal 100%%',
                42,
                1.5,
                false,
                null,
                ['a', 'b'],
            ])

        ->set('acme_php_kitchen_sink.formatter', Acme\PhpKitchenSinkBundle\Formatter\Formatter::class)

        ->set('.acme_php_kitchen_sink.cache_warmer', Formatter::class)
            ->args([
                service('acme_php_kitchen_sink.formatter'),
            ])
            ->tag('kernel.cache_warmer', ['priority' => 10])

        ->set('acme_php_kitchen_sink.controller.hello', HelloController::class)
            ->args([
                service('acme_php_kitchen_sink.greeter'),
            ])
            ->tag('controller.service_arguments')
            ->public()

        ->alias(GreeterInterface::class, 'acme_php_kitchen_sink.greeter')

        ->alias('acme_php_kitchen_sink.default_greeter', 'acme_php_kitchen_sink.greeter')
            ->public()
    ;
};
