<?php

declare(strict_types=1);

namespace Acme\BackendPhpBundle\Tests\Integration\Doctrine;

use Acme\BackendPhpBundle\AcmeBackendPhpBundle;
use Acme\BackendPhpBundle\Entity\Greeting;
use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class GreetingMappingTest extends KernelTestCase
{
    public function testMappingIsPrependedWhenDoctrineBundleIsEnabled(): void
    {
        $bundle = new AcmeBackendPhpBundle();
        $doctrineExtension = (new DoctrineBundle())->getContainerExtension();
        self::assertInstanceOf(ExtensionInterface::class, $doctrineExtension);

        $container = $this->createContainerBuilder();
        $container->registerExtension($doctrineExtension);
        $this->prepend($bundle, $container);

        self::assertSame([[
            'orm' => [
                'mappings' => [
                    'AcmeBackendPhpBundle' => [
                        'type' => 'xml',
                        'dir' => $bundle->getPath().'/config/doctrine',
                        'prefix' => 'Acme\\BackendPhpBundle\\Entity',
                        'is_bundle' => false,
                    ],
                ],
            ],
        ]], $container->getExtensionConfig('doctrine'));
    }

    public function testNothingIsPrependedWithoutDoctrineBundle(): void
    {
        $container = $this->createContainerBuilder();
        $this->prepend(new AcmeBackendPhpBundle(), $container);

        self::assertSame([], $container->getExtensionConfig('doctrine'));
    }

    /**
     * The table and column names come from config/doctrine/Greeting.orm.xml, validated against the
     * XML schema of Doctrine ORM by the test application.
     */
    public function testEntityIsMappedWithTheXmlFile(): void
    {
        $metadata = $this->getEntityManager()->getClassMetadata(Greeting::class);
        self::assertSame('acme_backend_php_greeting', $metadata->getTableName());
        self::assertSame(['id'], $metadata->getIdentifierFieldNames());
        self::assertEqualsCanonicalizing(['id', 'name', 'message', 'createdAt'], $metadata->getFieldNames());
        self::assertSame('created_at', $metadata->getColumnName('createdAt'));
    }

    public function testGreetingIsPersisted(): void
    {
        $entityManager = $this->getEntityManager();
        (new SchemaTool($entityManager))->createSchema([$entityManager->getClassMetadata(Greeting::class)]);

        $greeting = new Greeting('Jane', 'Hello Jane!', new \DateTimeImmutable('2026-01-01 10:00:00'));
        $entityManager->persist($greeting);
        $entityManager->flush();
        $entityManager->clear();

        $id = $greeting->getId();
        self::assertNotNull($id);

        $found = $entityManager->find(Greeting::class, $id);
        self::assertInstanceOf(Greeting::class, $found);
        self::assertNotSame($greeting, $found);
        self::assertSame('Jane', $found->getName());
        self::assertSame('Hello Jane!', $found->getMessage());
        self::assertEquals(new \DateTimeImmutable('2026-01-01 10:00:00'), $found->getCreatedAt());
    }

    private function getEntityManager(): EntityManagerInterface
    {
        self::bootKernel();

        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

        return $entityManager;
    }

    /**
     * A container with the parameters the kernel defines before prepending the configuration of the bundles.
     */
    private function createContainerBuilder(): ContainerBuilder
    {
        return new ContainerBuilder(new ParameterBag([
            'kernel.environment' => 'test',
            'kernel.build_dir' => sys_get_temp_dir(),
            'kernel.bundles_metadata' => [],
        ]));
    }

    private function prepend(AcmeBackendPhpBundle $bundle, ContainerBuilder $container): void
    {
        $extension = $bundle->getContainerExtension();
        self::assertInstanceOf(PrependExtensionInterface::class, $extension);

        $extension->prepend($container);
    }
}
