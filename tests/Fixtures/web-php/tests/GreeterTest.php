<?php

declare(strict_types=1);

namespace Acme\WebPhpBundle\Tests;

use Acme\WebPhpBundle\Exception\ExceptionInterface;
use Acme\WebPhpBundle\Exception\InvalidArgumentException;
use Acme\WebPhpBundle\Greeter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class GreeterTest extends TestCase
{
    public function testGreetsTheGivenName(): void
    {
        self::assertSame('Hello Jane!', (new Greeter('Hello'))->greet('Jane'));
    }

    public function testTrimsTheName(): void
    {
        self::assertSame('Hi Jane!', (new Greeter('Hi'))->greet("  Jane\n"));
    }

    #[DataProvider('provideEmptyNames')]
    public function testRejectsAnEmptyName(string $name): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The name to greet must not be empty.');

        (new Greeter('Hello'))->greet($name);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideEmptyNames(): iterable
    {
        yield 'empty' => [''];
        yield 'whitespace only' => [" \t\n"];
    }

    public function testExceptionsImplementTheExceptionInterfaceOfTheBundle(): void
    {
        $this->expectException(ExceptionInterface::class);

        (new Greeter('Hello'))->greet('');
    }
}
