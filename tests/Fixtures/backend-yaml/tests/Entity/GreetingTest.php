<?php

declare(strict_types=1);

namespace Acme\BackendYamlBundle\Tests\Entity;

use Acme\BackendYamlBundle\Entity\Greeting;
use PHPUnit\Framework\TestCase;

final class GreetingTest extends TestCase
{
    public function testHoldsTheGreeting(): void
    {
        $createdAt = new \DateTimeImmutable('2026-01-01 10:00:00');
        $greeting = new Greeting('Jane', 'Hello Jane!', $createdAt);

        self::assertNull($greeting->getId());
        self::assertSame('Jane', $greeting->getName());
        self::assertSame('Hello Jane!', $greeting->getMessage());
        self::assertSame($createdAt, $greeting->getCreatedAt());
    }

    public function testIsCreatedNowByDefault(): void
    {
        $before = new \DateTimeImmutable();
        $greeting = new Greeting('Jane', 'Hello Jane!');
        $after = new \DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $greeting->getCreatedAt());
        self::assertLessThanOrEqual($after, $greeting->getCreatedAt());
    }
}
