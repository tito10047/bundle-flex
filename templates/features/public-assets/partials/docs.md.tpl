<?php
use Yceruto\BundleFlex\Feature\PublicAssetsFeature;
use Yceruto\BundleFlex\Feature\TwigFeature;

/**
 * @var Yceruto\BundleFlex\Blueprint\BundleBlueprint $blueprint
 * @var Yceruto\BundleFlex\Naming\BundleNaming       $naming
 */
$path = PublicAssetsFeature::stylesheetPublicPath($naming);
?>
## Web Assets

The bundle provides a stylesheet, `public/<?= PublicAssetsFeature::stylesheetPath($naming) ?>`. The
`assets:install` command installs it in the `public/<?= $naming->publicAssetsDirectory ?>/` directory
of your application (see the [installation instructions](../README.md#installation)).
When your application uses AssetMapper, AssetMapper serves it too, with a versioned URL.
<?php if ($blueprint->hasFeature(TwigFeature::NAME)): ?>
The [templates](#templates) link it, with the `asset()` function of the
[Asset component](https://symfony.com/doc/current/components/asset.html).
<?php endif ?>
Link it in your own templates the same way, `asset()` gives the right URL in both cases:

```twig
<link rel="stylesheet" href="{{ asset('<?= $path ?>') }}">
```
