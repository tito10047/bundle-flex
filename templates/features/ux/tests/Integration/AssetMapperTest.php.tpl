<?php
use Yceruto\BundleFlex\Feature\UxFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->fqcn,
    'Symfony\Bundle\FrameworkBundle\FrameworkBundle',
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
    'Symfony\Component\AssetMapper\AssetMapperRepository',
    'Symfony\Component\DependencyInjection\ContainerBuilder',
    'Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface',
    'Symfony\Component\DependencyInjection\ParameterBag\ParameterBag',
], $naming->testsNamespace.'\Integration');
$controllerFile = UxFeature::CONTROLLER.'_controller.js';
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\Integration;

<?= $imports->render() ?>

/**
 * The bundle is a Symfony UX package: assets/package.json declares its Stimulus controllers,
 * and AssetMapper serves them.
 */
final class AssetMapperTest extends KernelTestCase
{
    private const PACKAGE_NAME = <?= $helper->php(UxFeature::packageName($naming)) ?>;

    public function testControllersAreExposedToAssetMapper(): void
    {
        self::bootKernel();
        $repository = self::getContainer()->get('asset_mapper.repository');
        self::assertInstanceOf(AssetMapperRepository::class, $repository);

        self::assertSame(
            realpath($this->getAssetsDir().'/src/<?= $controllerFile ?>'),
            $repository->find(self::PACKAGE_NAME.'/<?= $controllerFile ?>'),
        );
    }

    public function testAddsTheControllersToTheAssetMapperPaths(): void
    {
        $frameworkBundle = new \ReflectionClass(FrameworkBundle::class);
        $container = $this->prependExtensionConfigs([
            'FrameworkBundle' => ['path' => \dirname((string) $frameworkBundle->getFileName()), 'namespace' => $frameworkBundle->getNamespaceName()],
        ]);

        self::assertSame([
            ['asset_mapper' => ['paths' => [$this->getAssetsDir().'/src' => self::PACKAGE_NAME]]],
        ], $container->getExtensionConfig('framework'));
    }

    public function testDoesNotConfigureAssetMapperWithoutFrameworkBundle(): void
    {
        $container = $this->prependExtensionConfigs([]);

        self::assertSame([], $container->getExtensionConfig('framework'));
    }

    public function testPackageJsonDeclaresTheControllers(): void
    {
        $package = json_decode((string) file_get_contents($this->getAssetsDir().'/package.json'), true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($package);
        // StimulusBundle finds the Composer package from this name
        self::assertSame(self::PACKAGE_NAME, $package['name'] ?? null);

        $symfony = $package['symfony'] ?? null;
        self::assertIsArray($symfony);
        self::assertSame($package['peerDependencies'] ?? null, $symfony['importmap'] ?? null);

        $controllers = $symfony['controllers'] ?? null;
        self::assertIsArray($controllers);
        self::assertNotEmpty($controllers);
        foreach ($controllers as $name => $controller) {
            self::assertIsArray($controller, (string) $name);
            $main = $controller['main'] ?? null;
            self::assertIsString($main, (string) $name);
            self::assertFileExists($this->getAssetsDir().'/'.$main);
        }
    }

    /**
     * Prepends the configuration of the bundle to the other extensions, like the kernel does before loading them.
     *
     * @param array<string, array{path: string, namespace: string}> $bundlesMetadata
     */
    private function prependExtensionConfigs(array $bundlesMetadata): ContainerBuilder
    {
        $container = new ContainerBuilder(new ParameterBag([
            'kernel.environment' => 'test',
            'kernel.build_dir' => sys_get_temp_dir(),
            'kernel.bundles_metadata' => $bundlesMetadata,
        ]));
        $extension = (new <?= $naming->className ?>())->getContainerExtension();
        self::assertInstanceOf(PrependExtensionInterface::class, $extension);

        $extension->prepend($container);

        return $container;
    }

    private function getAssetsDir(): string
    {
        return \dirname(__DIR__, 2).'/assets';
    }
}
