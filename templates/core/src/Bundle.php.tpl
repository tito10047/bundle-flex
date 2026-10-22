<?php
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Blueprint\Slot;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Options\BundleOptions   $options
 * @var Yceruto\BundleFlex\Template\SlotRenderer   $slots
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    'Symfony\Component\DependencyInjection\ContainerBuilder',
    'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
    'Symfony\Component\HttpKernel\Bundle\AbstractBundle',
    ...($slots->has(Slot::BundleConfigure) ? ['Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator'] : []),
    ...$slots->values(ListSlot::BundleUses),
], $naming->namespace);
$containerBuilder = $imports->name('Symfony\Component\DependencyInjection\ContainerBuilder');
$containerConfigurator = $imports->name('Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator');
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->namespace ?>;

<?= $imports->render() ?>

/**
 * Entry point of the bundle: loads its configuration and its services into the container of the application.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html
 */
final class <?= $naming->className ?> extends <?= $imports->name('Symfony\Component\HttpKernel\Bundle\AbstractBundle') ?>

{
<?php if ($slots->has(Slot::BundleConfigure)): ?>
    public function configure(<?= $imports->name('Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator') ?> $definition): void
    {
<?= $slots->render(Slot::BundleConfigure, 8) ?>
    }

<?php endif ?>
<?php if ($slots->has(Slot::BundlePrependExtension)): ?>
    public function prependExtension(<?= $containerConfigurator ?> $container, <?= $containerBuilder ?> $builder): void
    {
<?= $slots->render(Slot::BundlePrependExtension, 8) ?>
    }

<?php endif ?>
    /**
     * @param array<mixed> $config the processed configuration of the bundle
     */
    public function loadExtension(array $config, <?= $containerConfigurator ?> $container, <?= $containerBuilder ?> $builder): void
    {
        $container->import('../config/<?= $options->configFormat->getFileName('services') ?>');
<?php if ($slots->has(Slot::BundleLoadExtension)): ?>

<?= $slots->render(Slot::BundleLoadExtension, 8) ?>
<?php endif ?>
    }
<?php if ($slots->has(Slot::BundleBuild)): ?>

    public function build(<?= $containerBuilder ?> $container): void
    {
        parent::build($container);

<?= $slots->render(Slot::BundleBuild, 8) ?>
    }
<?php endif ?>
}
