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
 * @see https://symfony.com/doc/current/bundles/best_practices.html#routing
 */
final readonly class RouteDefinition
{
    /**
     * @param string       $name       prefixed with the bundle alias, e.g. "acme_blog_hello"
     * @param string       $controller service id of an invokable controller, or "service_id::method"
     * @param list<string> $methods    HTTP methods, uppercase
     */
    public function __construct(
        public string $name,
        public string $path,
        public string $controller,
        public array $methods = [],
    ) {
        if (!str_starts_with($path, '/')) {
            throw new \InvalidArgumentException(\sprintf('The path "%s" of route "%s" must start with "/".', $path, $name));
        }

        foreach ($methods as $method) {
            if (1 !== preg_match('/^[A-Z]+$/', $method)) {
                throw new \InvalidArgumentException(\sprintf('The HTTP method "%s" of route "%s" must be uppercase.', $method, $name));
            }
        }
    }
}
