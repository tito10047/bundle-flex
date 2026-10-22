<?php
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->fqcn,
    $naming->testsNamespace.'\App\TestKernel',
    GreeterContributor::interfaceName($naming),
    'PHPUnit\Framework\Attributes\DataProvider',
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
    'Symfony\Component\Config\Definition\Exception\InvalidConfigurationException',
    'Symfony\Component\Config\Definition\Processor',
    'Symfony\Component\DependencyInjection\ContainerBuilder',
    'Symfony\Component\DependencyInjection\Extension\ConfigurationExtensionInterface',
    'Symfony\Component\DependencyInjection\ParameterBag\ParameterBag',
], $naming->testsNamespace.'\Integration');
$alias = $naming->alias;
$greeting = GreeterContributor::DEFAULT_GREETING;
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\Integration;

<?= $imports->render() ?>

final class ConfigurationTest extends KernelTestCase
{
    public function testDefaultConfiguration(): void
    {
        self::assertSame(['greeting' => <?= $helper->php($greeting) ?>], self::processConfiguration([]));
    }

    public function testConfigurationIsMerged(): void
    {
        self::assertSame(['greeting' => 'Hi'], self::processConfiguration([['greeting' => 'Hey'], ['greeting' => 'Hi']]));
    }

    /**
     * @param array<string, mixed> $config
     */
    #[DataProvider('provideInvalidConfigurations')]
    public function testInvalidConfigurationIsRejected(array $config, string $expectedMessage): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessage($expectedMessage);

        self::processConfiguration([$config]);
    }

    /**
     * @return iterable<string, array{array<string, mixed>, string}>
     */
    public static function provideInvalidConfigurations(): iterable
    {
        yield 'empty greeting' => [['greeting' => ''], 'The path "<?= $alias ?>.greeting" cannot contain an empty value'];
        yield 'greeting not a string' => [['greeting' => 42], 'Invalid type for path "<?= $alias ?>.greeting". Expected "string"'];
        yield 'unknown option' => [['unknown' => true], 'Unrecognized option "unknown" under "<?= $alias ?>"'];
    }

    public function testGreetingIsPassedToTheGreeter(): void
    {
        self::bootKernel(['extension_configs' => [<?= $helper->php($alias) ?> => ['greeting' => 'Hi']]]);
        $container = self::getContainer();

        self::assertSame('Hi', $container->getParameter(<?= $helper->php(GreeterContributor::greetingParameter($naming)) ?>));

        $greeter = $container->get('test.'.<?= $imports->name(GreeterContributor::interfaceName($naming)) ?>::class);
        self::assertInstanceOf(<?= $imports->name(GreeterContributor::interfaceName($naming)) ?>::class, $greeter);
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
    private static function processConfiguration(array $configs): array
    {
        $extension = (new <?= $naming->className ?>())->getContainerExtension();
        self::assertInstanceOf(ConfigurationExtensionInterface::class, $extension);

        $configuration = $extension->getConfiguration([], new ContainerBuilder(new ParameterBag(['kernel.environment' => 'test'])));
        self::assertNotNull($configuration);

        return (new Processor())->processConfiguration($configuration, $configs);
    }
}
