<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/*
 * Import these routes in the application with:
 *
 *     $routes->import('@AcmePhpKitchenSinkBundle/config/routes.php');
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
return static function (RoutingConfigurator $routes): void {
    $routes->add('acme_php_kitchen_sink_hello', '/hello/{name}')
        ->controller('acme_php_kitchen_sink.controller.hello')
        ->methods(['GET', 'HEAD'])
    ;

    $routes->add('acme_php_kitchen_sink_home', '/')
        ->controller('acme_php_kitchen_sink.controller.hello')
    ;
};
