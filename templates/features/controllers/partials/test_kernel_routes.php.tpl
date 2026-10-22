<?php
/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming   $naming
 * @var Yceruto\BundleFlex\Options\BundleOptions $options
 */
?>
// imported like an application does, see README.md
$routes->import('@<?= $naming->className ?>/config/<?= $options->configFormat->getFileName('routes') ?>');
