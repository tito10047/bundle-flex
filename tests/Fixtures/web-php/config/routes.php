<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/*
 * Import these routes in the application with:
 *
 *     $routes->import('@AcmeWebPhpBundle/config/routes.php');
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
return static function (RoutingConfigurator $routes): void {
    $routes->add('acme_web_php_hello', '/hello/{name}')
        ->controller('acme_web_php.controller.hello')
        ->methods(['GET'])
        ->defaults(['name' => 'World'])
        ->requirements(['name' => '\\w+'])
    ;
};
