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

namespace Yceruto\BundleFlex\Tests\Blueprint;

use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Blueprint\RouteDefinition;
use Yceruto\BundleFlex\Blueprint\RoutingConfiguration;

final class RoutingConfigurationTest extends TestCase
{
    public function testCollectsRoutes(): void
    {
        $routes = new RoutingConfiguration('acme_blog');
        self::assertTrue($routes->isEmpty());

        $route = new RouteDefinition('acme_blog_hello', '/hello', 'acme_blog.controller.hello');
        $routes->add($route);

        self::assertFalse($routes->isEmpty());
        self::assertSame(['acme_blog_hello' => $route], $routes->getRoutes());
    }

    public function testRouteNamesArePrefixed(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The route name "hello" must be prefixed with the bundle alias, e.g. "acme_blog_hello".');

        (new RoutingConfiguration('acme_blog'))->add(new RouteDefinition('hello', '/', 'acme_blog.controller'));
    }

    public function testRouteNamesAreUnique(): void
    {
        $routes = new RoutingConfiguration('acme_blog');
        $routes->add(new RouteDefinition('acme_blog_hello', '/', 'acme_blog.controller'));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The route "acme_blog_hello" is already defined.');

        $routes->add(new RouteDefinition('acme_blog_hello', '/other', 'acme_blog.controller'));
    }
}
