<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/*
 * Import these routes in the application with:
 *
 *     $routes->import('@AcmeControllersBundle/config/routes.php');
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
return static function (RoutingConfigurator $routes): void {
    $routes->add('acme_controllers_hello', '/hello/{name}')
        ->controller('acme_controllers.controller.hello')
        ->methods(['GET'])
        ->defaults(['name' => 'World'])
        ->requirements(['name' => '\\w+'])
    ;
};
