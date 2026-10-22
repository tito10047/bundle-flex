<?php

declare(strict_types=1);

namespace Acme\DoctrineEntityBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Entry point of the bundle: loads its configuration and its services into the container of the application.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html
 */
final class AcmeDoctrineEntityBundle extends AbstractBundle
{
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // maps the entities of src/Entity/ with the XML files of config/doctrine/ when the application uses
        // Doctrine ORM, whatever its "auto_mapping" setting; its configuration is applied after this one
        if ($builder->hasExtension('doctrine')) {
            $builder->prependExtensionConfig('doctrine', [
                'orm' => [
                    'mappings' => [
                        'AcmeDoctrineEntityBundle' => [
                            'type' => 'xml',
                            'dir' => $this->getPath().'/config/doctrine',
                            'prefix' => 'Acme\\DoctrineEntityBundle\\Entity',
                            'is_bundle' => false,
                        ],
                    ],
                ],
            ]);
        }
    }

    /**
     * @param array<mixed> $config the processed configuration of the bundle
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');
    }
}
