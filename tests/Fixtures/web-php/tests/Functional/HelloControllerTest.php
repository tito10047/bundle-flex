<?php

declare(strict_types=1);

namespace Acme\WebPhpBundle\Tests\Functional;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Requests the pages of the bundle like a browser does, and only checks the responses.
 */
final class HelloControllerTest extends WebTestCase
{
    public function testGreetsTheNameOfTheUrl(): void
    {
        $client = self::createClient();

        $client->request('GET', '/hello/Jane');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('Content-Type', 'text/html; charset=UTF-8');
        self::assertStringContainsString('Hello Jane!', (string) $client->getResponse()->getContent());
    }

    public function testGreetsTheWorldByDefault(): void
    {
        $client = self::createClient();

        $client->request('GET', '/hello');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('Hello World!', (string) $client->getResponse()->getContent());
    }

    public function testRouteCanBeGenerated(): void
    {
        $client = self::createClient();
        $urlGenerator = self::getContainer()->get('router');
        self::assertInstanceOf(UrlGeneratorInterface::class, $urlGenerator);

        $client->request('GET', $urlGenerator->generate('acme_web_php_hello', ['name' => 'Jane']));

        self::assertResponseIsSuccessful();
        self::assertRouteSame('acme_web_php_hello', ['name' => 'Jane']);
    }

    #[DataProvider('provideInvalidUrls')]
    public function testOnlyAcceptsAWordAsName(string $url): void
    {
        $client = self::createClient();

        $client->request('GET', $url);

        self::assertResponseStatusCodeSame(404);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideInvalidUrls(): iterable
    {
        yield 'several words' => ['/hello/Jane%20Doe'];
        yield 'not a word' => ['/hello/-'];
    }

    public function testOnlyAcceptsGetRequests(): void
    {
        $client = self::createClient();

        $client->request('POST', '/hello/Jane');

        self::assertResponseStatusCodeSame(405);
    }
}
