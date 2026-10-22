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

namespace Yceruto\BundleFlex\Contributor\Core;

use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Contributor\ContributorInterface;

/**
 * The bundle class (src/AcmeBlogBundle.php), an AbstractBundle whose methods are
 * assembled from the Bundle* slots.
 */
final class BundleClassContributor implements ContributorInterface
{
    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->addFile('core/src/Bundle.php.tpl', 'src/'.$blueprint->naming->className.'.php');
    }
}
