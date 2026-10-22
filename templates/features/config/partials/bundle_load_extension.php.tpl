<?php
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
?>
$container->parameters()
    ->set(<?= $helper->php(GreeterContributor::greetingParameter($naming)) ?>, $config['greeting'])
;
