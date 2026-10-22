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
use Yceruto\BundleFlex\Blueprint\ArgumentType;
use Yceruto\BundleFlex\Blueprint\ServiceAlias;
use Yceruto\BundleFlex\Blueprint\ServiceArgument;
use Yceruto\BundleFlex\Blueprint\ServiceConfiguration;
use Yceruto\BundleFlex\Blueprint\ServiceDefinition;

final class ServiceConfigurationTest extends TestCase
{
    private ServiceConfiguration $config;

    protected function setUp(): void
    {
        $this->config = new ServiceConfiguration('acme_blog');
    }

    public function testCollectsParametersServicesAndAliases(): void
    {
        self::assertTrue($this->config->isEmpty());

        $greeter = new ServiceDefinition('acme_blog.greeter', 'Acme\\BlogBundle\\Greeter', [ServiceArgument::parameter('acme_blog.greeting')]);
        $internal = new ServiceDefinition('.acme_blog.internal', 'Acme\\BlogBundle\\Internal');
        $alias = new ServiceAlias('Acme\\BlogBundle\\GreeterInterface', 'acme_blog.greeter');
        $idAlias = new ServiceAlias('acme_blog.default', 'acme_blog.greeter', true);

        $this->config->setParameter('acme_blog.greeting', 'Hello');
        $this->config->add($greeter);
        $this->config->add($internal);
        $this->config->addAlias($alias);
        $this->config->addAlias($idAlias);
        $this->config->validate();

        self::assertFalse($this->config->isEmpty());
        self::assertSame(['acme_blog.greeting' => 'Hello'], $this->config->getParameters());
        self::assertSame(['acme_blog.greeter' => $greeter, '.acme_blog.internal' => $internal], $this->config->getServices());
        self::assertSame(['Acme\\BlogBundle\\GreeterInterface' => $alias, 'acme_blog.default' => $idAlias], $this->config->getAliases());
        self::assertSame(['Acme\\BlogBundle\\Greeter', 'Acme\\BlogBundle\\Internal', 'Acme\\BlogBundle\\GreeterInterface'], $this->config->getReferencedClasses());
        self::assertTrue($this->config->hasArgumentOfType(ArgumentType::Parameter));
        self::assertFalse($this->config->hasArgumentOfType(ArgumentType::Service));
    }

    public function testParametersArePrefixed(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The parameter "greeting" must be prefixed with the bundle alias');

        $this->config->setParameter('greeting', 'Hello');
    }

    public function testParametersAreUnique(): void
    {
        $this->config->setParameter('acme_blog.greeting', 'Hello');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The parameter "acme_blog.greeting" is already defined.');

        $this->config->setParameter('acme_blog.greeting', 'Hi');
    }

    public function testServiceIdsArePrefixed(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The service id "greeter" must be prefixed with the bundle alias: "acme_blog.<name>", or ".acme_blog.<name>" for internal services.');

        $this->config->add(new ServiceDefinition('greeter', 'Acme\\Greeter'));
    }

    public function testServiceIdsAreUnique(): void
    {
        $this->config->add(new ServiceDefinition('acme_blog.greeter', 'Acme\\Greeter'));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The service or alias "acme_blog.greeter" is already defined.');

        $this->config->addAlias(new ServiceAlias('acme_blog.greeter', 'acme_blog.other'));
    }

    public function testAliasIdsAreClassesOrPrefixedIds(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The alias "greeter" must be a namespaced class or interface name, or an id prefixed with the bundle alias "acme_blog.".');

        $this->config->addAlias(new ServiceAlias('greeter', 'acme_blog.greeter'));
    }

    public function testAliasesTargetBundleServices(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The alias "Acme\\GreeterInterface" must target a service of the bundle, "logger" given.');

        $this->config->addAlias(new ServiceAlias('Acme\\GreeterInterface', 'logger'));
    }

    public function testAliasesTargetDefinedServices(): void
    {
        $this->config->addAlias(new ServiceAlias('Acme\\GreeterInterface', 'acme_blog.greeter'));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The alias "Acme\\GreeterInterface" targets the undefined service "acme_blog.greeter".');

        $this->config->validate();
    }

    public function testAliasesMayTargetAliases(): void
    {
        $this->config->add(new ServiceDefinition('acme_blog.greeter', 'Acme\\Greeter'));
        $this->config->addAlias(new ServiceAlias('acme_blog.default', 'acme_blog.greeter'));
        $this->config->addAlias(new ServiceAlias('Acme\\GreeterInterface', 'acme_blog.default'));

        $this->config->validate();

        $this->addToAssertionCount(1);
    }
}
