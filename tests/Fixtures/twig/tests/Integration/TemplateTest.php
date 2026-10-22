<?php

declare(strict_types=1);

namespace Acme\TwigBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class TemplateTest extends KernelTestCase
{
    public function testTemplateIsRegisteredInTheNamespaceOfTheBundle(): void
    {
        self::assertTrue($this->getTwig()->getLoader()->exists('@AcmeTwig/hello.html.twig'));
    }

    public function testRendersTheGreeting(): void
    {
        $html = $this->getTwig()->render('@AcmeTwig/hello.html.twig', ['greeting' => 'Hello Jane!']);

        self::assertStringContainsString('Hello Jane!', $html);
    }

    public function testEscapesTheGreeting(): void
    {
        $html = $this->getTwig()->render('@AcmeTwig/hello.html.twig', ['greeting' => 'Hello <b>Jane</b>!']);

        self::assertStringContainsString('Hello &lt;b&gt;Jane&lt;/b&gt;!', $html);
        self::assertStringNotContainsString('<b>', $html);
    }

    private function getTwig(): Environment
    {
        $twig = self::getContainer()->get('twig');
        self::assertInstanceOf(Environment::class, $twig);

        return $twig;
    }
}
