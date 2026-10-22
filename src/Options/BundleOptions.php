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

namespace Yceruto\BundleFlex\Options;

use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Naming\BundleNaming;

/**
 * Answers of the bundle assistant: everything needed to generate the bundle.
 */
final readonly class BundleOptions
{
    public function __construct(
        public BundleNaming $naming,
        public string $description,
        public Author $author,
        public License $license,
        public ConfigFormat $configFormat,
        public FeatureSelection $features,
    ) {
    }

    public function hasFeature(string $feature): bool
    {
        return $this->features->isEnabled($feature);
    }
}
