<?php
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Blueprint\Slot;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\SlotRenderer   $slots
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$hasRoutes = $slots->has(Slot::TestKernelConfigureRoutes);
$bundles = $slots->values(ListSlot::TestKernelBundles);
$imports = $helper->imports([
    $naming->fqcn,
    'Symfony\Bundle\FrameworkBundle\FrameworkBundle',
    'Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait',
    'Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator',
    'Symfony\Component\HttpKernel\Kernel',
    ...($hasRoutes ? ['Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator'] : []),
    ...$bundles,
    ...$slots->values(ListSlot::TestKernelUses),
], $naming->testsNamespace.'\App');
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\App;

<?= $imports->render() ?>

/**
 * Minimal application registering <?= $naming->className ?>, booted by the integration and functional tests.
 *
 * The bundle directory is read-only: the cache and the logs are written to the system temporary
 * directory, in a directory specific to the configuration of the kernel.
 */
final class TestKernel extends <?= $imports->name('Symfony\Component\HttpKernel\Kernel') ?>

{
    use <?= $imports->name('Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait') ?>;

    /**
     * @param array<string, array<string, mixed>> $extensionConfigs configuration of the extensions, indexed by alias,
     *                                                              applied last, e.g. ['<?= $naming->alias ?>' => ['greeting' => 'Hi']]
     */
    public function __construct(
        string $environment = 'test',
        bool $debug = true,
        private readonly array $extensionConfigs = [],
    ) {
        parent::__construct($environment, $debug);
    }

    public function registerBundles(): iterable
    {
        yield new <?= $imports->name('Symfony\Bundle\FrameworkBundle\FrameworkBundle') ?>();
<?php foreach ($bundles as $bundle): ?>
        yield new <?= $imports->name($bundle) ?>();
<?php endforeach ?>
        yield new <?= $imports->name($naming->fqcn) ?>();
    }

    /**
     * Booting registers the exception handler of the ErrorHandler component, which Symfony 7.4 before 7.4.17
     * and Symfony 8.0 never remove: PHPUnit flags the tests as risky. This restores the previous handler,
     * and does nothing on the versions that do not leave their handler behind.
     */
    public function boot(): void
    {
        $previousHandler = set_exception_handler(null);
        restore_exception_handler();

        parent::boot();

        $handler = set_exception_handler(null);
        restore_exception_handler();
        if ($handler !== $previousHandler) {
            restore_exception_handler();
        }
    }

    /**
     * The application has no files of its own: using this directory as the project directory
     * keeps the files of the bundle (config, templates, translations) from being loaded as
     * application files.
     */
    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return $this->getVarDir().'/cache/'.$this->environment;
    }

    public function getLogDir(): string
    {
        return $this->getVarDir().'/log';
    }

    protected function configureContainer(<?= $imports->name('Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator') ?> $container): void
    {
        $container->extension('framework', [
            'test' => true,
        ]);
<?php if ($slots->has(Slot::TestKernelConfigureContainer)): ?>

<?= $slots->render(Slot::TestKernelConfigureContainer, 8) ?>
<?php endif ?>

        foreach ($this->extensionConfigs as $alias => $config) {
            $container->extension($alias, $config);
        }
    }
<?php if ($hasRoutes): ?>

    protected function configureRoutes(<?= $imports->name('Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator') ?> $routes): void
    {
<?= $slots->render(Slot::TestKernelConfigureRoutes, 8) ?>
    }
<?php endif ?>

    private function getVarDir(): string
    {
        return sys_get_temp_dir().'/<?= $naming->slug ?>-bundle/'.hash('xxh128', serialize([__DIR__, $this->extensionConfigs]));
    }
}
