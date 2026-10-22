<?php

declare(strict_types=1);

namespace Acme\BackendYamlBundle\Tests\Command;

use Acme\BackendYamlBundle\Command\HelloCommand;
use Acme\BackendYamlBundle\Greeter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

final class HelloCommandTest extends TestCase
{
    public function testIsDescribedByItsAttribute(): void
    {
        $command = $this->createCommand();

        self::assertSame('acme-backend-yaml:hello', $command->getName());
        self::assertSame('Greets someone', $command->getDescription());
        self::assertTrue($command->getDefinition()->getArgument('name')->isRequired());
        self::assertSame('y', $command->getDefinition()->getOption('yell')->getShortcut());
    }

    public function testGreetsTheGivenName(): void
    {
        $tester = new CommandTester($this->createCommand());

        $tester->execute(['name' => 'Jane']);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('[OK] Hello Jane!', $tester->getDisplay());
    }

    public function testYellsTheGreeting(): void
    {
        $tester = new CommandTester($this->createCommand());

        $tester->execute(['name' => 'Jane', '--yell' => true]);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('[OK] HELLO JANE!', $tester->getDisplay());
    }

    public function testRejectsAnEmptyName(): void
    {
        $tester = new CommandTester($this->createCommand());

        $tester->execute(['name' => ' ']);

        self::assertSame(Command::INVALID, $tester->getStatusCode());
        self::assertStringContainsString('[ERROR] The name to greet must not be empty.', $tester->getDisplay());
    }

    private function createCommand(): Command
    {
        return new Command(null, new HelloCommand(new Greeter('Hello')));
    }
}
