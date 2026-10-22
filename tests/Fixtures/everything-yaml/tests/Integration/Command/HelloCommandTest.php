<?php

declare(strict_types=1);

namespace MyVendor\EverythingYamlBundle\Tests\Integration\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Tester\CommandTester;

final class HelloCommandTest extends KernelTestCase
{
    public function testCommandIsRegisteredInTheApplication(): void
    {
        $application = new Application(self::bootKernel());
        $tester = new CommandTester($application->find('my-vendor-everything-yaml:hello'));

        $tester->execute(['name' => 'Jane']);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('Hello Jane!', $tester->getDisplay());
    }

    public function testCommandIsLazyLoaded(): void
    {
        self::bootKernel();

        $commandLoader = self::getContainer()->get('console.command_loader');

        self::assertInstanceOf(CommandLoaderInterface::class, $commandLoader);
        self::assertTrue($commandLoader->has('my-vendor-everything-yaml:hello'));
    }
}
