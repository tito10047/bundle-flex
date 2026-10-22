<?php

declare(strict_types=1);

/*
 * This file is part of the yceruto/bundle-flex package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\BundleFlex\Tests\Snapshot;

use Yceruto\BundleFlex\Feature\FeatureRegistry;
use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Naming\BundleNaming;
use Yceruto\BundleFlex\Options\Author;
use Yceruto\BundleFlex\Options\BundleOptions;
use Yceruto\BundleFlex\Options\ConfigFormat;
use Yceruto\BundleFlex\Options\License;

/**
 * A snapshot scenario: the options a bundle is generated with, and the fixture directory
 * (tests/Fixtures/<name>/) holding the expected output.
 *
 * Package names must be unique across scenarios: generated bundle classes are loaded in the same process.
 */
final readonly class Scenario
{
    /**
     * @param list<string>|true $features enabled features (true: all of them); every other feature is disabled
     */
    public function __construct(
        public string $name,
        public string $packageName,
        public ConfigFormat $configFormat = ConfigFormat::Php,
        public array|bool $features = [],
        public License $license = License::Mit,
    ) {
    }

    public function createOptions(FeatureRegistry $registry): BundleOptions
    {
        $selection = new FeatureSelection();
        foreach ($registry as $name => $feature) {
            $selection = $selection->with($name, true === $this->features || \in_array($name, $this->features, true));
        }

        $naming = new BundleNaming($this->packageName);

        return new BundleOptions($naming, $naming->className.' for Symfony', new Author('Jane Doe', 'jane@example.com'), $this->license, $this->configFormat, $selection);
    }

    public function getFixtureDir(): string
    {
        return \dirname(__DIR__).'/Fixtures/'.$this->name;
    }

    /**
     * Whether the scenario includes the test-only kitchen-sink feature.
     */
    public function hasKitchenSink(): bool
    {
        return true === $this->features || \in_array('kitchen-sink', $this->features, true);
    }
}
