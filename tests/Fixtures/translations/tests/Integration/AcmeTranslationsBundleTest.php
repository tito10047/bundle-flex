<?php

declare(strict_types=1);

namespace Acme\TranslationsBundle\Tests\Integration;

use Acme\TranslationsBundle\AcmeTranslationsBundle;
use Acme\TranslationsBundle\Greeter;
use Acme\TranslationsBundle\GreeterInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class AcmeTranslationsBundleTest extends KernelTestCase
{
    public function testBundleIsRegistered(): void
    {
        self::assertInstanceOf(AcmeTranslationsBundle::class, self::bootKernel()->getBundle('AcmeTranslationsBundle'));
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

        self::assertSame('Hello', self::getContainer()->getParameter('acme_translations.greeting'));
    }

    public function testServicesAreNotPublic(): void
    {
        $container = self::bootKernel()->getContainer();

        self::assertFalse($container->has('acme_translations.greeter'));
        self::assertFalse($container->has(GreeterInterface::class));
    }

    public function testServicesAreDefinedExplicitly(): void
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.environment' => 'test',
            'kernel.build_dir' => sys_get_temp_dir(),
            'kernel.debug' => false,
        ]));
        $extension = (new AcmeTranslationsBundle())->getContainerExtension();
        self::assertInstanceOf(ExtensionInterface::class, $extension);

        $extension->load([], $container);

        $ids = array_filter(array_keys($container->getDefinitions()), static fn (string $id): bool => str_starts_with(ltrim($id, '.'), 'acme_translations.'));
        self::assertNotEmpty($ids);
        foreach ($ids as $id) {
            $definition = $container->getDefinition($id);
            self::assertFalse($definition->isAutowired(), \sprintf('The "%s" service must not be autowired.', $id));
            self::assertFalse($definition->isAutoconfigured(), \sprintf('The "%s" service must not be autoconfigured.', $id));
        }

        self::assertSame('acme_translations.greeter', (string) $container->getAlias(GreeterInterface::class));
    }
}
