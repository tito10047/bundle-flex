<?php
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->fqcn,
    $naming->namespace.'\Greeter',
    GreeterContributor::interfaceName($naming),
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
    'Symfony\Component\DependencyInjection\ContainerBuilder',
    'Symfony\Component\DependencyInjection\Extension\ExtensionInterface',
    'Symfony\Component\DependencyInjection\ParameterBag\ParameterBag',
], $naming->testsNamespace.'\Integration');
$interface = $imports->name(GreeterContributor::interfaceName($naming));
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\Integration;

<?= $imports->render() ?>

final class <?= $naming->className ?>Test extends <?= $imports->name('Symfony\Bundle\FrameworkBundle\Test\KernelTestCase') ?>

{
    public function testBundleIsRegistered(): void
    {
        $this->assertInstanceOf(<?= $naming->className ?>::class, self::bootKernel()->getBundle(<?= $helper->php($naming->className) ?>));
    }

    public function testGreeterCanBeAutowired(): void
    {
        self::bootKernel();

        $greeter = self::getContainer()->get('test.'.<?= $interface ?>::class);

        $this->assertInstanceOf(Greeter::class, $greeter);
        $this->assertSame(<?= $helper->php(GreeterContributor::DEFAULT_GREETING.' Jane!') ?>, $greeter->greet('Jane'));
    }

    public function testGreetingParameterHasADefaultValue(): void
    {
        self::bootKernel();

        $this->assertSame(<?= $helper->php(GreeterContributor::DEFAULT_GREETING) ?>, self::getContainer()->getParameter(<?= $helper->php(GreeterContributor::greetingParameter($naming)) ?>));
    }

    public function testServicesAreNotPublic(): void
    {
        $container = self::bootKernel()->getContainer();

        $this->assertFalse($container->has(<?= $helper->php(GreeterContributor::serviceId($naming)) ?>));
        $this->assertFalse($container->has(<?= $interface ?>::class));
    }

    public function testServicesAreDefinedExplicitly(): void
    {
        $container = new <?= $imports->name('Symfony\Component\DependencyInjection\ContainerBuilder') ?>(new <?= $imports->name('Symfony\Component\DependencyInjection\ParameterBag\ParameterBag') ?>([
            'kernel.environment' => 'test',
            'kernel.build_dir' => sys_get_temp_dir(),
            'kernel.debug' => false,
        ]));
        $extension = (new <?= $naming->className ?>())->getContainerExtension();
        $this->assertInstanceOf(<?= $imports->name('Symfony\Component\DependencyInjection\Extension\ExtensionInterface') ?>::class, $extension);

        $extension->load([], $container);

        $ids = array_filter(array_keys($container->getDefinitions()), static fn (string $id): bool => str_starts_with(ltrim($id, '.'), <?= $helper->php($naming->alias.'.') ?>));
        $this->assertNotEmpty($ids);
        foreach ($ids as $id) {
            $definition = $container->getDefinition($id);
            $this->assertFalse($definition->isAutowired(), \sprintf('The "%s" service must not be autowired.', $id));
            $this->assertFalse($definition->isAutoconfigured(), \sprintf('The "%s" service must not be autoconfigured.', $id));
        }

        $this->assertSame(<?= $helper->php(GreeterContributor::serviceId($naming)) ?>, (string) $container->getAlias(<?= $interface ?>::class));
    }
}
