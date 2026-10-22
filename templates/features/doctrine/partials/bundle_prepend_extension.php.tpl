<?php
use Yceruto\BundleFlex\Feature\DoctrineFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
?>
// maps the entities of src/Entity/ with the XML files of config/doctrine/ when the application uses
// Doctrine ORM, whatever its "auto_mapping" setting; its configuration is applied after this one
if ($builder->hasExtension('doctrine')) {
    $builder->prependExtensionConfig('doctrine', [
        'orm' => [
            'mappings' => [
                <?= $helper->php(DoctrineFeature::mappingName($naming)) ?> => [
                    'type' => 'xml',
                    'dir' => $this->getPath().'/config/doctrine',
                    'prefix' => <?= $helper->php($naming->namespace.'\Entity') ?>,
                    'is_bundle' => false,
                ],
            ],
        ],
    ]);
}
