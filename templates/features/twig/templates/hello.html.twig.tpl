<?php
use Yceruto\BundleFlex\Feature\ControllersFeature;
use Yceruto\BundleFlex\Feature\PublicAssetsFeature;
use Yceruto\BundleFlex\Feature\TranslationsFeature;
use Yceruto\BundleFlex\Feature\TwigFeature;
use Yceruto\BundleFlex\Feature\UxFeature;

/**
 * @var Yceruto\BundleFlex\Blueprint\BundleBlueprint $blueprint
 * @var Yceruto\BundleFlex\Naming\BundleNaming       $naming
 */
$translations = $blueprint->hasFeature(TranslationsFeature::NAME);
$ux = $blueprint->hasFeature(UxFeature::NAME);
$domain = $naming->translationDomain;
$message = static fn (string $message): string => $translations ? "{{ '".$message."'|trans({}, '".$domain."') }}" : $message;
$css = $naming->slug.'-hello';
$controller = UxFeature::controllerName($naming);
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
<?php if ($blueprint->hasFeature(PublicAssetsFeature::NAME)): ?>
<link rel="stylesheet" href="{{ asset('<?= PublicAssetsFeature::stylesheetPublicPath($naming) ?>') }}">
<?php endif ?>
<section class="<?= $css ?>"<?= $ux ? " {{ stimulus_controller('".$controller."') }}" : '' ?>>
    <h1 class="<?= $css ?>__title">{% block title %}<?= $message(TranslationsFeature::TITLE_MESSAGE) ?>{% endblock %}</h1>
    <p class="<?= $css ?>__greeting">{{ greeting }}</p>
<?php if ($ux): ?>
    <button type="button" class="<?= $css ?>__close" {{ stimulus_action('<?= $controller ?>', 'close') }}><?= $message(TranslationsFeature::CLOSE_MESSAGE) ?></button>
<?php endif ?>
</section>
