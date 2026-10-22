<?php
/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming $naming
 */
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->namespace ?>\Exception;

/**
 * Implemented by every exception thrown by <?= $naming->className ?>, so they can be caught at once.
 */
interface ExceptionInterface extends \Throwable
{
}
