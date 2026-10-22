<?php
use Yceruto\BundleFlex\Feature\DoctrineFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->fqcn,
    $naming->namespace.'\Entity\Greeting',
    'Doctrine\Bundle\DoctrineBundle\DoctrineBundle',
    'Doctrine\ORM\EntityManagerInterface',
    'Doctrine\ORM\Tools\SchemaTool',
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
    'Symfony\Component\DependencyInjection\ContainerBuilder',
    'Symfony\Component\DependencyInjection\Extension\ExtensionInterface',
    'Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface',
    'Symfony\Component\DependencyInjection\ParameterBag\ParameterBag',
], $naming->testsNamespace.'\Integration\Doctrine');
$bundle = $imports->name($naming->fqcn);
$greeting = $imports->name($naming->namespace.'\Entity\Greeting');
$entityPrefix = $helper->php($naming->namespace.'\Entity');
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\Integration\Doctrine;

<?= $imports->render() ?>

final class GreetingMappingTest extends <?= $imports->name('Symfony\Bundle\FrameworkBundle\Test\KernelTestCase') ?>

{
    public function testMappingIsPrependedWhenDoctrineBundleIsEnabled(): void
    {
        $bundle = new <?= $bundle ?>();
        $doctrineExtension = (new <?= $imports->name('Doctrine\Bundle\DoctrineBundle\DoctrineBundle') ?>())->getContainerExtension();
        self::assertInstanceOf(<?= $imports->name('Symfony\Component\DependencyInjection\Extension\ExtensionInterface') ?>::class, $doctrineExtension);

        $container = $this->createContainerBuilder();
        $container->registerExtension($doctrineExtension);
        $this->prepend($bundle, $container);

        self::assertSame([[
            'orm' => [
                'mappings' => [
                    <?= $helper->php(DoctrineFeature::mappingName($naming)) ?> => [
                        'type' => 'xml',
                        'dir' => $bundle->getPath().'/config/doctrine',
                        'prefix' => <?= $entityPrefix ?>,
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]], $container->getExtensionConfig('doctrine'));
    }

    public function testNothingIsPrependedWithoutDoctrineBundle(): void
    {
        $container = $this->createContainerBuilder();
        $this->prepend(new <?= $bundle ?>(), $container);

        self::assertSame([], $container->getExtensionConfig('doctrine'));
    }

    /**
     * The table and column names come from config/doctrine/Greeting.orm.xml, validated against the
     * XML schema of Doctrine ORM by the test application.
     */
    public function testEntityIsMappedWithTheXmlFile(): void
    {
        $metadata = $this->getEntityManager()->getClassMetadata(<?= $greeting ?>::class);
        self::assertSame(<?= $helper->php(DoctrineFeature::tableName($naming)) ?>, $metadata->getTableName());
        self::assertSame(['id'], $metadata->getIdentifierFieldNames());
        self::assertEqualsCanonicalizing(['id', 'name', 'message', 'createdAt'], $metadata->getFieldNames());
        self::assertSame('created_at', $metadata->getColumnName('createdAt'));
    }

    public function testGreetingIsPersisted(): void
    {
        $entityManager = $this->getEntityManager();
        (new <?= $imports->name('Doctrine\ORM\Tools\SchemaTool') ?>($entityManager))->createSchema([$entityManager->getClassMetadata(<?= $greeting ?>::class)]);

        $greeting = new <?= $greeting ?>('Jane', 'Hello Jane!', new \DateTimeImmutable('2026-01-01 10:00:00'));
        $entityManager->persist($greeting);
        $entityManager->flush();
        $entityManager->clear();

        $id = $greeting->getId();
        self::assertNotNull($id);

        $found = $entityManager->find(<?= $greeting ?>::class, $id);
        self::assertInstanceOf(<?= $greeting ?>::class, $found);
        self::assertNotSame($greeting, $found);
        self::assertSame('Jane', $found->getName());
        self::assertSame('Hello Jane!', $found->getMessage());
        self::assertEquals(new \DateTimeImmutable('2026-01-01 10:00:00'), $found->getCreatedAt());
    }

    private function getEntityManager(): <?= $imports->name('Doctrine\ORM\EntityManagerInterface') ?>

    {
        self::bootKernel();

        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(<?= $imports->name('Doctrine\ORM\EntityManagerInterface') ?>::class, $entityManager);

        return $entityManager;
    }

    /**
     * A container with the parameters the kernel defines before prepending the configuration of the bundles.
     */
    private function createContainerBuilder(): <?= $imports->name('Symfony\Component\DependencyInjection\ContainerBuilder') ?>

    {
        return new <?= $imports->name('Symfony\Component\DependencyInjection\ContainerBuilder') ?>(new <?= $imports->name('Symfony\Component\DependencyInjection\ParameterBag\ParameterBag') ?>([
            'kernel.environment' => 'test',
            'kernel.build_dir' => sys_get_temp_dir(),
            'kernel.bundles_metadata' => [],
        ]));
    }

    private function prepend(<?= $bundle ?> $bundle, <?= $imports->name('Symfony\Component\DependencyInjection\ContainerBuilder') ?> $container): void
    {
        $extension = $bundle->getContainerExtension();
        self::assertInstanceOf(<?= $imports->name('Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface') ?>::class, $extension);

        $extension->prepend($container);
    }
}
