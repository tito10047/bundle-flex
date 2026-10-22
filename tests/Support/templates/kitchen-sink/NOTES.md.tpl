<?php
/**
 * @var string                                  $note
 * @var Yceruto\BundleFlex\Template\SlotRenderer $slots
 */
use Yceruto\BundleFlex\Blueprint\ListSlot;
?>
# Notes

<?= $note ?>


Uses: <?= implode(', ', $slots->values(ListSlot::BundleUses)) ?>

