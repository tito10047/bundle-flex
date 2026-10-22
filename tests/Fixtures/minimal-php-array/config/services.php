<?php

declare(strict_types=1);

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "acme_minimal_php_array." and aliases for autowiring.
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return [
    'services' => [
        // 'acme_minimal_php_array.some_service' => [
        //     'class' => SomeService::class,
        //     'arguments' => [service('acme_minimal_php_array.other_service')],
        // ],
    ],
];
