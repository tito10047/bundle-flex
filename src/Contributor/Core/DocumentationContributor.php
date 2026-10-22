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
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Contributor\ContributorInterface;

/**
 * The documentation every bundle has:
 *
 *  - README.md: description, requirements, the standard installation instructions
 *    (see the Readme* slots), development commands and license;
 *  - docs/index.md: the mandatory entry point of the documentation (see Slot::DocsSections);
 *  - CHANGELOG.md, following the "Keep a Changelog" format.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#documentation
 */
final class DocumentationContributor implements ContributorInterface
{
    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->addFile('core/README.md.tpl', 'README.md');
        $blueprint->addFile('core/CHANGELOG.md.tpl', 'CHANGELOG.md');
        $blueprint->addFile('core/docs/index.md.tpl', 'docs/index.md');

        $blueprint->slots->addValue(ListSlot::GitAttributesExportIgnore, '/docs');
    }
}
