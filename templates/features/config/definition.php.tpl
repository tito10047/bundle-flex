<?php
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
?>
<?= "<?php\n" ?>

declare(strict_types=1);

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

/*
 * Configuration tree of the "<?= $naming->alias ?>" extension.
 *
 * Keep it static (no environment variables, no container parameters): Symfony
 * dumps it as the configuration reference of the bundle.
 *
 * See https://symfony.com/doc/current/bundles/configuration.html
 */
return static function (DefinitionConfigurator $definition): void {
    $definition->rootNode()
        ->children()
            ->stringNode('greeting')
                ->info('The word people are greeted with, e.g. "Hello" in "Hello Jane!".')
                ->example('Hi')
                ->defaultValue(<?= $helper->php(GreeterContributor::DEFAULT_GREETING) ?>)
                ->cannotBeEmpty()
            ->end()
        ->end()
    ;
};
