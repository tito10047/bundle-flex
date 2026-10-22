<?php

declare(strict_types=1);

/*
 * Import these routes in the application with:
 *
 *     $routes->import('@AcmeWebPhpArrayBundle/config/routes.php');
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
return [
    'acme_web_php_array_hello' => [
        'path' => '/hello/{name}',
        'controller' => 'acme_web_php_array.controller.hello',
        'methods' => ['GET'],
        'defaults' => ['name' => 'World'],
        'requirements' => ['name' => '\\w+'],
    ],
];
