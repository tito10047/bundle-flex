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
$imports = $helper->imports($config->getReferencedClasses(), null, $functions);
?>
<?= "<?php\n" ?>

declare(strict_types=1);

<?php if (!$imports->isEmpty()): ?>
<?= $imports->render() ?>

<?php endif ?>
/*
 * Services are defined explicitly: no autowiring, no autoconfiguration, private by default,
 * with ids prefixed by "<?= $naming->alias ?>." and aliases for autowiring.
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#services
 */
return [
<?php if ([] !== $config->getParameters()): ?>
    'parameters' => [
<?php foreach ($config->getParameters() as $name => $value): ?>
        <?= $helper->php($name) ?> => <?= $helper->configValue($value, $format) ?>,
<?php endforeach ?>
    ],
<?php endif ?>
    'services' => [
<?php if ([] === $config->getServices() && [] === $config->getAliases()): ?>
        // '<?= $naming->alias ?>.some_service' => [
        //     'class' => SomeService::class,
        //     'arguments' => [service('<?= $naming->alias ?>.other_service')],
        // ],
<?php endif ?>
<?php foreach ($config->getServices() as $service): ?>
        <?= $helper->php($service->id) ?> => [
            'class' => <?= $imports->name($service->class) ?>::class,
<?php if ([] !== $service->arguments): ?>
            'arguments' => [
<?php foreach ($service->arguments as $argument): ?>
                <?= $helper->serviceArgument($argument, $format) ?>,
<?php endforeach ?>
            ],
<?php endif ?>
<?php if ([] !== $service->tags): ?>
            'tags' => [
<?php foreach ($service->tags as $tag): ?>
                <?= $helper->php(['name' => $tag->name] + $tag->attributes) ?>,
<?php endforeach ?>
            ],
<?php endif ?>
<?php if ($service->public): ?>
            'public' => true,
<?php endif ?>
        ],
<?php endforeach ?>
<?php foreach ($config->getAliases() as $alias): ?>
        <?= str_contains($alias->id, '\\') ? $imports->name($alias->id).'::class' : $helper->php($alias->id) ?> => [
            'alias' => <?= $helper->php($alias->target) ?>,
<?php if ($alias->public): ?>
            'public' => true,
<?php endif ?>
        ],
<?php endforeach ?>
    ],
];
