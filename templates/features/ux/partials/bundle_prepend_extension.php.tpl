<?php
use Yceruto\BundleFlex\Feature\UxFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
?>
// exposes the Stimulus controllers to AssetMapper, when the application uses it,
// see https://symfony.com/doc/current/frontend/create_ux_bundle.html#specifics-for-asset-mapper
$bundlesMetadata = $builder->getParameter('kernel.bundles_metadata');
if (interface_exists(AssetMapperInterface::class) && \is_array($bundlesMetadata) && isset($bundlesMetadata['FrameworkBundle'])) {
    $builder->prependExtensionConfig('framework', [
        'asset_mapper' => [
            'paths' => [
                $this->getPath().'/assets/src' => <?= $helper->php(UxFeature::packageName($naming)) ?>,
            ],
        ],
    ]);
}
