<?php

declare(strict_types=1);

use Acme\WebPhpArrayBundle\Controller\HelloController;
use Acme\WebPhpArrayBundle\Greeter;
use Acme\WebPhpArrayBundle\GreeterInterface;

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_web_php_array." and aliases for autowiring.
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return [
    'services' => [
        'acme_web_php_array.greeter' => [
            'class' => Greeter::class,
            'arguments' => [
                '%acme_web_php_array.greeting%',
            ],
        ],
        'acme_web_php_array.controller.hello' => [
            'class' => HelloController::class,
            'arguments' => [
                '@acme_web_php_array.greeter',
                '@twig',
            ],
            'tags' => [
                ['name' => 'controller.service_arguments'],
            ],
        ],
        GreeterInterface::class => [
            'alias' => 'acme_web_php_array.greeter',
        ],
    ],
];
