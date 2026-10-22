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

namespace Yceruto\BundleFlex\Blueprint;

/**
 * Value slots of the shared ("master") templates: sets of unique strings,
 * kept in registration order. Master templates decide how to sort and render them.
 *
 * Adding a case requires rendering it in the master template that owns it.
 */
enum ListSlot: string
{
    /** Fully-qualified class names imported ("use") by the bundle class, for code added to the Bundle* slots. */
    case BundleUses = 'bundle.uses';

    /** Fully-qualified class names of the bundles registered by the test kernel, besides FrameworkBundle and the bundle itself. */
    case TestKernelBundles = 'test_kernel.bundles';

    /** Fully-qualified class names imported ("use") by the test kernel, for code added to the TestKernel* slots. */
    case TestKernelUses = 'test_kernel.uses';

    /** Markdown badges shown under the title of README.md, one per value, e.g. "[![CI](https://...svg)](https://...)". */
    case ReadmeBadges = 'readme.badges';

    /** Paths excluded from the Composer archive in .gitattributes, e.g. "/phpstan.dist.neon". */
    case GitAttributesExportIgnore = 'gitattributes.export_ignore';

    /** Patterns of .gitignore, e.g. "/.php-cs-fixer.cache". */
    case GitIgnore = 'gitignore';
}
