<?php

declare(strict_types=1);

namespace Acme\BackendPhpArrayBundle\Tests\App;

use Acme\BackendPhpArrayBundle\AcmeBackendPhpArrayBundle;
use Acme\BackendPhpArrayBundle\GreeterInterface;
use Composer\InstalledVersions;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Minimal application registering AcmeBackendPhpArrayBundle, booted by the integration and functional tests.
 *
 * The bundle directory is read-only: the cache and the logs are written to the system temporary
 * directory, in a directory specific to the configuration of the kernel.
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @param array<string, array<string, mixed>> $extensionConfigs configuration of the extensions, indexed by alias,
     *                                                              applied last, e.g. ['acme_backend_php_array' => ['greeting' => 'Hi']]
     */
    public function __construct(
        string $environment = 'test',
        bool $debug = true,
        private readonly array $extensionConfigs = [],
    ) {
        parent::__construct($environment, $debug);
    }

    /**
     * The bundles are typed as Bundle: the BundleInterface of HttpKernel is deprecated since Symfony 8.1,
     * and the interface replacing it does not exist in Symfony 7.4.
     *
     * @return iterable<Bundle>
     */
    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new DoctrineBundle();
        yield new AcmeBackendPhpArrayBundle();
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

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'test' => true,
        ]);

        // unused private services are removed from the container: this public alias keeps the
        // greeter available to the tests, the way an application autowiring it does
        $container->services()
            ->alias('test.'.GreeterInterface::class, GreeterInterface::class)
            ->public()
        ;

        $container->extension('doctrine', [
            'dbal' => [
                'url' => 'sqlite:///:memory:',
            ],
            'orm' => [
                // the mapping files are validated against the XML schema of Doctrine ORM
                'validate_xml_mapping' => true,
            ],
        ]);

        // DoctrineBundle 2, the only version supporting PHP 8.2 and 8.3, deprecates the defaults that DoctrineBundle 3 no longer has
        if (version_compare(InstalledVersions::getVersion('doctrine/doctrine-bundle') ?? '', '3.0.0', '<')) {
            $container->extension('doctrine', [
                'orm' => [
                    'enable_lazy_ghost_objects' => true,
                    'enable_native_lazy_objects' => \PHP_VERSION_ID >= 80400,
                    'controller_resolver' => [
                        'auto_mapping' => false,
                    ],
                ],
            ]);
        }

        foreach ($this->extensionConfigs as $alias => $config) {
            $container->extension($alias, $config);
        }
    }

    private function getVarDir(): string
    {
        return sys_get_temp_dir().'/acme-backend-php-array-bundle/'.hash('xxh128', serialize([__DIR__, $this->extensionConfigs]));
    }
}
