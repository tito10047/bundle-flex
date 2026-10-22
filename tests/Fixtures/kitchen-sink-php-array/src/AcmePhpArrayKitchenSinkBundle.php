<?php

declare(strict_types=1);

namespace Acme\PhpArrayKitchenSinkBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @see https://symfony.com/doc/current/bundles/best_practices.html
 */
final class AcmePhpArrayKitchenSinkBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('framework', ['enabled' => true]);
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.php');

        $container->parameters()
            ->set('acme_php_array_kitchen_sink.loaded', true)
        ;
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->getParameterBag()->set('kitchen_sink.priority', PassConfig::TYPE_BEFORE_OPTIMIZATION);

        // compiler passes would be registered here, e.g. PassConfig::TYPE_BEFORE_OPTIMIZATION
    }
}
