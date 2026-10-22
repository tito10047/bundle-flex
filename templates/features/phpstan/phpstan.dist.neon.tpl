<?php
use Yceruto\BundleFlex\Blueprint\Slot;

/**
 * @var Yceruto\BundleFlex\Blueprint\BundleBlueprint $blueprint
 * @var Yceruto\BundleFlex\Template\SlotRenderer     $slots
 */
$paths = ['config', 'src', ...($blueprint->hasDirectory('tests') ? ['tests'] : [])];
?>
# PHPStan configuration, run it with "composer phpstan".
# Extensions (Symfony, PHPUnit, strict and deprecation rules) are registered by phpstan/extension-installer.
# To change it locally, create a phpstan.neon file (ignored by Git) that includes this one.
#
# See https://phpstan.org/config-reference

parameters:
    level: max
    paths:
<?php foreach ($paths as $path): ?>
        - <?= $path ?>

<?php endforeach ?>
<?php if ($slots->has(Slot::PhpStanParameters)): ?>

<?= $slots->render(Slot::PhpStanParameters, 4) ?>
<?php endif ?>
