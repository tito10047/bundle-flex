<?php
/**
 * @var Yceruto\BundleFlex\Blueprint\BundleBlueprint $blueprint
 * @var Yceruto\BundleFlex\Naming\BundleNaming       $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper   $helper
 */
$first = true;
?>
<?= "<?php\n" ?>

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

/*
 * Import these routes in the application with:
 *
 *     $routes->import('@<?= $naming->className ?>/config/routes.php');
 *
 * See https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
return static function (RoutingConfigurator $routes): void {
<?php foreach ($blueprint->routes->getRoutes() as $route): ?>
<?php if (!$first): ?>

<?php endif ?>
<?php $first = false ?>
    $routes->add(<?= $helper->php($route->name) ?>, <?= $helper->php($route->path) ?>)
        ->controller(<?= $helper->php($route->controller) ?>)
<?php if ([] !== $route->methods): ?>
        ->methods(<?= $helper->php($route->methods) ?>)
<?php endif ?>
    ;
<?php endforeach ?>
};
