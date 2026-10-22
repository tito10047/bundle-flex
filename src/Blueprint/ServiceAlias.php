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
 * Alias of a service, typically from an interface FQCN to the service id so the
 * service can be autowired by applications.
 */
final readonly class ServiceAlias
{
    /**
     * @param string $id     alias id: an interface or class FQCN, or a prefixed service id
     * @param string $target id of a service defined by the bundle
     */
    public function __construct(
        public string $id,
        public string $target,
        public bool $public = false,
    ) {
    }
}
