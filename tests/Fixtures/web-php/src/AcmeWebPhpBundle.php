<?php

declare(strict_types=1);

namespace Acme\WebPhpBundle;

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
final class AcmeWebPhpBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        // exposes the Stimulus controllers to AssetMapper, when the application uses it,
        // see https://symfony.com/doc/current/frontend/create_ux_bundle.html#specifics-for-asset-mapper
        $bundlesMetadata = $builder->getParameter('kernel.bundles_metadata');
        if (interface_exists(AssetMapperInterface::class) && \is_array($bundlesMetadata) && isset($bundlesMetadata['FrameworkBundle'])) {
            $builder->prependExtensionConfig('framework', [
                'asset_mapper' => [
                    'paths' => [
                        $this->getPath().'/assets/src' => '@acme/web-php-bundle',
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

        $container->parameters()
            ->set('acme_web_php.greeting', $config['greeting'])
        ;
    }
}
