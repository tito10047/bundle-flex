<?php
use Yceruto\BundleFlex\Blueprint\ListSlot;

/**
 * @var Yceruto\BundleFlex\Template\SlotRenderer $slots
 */
$patterns = $slots->values(ListSlot::GitIgnore);
sort($patterns);
?>
<?php foreach ($patterns as $pattern): ?>
<?= $pattern ?>

<?php endforeach ?>
