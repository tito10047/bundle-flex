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

namespace Yceruto\BundleFlex\Tests\Support;

use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Naming\BundleNaming;
use Yceruto\BundleFlex\Options\Author;
use Yceruto\BundleFlex\Options\BundleOptions;
use Yceruto\BundleFlex\Options\ConfigFormat;
use Yceruto\BundleFlex\Options\License;

final class OptionsFactory
{
    /**
     * @param array<string, bool> $features
     */
    public static function create(
        string $packageName = 'acme/blog-bundle',
        ConfigFormat $configFormat = ConfigFormat::Php,
        array $features = [],
        License $license = License::Mit,
        ?string $email = 'jane@example.com',
    ): BundleOptions {
        $naming = new BundleNaming($packageName);

        return new BundleOptions($naming, $naming->className.' for Symfony', new Author('Jane Doe', $email), $license, $configFormat, new FeatureSelection($features));
    }
}
