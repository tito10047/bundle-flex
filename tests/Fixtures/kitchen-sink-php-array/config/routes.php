<?php

declare(strict_types=1);

/*
 * Import these routes in the application with:
 *
 *     $routes->import('@AcmePhpArrayKitchenSinkBundle/config/routes.php');
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
return [
    'acme_php_array_kitchen_sink_hello' => [
        'path' => '/hello/{name}',
        'controller' => 'acme_php_array_kitchen_sink.controller.hello',
        'methods' => ['GET', 'HEAD'],
    ],
    'acme_php_array_kitchen_sink_home' => [
        'path' => '/',
        'controller' => 'acme_php_array_kitchen_sink.controller.hello',
    ],
];
