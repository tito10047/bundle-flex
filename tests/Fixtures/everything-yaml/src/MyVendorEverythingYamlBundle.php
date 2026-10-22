<?php

declare(strict_types=1);

namespace MyVendor\EverythingYamlBundle;

use Symfony\Component\AssetMapper\AssetMapperInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * Entry point of the bundle: loads its configuration and its services into the container of the application.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html
 */
final class MyVendorEverythingYamlBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // maps the entities of src/Entity/ with the XML files of config/doctrine/ when the application uses
        // Doctrine ORM, whatever its "auto_mapping" setting; its configuration is applied after this one
        if ($builder->hasExtension('doctrine')) {
            $builder->prependExtensionConfig('doctrine', [
                'orm' => [
                    'mappings' => [
                        'MyVendorEverythingYamlBundle' => [
                            'type' => 'xml',
                            'dir' => $this->getPath().'/config/doctrine',
                            'prefix' => 'MyVendor\\EverythingYamlBundle\\Entity',
                            'is_bundle' => false,
                        ],
                    ],
                ],
            ]);
        }

        // exposes the Stimulus controllers to AssetMapper, when the application uses it,
        // see https://symfony.com/doc/current/frontend/create_ux_bundle.html#specifics-for-asset-mapper
        $bundlesMetadata = $builder->getParameter('kernel.bundles_metadata');
        if (interface_exists(AssetMapperInterface::class) && \is_array($bundlesMetadata) && isset($bundlesMetadata['FrameworkBundle'])) {
            $builder->prependExtensionConfig('framework', [
                'asset_mapper' => [
                    'paths' => [
                        $this->getPath().'/assets/src' => '@my-vendor/everything-yaml-bundle',
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
        $container->import('../config/services.yaml');

        $container->parameters()
            ->set('my_vendor_everything_yaml.greeting', $config['greeting'])
        ;
    }
}
