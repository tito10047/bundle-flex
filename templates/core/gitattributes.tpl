<?php
use Yceruto\BundleFlex\Blueprint\ListSlot;

/**
 * @var Yceruto\BundleFlex\Template\SlotRenderer $slots
 */
$paths = $slots->values(ListSlot::GitAttributesExportIgnore);
sort($paths);
?>
# Files and directories excluded from the archive installed by Composer
<?php foreach ($paths as $path): ?>
<?= $path ?> export-ignore
<?php endforeach ?>
