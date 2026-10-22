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
 * Version constraints shared by every contributor, so the supported versions are defined once.
 */
final class Constraint
{
    /** Symfony 7.4 LTS requires PHP 8.2; Symfony 8.x requires PHP 8.4 (enforced by Symfony itself). */
    public const PHP = '>=8.2';

    /** Every Symfony component and bundle: 7.4 LTS and the 8.x branch. */
    public const SYMFONY = '^7.4 || ^8.0';

    private function __construct()
    {
    }
}
