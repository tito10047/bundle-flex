<?php

declare(strict_types=1);

namespace Acme\PhpKitchenSinkBundle\Tests\Integration;

use Acme\PhpKitchenSinkBundle\AcmePhpKitchenSinkBundle;
use Acme\PhpKitchenSinkBundle\GreeterInterface;
use Acme\PhpKitchenSinkBundle\Tests\App\TestKernel;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class ConfigurationTest extends KernelTestCase
{
    public function testDefaultConfiguration(): void
    {
        self::assertSame(['greeting' => 'Hello'], $this->processConfiguration([]));
    }

    public function testConfigurationIsMerged(): void
    {
        self::assertSame(['greeting' => 'Hi'], $this->processConfiguration([['greeting' => 'Hey'], ['greeting' => 'Hi']]));
    }

    /**
     * @param array<string, mixed> $config
     */
    #[DataProvider('provideInvalidConfigurations')]
    public function testInvalidConfigurationIsRejected(array $config, string $expectedMessage): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->processConfiguration([$config]);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function provideInvalidConfigurations(): iterable
    {
        yield 'empty greeting' => [['greeting' => ''], 'The path "acme_php_kitchen_sink.greeting" cannot contain an empty value'];
        yield 'greeting not a string' => [['greeting' => 42], 'Invalid type for path "acme_php_kitchen_sink.greeting". Expected "string"'];
        yield 'unknown option' => [['unknown' => true], 'Unrecognized option "unknown" under "acme_php_kitchen_sink"'];
    }

    public function testGreetingIsPassedToTheGreeter(): void
    {
        self::bootKernel(['extension_configs' => ['acme_php_kitchen_sink' => ['greeting' => 'Hi']]]);
        $container = self::getContainer();

        self::assertSame('Hi', $container->getParameter('acme_php_kitchen_sink.greeting'));

        $greeter = $container->get('test.'.GreeterInterface::class);
        self::assertInstanceOf(GreeterInterface::class, $greeter);
        self::assertSame('Hi Jane!', $greeter->greet('Jane'));
    }

    /**
     * Supports the "extension_configs" option, see TestKernel::__construct().
     *
     * @param array<mixed> $options
     */
    protected static function createKernel(array $options = []): TestKernel
    {
        /** @var array<string, array<string, mixed>> $extensionConfigs */
        $extensionConfigs = $options['extension_configs'] ?? [];

        return new TestKernel(extensionConfigs: $extensionConfigs);
    }

    /**
     * Processes the given configurations with the configuration tree of the bundle.
     *
     * @param list<array<string, mixed>> $configs
     *
     * @return array<mixed>
     */
    private function processConfiguration(array $configs): array
    {
        $extension = (new AcmePhpKitchenSinkBundle())->getContainerExtension();
        self::assertInstanceOf(ConfigurationExtensionInterface::class, $extension);

        $configuration = $extension->getConfiguration([], new ContainerBuilder(new ParameterBag(['kernel.environment' => 'test'])));
        self::assertInstanceOf(ConfigurationInterface::class, $configuration);

        return (new Processor())->processConfiguration($configuration, $configs);
    }
}
