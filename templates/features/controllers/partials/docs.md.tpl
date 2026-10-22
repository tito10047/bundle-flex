<?php
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Feature\ControllersFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming       $naming
 * @var Yceruto\BundleFlex\Options\BundleOptions     $options
 */
$route = ControllersFeature::routeName($naming);
$prefix = ControllersFeature::routePrefix($naming);
$greeting = GreeterContributor::DEFAULT_GREETING;
?>
## Routes

The bundle provides a page greeting people, once its routes are imported
(see the [installation instructions](../README.md#installation)):

| Route | Path | Controller |
|---|---|---|
| `<?= $route ?>` | `<?= ControllersFeature::ROUTE_PATH ?>` | `<?= ControllersFeature::className($naming) ?>` |

With the `<?= $prefix ?>` prefix, `<?= $prefix ?>/hello/Jane` greets "<?= $greeting ?> Jane!", and
`<?= $prefix ?>/hello` greets "<?= $greeting ?> <?= ControllersFeature::DEFAULT_NAME ?>!". The name must be a single word.
The page is plain text.

Link to the page by the name of its route, never by its path, which depends
on the prefix chosen by the application:

```twig
<a href="{{ path('<?= $route ?>', {name: 'Jane'}) }}">Greet Jane</a>
```

Run `php bin/console debug:router <?= $route ?>` to display the route.
