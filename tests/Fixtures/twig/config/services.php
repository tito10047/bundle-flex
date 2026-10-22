<?php

declare(strict_types=1);

use Acme\TwigBundle\Greeter;
use Acme\TwigBundle\GreeterInterface;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_twig." and aliases for autowiring.
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return [
    'parameters' => [
        'acme_twig.greeting' => 'Hello',
    ],
    'services' => [
        'acme_twig.greeter' => [
            'class' => Greeter::class,
            'arguments' => [
                '%acme_twig.greeting%',
            ],
        ],
        GreeterInterface::class => [
            'alias' => 'acme_twig.greeter',
        ],
    ],
];
