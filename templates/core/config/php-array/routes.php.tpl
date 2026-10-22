<?php
/**
 * @var Yceruto\BundleFlex\Blueprint\BundleBlueprint $blueprint
 * @var Yceruto\BundleFlex\Naming\BundleNaming       $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper   $helper
 */
?>
<?= "<?php\n" ?>

declare(strict_types=1);

/*
 * Import these routes in the application with:
 *
 *     $routes->import('@<?= $naming->className ?>/config/routes.php');
 *
 * This file returns the same structure as a YAML file, which requires Symfony 7.4 or newer.
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
return [
<?php foreach ($blueprint->routes->getRoutes() as $route): ?>
    <?= $helper->php($route->name) ?> => [
        'path' => <?= $helper->php($route->path) ?>,
        'controller' => <?= $helper->php($route->controller) ?>,
<?php if ([] !== $route->methods): ?>
        'methods' => <?= $helper->php($route->methods) ?>,
<?php endif ?>
    ],
<?php endforeach ?>
];
