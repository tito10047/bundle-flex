<?php

declare(strict_types=1);

use Acme\UxBundle\Greeter;
use Acme\UxBundle\GreeterInterface;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_ux." and aliases for autowiring.
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return [
    'parameters' => [
        'acme_ux.greeting' => 'Hello',
    ],
    'services' => [
        'acme_ux.greeter' => [
            'class' => Greeter::class,
            'arguments' => [
                '%acme_ux.greeting%',
            ],
        ],
        GreeterInterface::class => [
            'alias' => 'acme_ux.greeter',
        ],
    ],
];
