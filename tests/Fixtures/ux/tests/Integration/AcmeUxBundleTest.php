<?php

declare(strict_types=1);

namespace Acme\UxBundle\Tests\Integration;

use Acme\UxBundle\AcmeUxBundle;
use Acme\UxBundle\Greeter;
use Acme\UxBundle\GreeterInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class AcmeUxBundleTest extends KernelTestCase
{
    public function testBundleIsRegistered(): void
    {
        self::assertInstanceOf(AcmeUxBundle::class, self::bootKernel()->getBundle('AcmeUxBundle'));
    }

    public function testGreeterCanBeAutowired(): void
    {
        self::bootKernel();

        $greeter = self::getContainer()->get('test.'.GreeterInterface::class);

        self::assertInstanceOf(Greeter::class, $greeter);
        self::assertSame('Hello Jane!', $greeter->greet('Jane'));
    }

    public function testGreetingParameterHasADefaultValue(): void
    {
        self::bootKernel();

        self::assertSame('Hello', self::getContainer()->getParameter('acme_ux.greeting'));
    }

    public function testServicesAreNotPublic(): void
    {
        $container = self::bootKernel()->getContainer();

        self::assertFalse($container->has('acme_ux.greeter'));
        self::assertFalse($container->has(GreeterInterface::class));
    }

    public function testServicesAreDefinedExplicitly(): void
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.environment' => 'test',
            'kernel.build_dir' => sys_get_temp_dir(),
            'kernel.debug' => false,
        ]));
        $extension = (new AcmeUxBundle())->getContainerExtension();
        self::assertInstanceOf(ExtensionInterface::class, $extension);

        $extension->load([], $container);

        $ids = array_filter(array_keys($container->getDefinitions()), static fn (string $id): bool => str_starts_with(ltrim($id, '.'), 'acme_ux.'));
        self::assertNotEmpty($ids);
        foreach ($ids as $id) {
            $definition = $container->getDefinition($id);
            self::assertFalse($definition->isAutowired(), \sprintf('The "%s" service must not be autowired.', $id));
            self::assertFalse($definition->isAutoconfigured(), \sprintf('The "%s" service must not be autoconfigured.', $id));
        }

        self::assertSame('acme_ux.greeter', (string) $container->getAlias(GreeterInterface::class));
    }
}
