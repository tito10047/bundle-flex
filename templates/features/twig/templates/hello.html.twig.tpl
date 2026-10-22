<?php
use Yceruto\BundleFlex\Feature\ControllersFeature;
use Yceruto\BundleFlex\Feature\TwigFeature;

/**
 * @var Yceruto\BundleFlex\Blueprint\BundleBlueprint $blueprint
 * @var Yceruto\BundleFlex\Naming\BundleNaming       $naming
 */
$css = $naming->slug.'-hello';
?>
{#
<?php if ($blueprint->hasFeature(ControllersFeature::NAME)): ?>
 # Greets people, rendered by <?= ControllersFeature::className($naming) ?>.
<?php else: ?>
 # Greets people: render it in your templates with {{ include('<?= TwigFeature::templateName($naming) ?>', {greeting: 'Hello Jane!'}) }}.
<?php endif ?>
 #
 # Variables:
 #   greeting  string  the greeting, e.g. "Hello Jane!"
 #
 # It extends no layout, so applications can display it anywhere. Override it in the
 # <?= TwigFeature::overrideDirectory($naming) ?>hello.html.twig file of your application,
 # or only its "title" block by extending '@!<?= $naming->twigNamespace ?>/hello.html.twig'.
 #}
<section class="<?= $css ?>">
    <h1 class="<?= $css ?>__title">{% block title %}Welcome!{% endblock %}</h1>
    <p class="<?= $css ?>__greeting">{{ greeting }}</p>
</section>
