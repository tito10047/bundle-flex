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
use Yceruto\BundleFlex\Options\License;

/**
 * The LICENSE file: the full text of the chosen license, with the copyright line
 * (year and author) when the license has one.
 *
 * The LGPL only lists additional permissions on top of the GPL: its LICENSE file
 * contains both texts, as the LGPL requires.
 */
final class LicenseContributor implements ContributorInterface
{
    /**
     * @param int $year year of the copyright notice
     */
    public function __construct(
        private readonly int $year,
    ) {
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->addFile(self::getTemplate($blueprint->options->license), 'LICENSE', ['year' => $this->year]);
    }

    /**
     * Template of the license text, e.g. "core/licenses/MIT.tpl".
     */
    public static function getTemplate(License $license): string
    {
        return 'core/licenses/'.$license->value.'.tpl';
    }
}
