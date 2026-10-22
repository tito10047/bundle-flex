<?php
use Yceruto\BundleFlex\Feature\UxFeature;
use Yceruto\BundleFlex\Options\License;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming   $naming
 * @var Yceruto\BundleFlex\Options\BundleOptions $options
 */
$stimulus = ['@hotwired/stimulus' => UxFeature::STIMULUS_CONSTRAINT];
$package = [
    'name' => UxFeature::packageName($naming),
    'description' => $options->description,
    'version' => '1.0.0',
    // npm has no "proprietary" license identifier
    'license' => License::Proprietary === $options->license ? 'UNLICENSED' : $options->license->value,
    'type' => 'module',
    'symfony' => [
        'controllers' => [
            UxFeature::CONTROLLER => [
                'main' => 'src/'.UxFeature::CONTROLLER.'_controller.js',
                'fetch' => 'lazy',
                'enabled' => true,
            ],
        ],
        // installed in the importmap of applications using AssetMapper, must match "peerDependencies"
        'importmap' => $stimulus,
    ],
    'peerDependencies' => $stimulus,
];
?>
<?= json_encode($package, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE | \JSON_THROW_ON_ERROR) ?>

