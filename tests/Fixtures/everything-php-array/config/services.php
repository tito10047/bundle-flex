<?php

declare(strict_types=1);

use Acme\EverythingPhpArrayBundle\Command\HelloCommand;
use Acme\EverythingPhpArrayBundle\Controller\HelloController;
use Acme\EverythingPhpArrayBundle\EventListener\GreetingHeaderListener;
use Acme\EverythingPhpArrayBundle\Greeter;
use Acme\EverythingPhpArrayBundle\GreeterInterface;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_everything_php_array." and aliases for autowiring.
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return [
    'services' => [
        'acme_everything_php_array.greeter' => [
            'class' => Greeter::class,
            'arguments' => [
                '%acme_everything_php_array.greeting%',
            ],
        ],
        'acme_everything_php_array.controller.hello' => [
            'class' => HelloController::class,
            'arguments' => [
                '@acme_everything_php_array.greeter',
                '@twig',
            ],
            'tags' => [
                ['name' => 'controller.service_arguments'],
            ],
        ],
        'acme_everything_php_array.command.hello' => [
            'class' => HelloCommand::class,
            'arguments' => [
                '@acme_everything_php_array.greeter',
            ],
            'tags' => [
                ['name' => 'console.command', 'command' => 'acme-everything-php-array:hello'],
            ],
        ],
        'acme_everything_php_array.event_listener.greeting_header' => [
            'class' => GreetingHeaderListener::class,
            'arguments' => [
                '@acme_everything_php_array.greeter',
            ],
            'tags' => [
                ['name' => 'kernel.event_listener', 'event' => 'kernel.response', 'method' => 'onKernelResponse', 'priority' => 0],
            ],
        ],
        GreeterInterface::class => [
            'alias' => 'acme_everything_php_array.greeter',
        ],
    ],
];
