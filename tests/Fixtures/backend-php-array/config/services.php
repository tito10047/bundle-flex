<?php

declare(strict_types=1);

use Acme\BackendPhpArrayBundle\Command\HelloCommand;
use Acme\BackendPhpArrayBundle\EventListener\GreetingHeaderListener;
use Acme\BackendPhpArrayBundle\Greeter;
use Acme\BackendPhpArrayBundle\GreeterInterface;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_backend_php_array." and aliases for autowiring.
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return [
    'services' => [
        'acme_backend_php_array.greeter' => [
            'class' => Greeter::class,
            'arguments' => [
                '%acme_backend_php_array.greeting%',
            ],
        ],
        'acme_backend_php_array.command.hello' => [
            'class' => HelloCommand::class,
            'arguments' => [
                '@acme_backend_php_array.greeter',
            ],
            'tags' => [
                ['name' => 'console.command', 'command' => 'acme-backend-php-array:hello'],
            ],
        ],
        'acme_backend_php_array.event_listener.greeting_header' => [
            'class' => GreetingHeaderListener::class,
            'arguments' => [
                '@acme_backend_php_array.greeter',
            ],
            'tags' => [
                ['name' => 'kernel.event_listener', 'event' => 'kernel.response', 'method' => 'onKernelResponse', 'priority' => 0],
            ],
        ],
        GreeterInterface::class => [
            'alias' => 'acme_backend_php_array.greeter',
        ],
    ],
];
