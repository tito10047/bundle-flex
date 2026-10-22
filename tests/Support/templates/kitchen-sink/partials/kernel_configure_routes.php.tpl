<?php
/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming   $naming
 * @var Yceruto\BundleFlex\Options\BundleOptions $options
 */
?>
$routes->import(\dirname(__DIR__, 2).'/config/<?= $options->configFormat->getFileName('routes') ?>');
