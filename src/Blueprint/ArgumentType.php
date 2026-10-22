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

enum ArgumentType
{
    /** Reference to another service, by id. */
    case Service;

    /** Reference to a container parameter, by name. */
    case Parameter;

    /** Literal value. */
    case Value;
}
