<?php
use Yceruto\BundleFlex\Feature\ControllersFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming   $naming
 * @var Yceruto\BundleFlex\Options\BundleOptions $options
 */
$resource = '@'.$naming->className.'/config/'.$options->configFormat->getFileName('routes');
$prefix = ControllersFeature::routePrefix($naming);
?>
Import the Routes

Import the routes of the bundle in your application, under the prefix of
your choice:

```yaml
# config/routes/<?= $naming->alias ?>.yaml
<?= $naming->alias ?>:
    resource: '<?= $resource ?>'
    prefix: <?= $prefix ?>

```

Or in PHP:

```php
// config/routes/<?= $naming->alias ?>.php
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('<?= $resource ?>')
        ->prefix('<?= $prefix ?>')
    ;
};
```
