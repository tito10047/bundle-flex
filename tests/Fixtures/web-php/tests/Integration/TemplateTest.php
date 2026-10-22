<?php

declare(strict_types=1);

namespace Acme\WebPhpBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Asset\Packages;
use Twig\Environment;

final class TemplateTest extends KernelTestCase
{
    public function testTemplateIsRegisteredInTheNamespaceOfTheBundle(): void
    {
        self::assertTrue($this->getTwig()->getLoader()->exists('@AcmeWebPhp/hello.html.twig'));
    }

    public function testRendersTheGreeting(): void
    {
        $html = $this->getTwig()->render('@AcmeWebPhp/hello.html.twig', ['greeting' => 'Hello Jane!']);

        self::assertStringContainsString('Hello Jane!', $html);
        self::assertStringContainsString('Welcome!', $html);
        // the URL depends on the configuration of the application, e.g. versioned with AssetMapper
        $packages = self::getContainer()->get('assets.packages');
        self::assertInstanceOf(Packages::class, $packages);
        self::assertStringContainsString('href="'.$packages->getUrl('bundles/acmewebphp/css/acme-web-php.css').'"', $html);
        self::assertStringContainsString('data-controller="acme--web-php-bundle--hello"', $html);
        self::assertStringContainsString('data-action="acme--web-php-bundle--hello#close"', $html);
    }

    public function testEscapesTheGreeting(): void
    {
        $html = $this->getTwig()->render('@AcmeWebPhp/hello.html.twig', ['greeting' => 'Hello <b>Jane</b>!']);

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
