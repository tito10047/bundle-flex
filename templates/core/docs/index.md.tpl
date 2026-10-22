<?php
use Yceruto\BundleFlex\Blueprint\Slot;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming   $naming
 * @var Yceruto\BundleFlex\Options\BundleOptions $options
 * @var Yceruto\BundleFlex\Template\SlotRenderer $slots
 */
?>
# <?= $naming->className ?>


<?= $options->description ?>


## Installation

Follow the [installation instructions](../README.md#installation) of the README.
<?php if ($slots->has(Slot::DocsSections)): ?>

<?= $slots->render(Slot::DocsSections) ?>
<?php endif ?>
