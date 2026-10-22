<?php

declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

/*
 * Configuration tree of the "acme_php_kitchen_sink" extension.
 *
 * Keep it static (no environment variables, no container parameters): Symfony
 * dumps it as the configuration reference of the bundle.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#configuration
 */
return static function (DefinitionConfigurator $definition): void {
    $definition->rootNode()
        ->children()
        ->end()
    ;
};
