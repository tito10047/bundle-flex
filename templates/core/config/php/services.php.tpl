<?php
use Yceruto\BundleFlex\Blueprint\ArgumentType;

/**
 * @var Yceruto\BundleFlex\Blueprint\BundleBlueprint $blueprint
 * @var Yceruto\BundleFlex\Naming\BundleNaming       $naming
 * @var Yceruto\BundleFlex\Options\BundleOptions     $options
 * @var Yceruto\BundleFlex\Template\TemplateHelper   $helper
 */
$format = $options->configFormat;
$config = $blueprint->services;
$functions = [];
if ($config->hasArgumentOfType(ArgumentType::Parameter)) {
    $functions[] = 'Symfony\Component\DependencyInjection\Loader\Configurator\param';
}
if ($config->hasArgumentOfType(ArgumentType::Service)) {
    $functions[] = 'Symfony\Component\DependencyInjection\Loader\Configurator\service';
}
$imports = $helper->imports([
    'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
    ...$config->getReferencedClasses(),
], null, $functions);
?>
<?= "<?php\n" ?>

declare(strict_types=1);

<?= $imports->render() ?>

/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "<?= $naming->alias ?>." and aliases for autowiring.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return static function (<?= $imports->name('Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator') ?> $container): void {
<?php if ($config->isEmpty()): ?>
    // $container->services()
    //     ->set('<?= $naming->alias ?>.some_service', SomeService::class)
    //         ->args([service('<?= $naming->alias ?>.other_service')])
    // ;
<?php endif ?>
<?php if ([] !== $config->getParameters()): ?>
    $container->parameters()
<?php foreach ($config->getParameters() as $name => $value): ?>
        ->set(<?= $helper->php($name) ?>, <?= $helper->configValue($value, $format) ?>)
<?php endforeach ?>
    ;
<?php if ([] !== $config->getServices() || [] !== $config->getAliases()): ?>

<?php endif ?>
<?php endif ?>
<?php if ([] !== $config->getServices() || [] !== $config->getAliases()): ?>
    $container->services()
<?php $first = true ?>
<?php foreach ($config->getServices() as $service): ?>
<?php if (!$first): ?>

<?php endif ?>
<?php $first = false ?>
        ->set(<?= $helper->php($service->id) ?>, <?= $imports->name($service->class) ?>::class)
<?php if ([] !== $service->arguments): ?>
            ->args([
<?php foreach ($service->arguments as $argument): ?>
                <?= $helper->serviceArgument($argument, $format) ?>,
<?php endforeach ?>
            ])
<?php endif ?>
<?php foreach ($service->tags as $tag): ?>
            ->tag(<?= $helper->php($tag->name) ?><?= [] !== $tag->attributes ? ', '.$helper->php($tag->attributes) : '' ?>)
<?php endforeach ?>
<?php if ($service->public): ?>
            ->public()
<?php endif ?>
<?php endforeach ?>
<?php foreach ($config->getAliases() as $alias): ?>
<?php if (!$first): ?>

<?php endif ?>
<?php $first = false ?>
        ->alias(<?= str_contains($alias->id, '\\') ? $imports->name($alias->id).'::class' : $helper->php($alias->id) ?>, <?= $helper->php($alias->target) ?>)
<?php if ($alias->public): ?>
            ->public()
<?php endif ?>
<?php endforeach ?>
    ;
<?php endif ?>
};
