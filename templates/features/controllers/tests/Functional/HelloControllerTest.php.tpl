<?php
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Feature\ControllersFeature;
use Yceruto\BundleFlex\Feature\TwigFeature;

/**
 * @var Yceruto\BundleFlex\Blueprint\BundleBlueprint $blueprint
 * @var Yceruto\BundleFlex\Naming\BundleNaming       $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper   $helper
 */
$twig = $blueprint->hasFeature(TwigFeature::NAME);
$imports = $helper->imports([
    'PHPUnit\Framework\Attributes\DataProvider',
    'Symfony\Bundle\FrameworkBundle\Test\WebTestCase',
    'Symfony\Component\Routing\Generator\UrlGeneratorInterface',
], $naming->testsNamespace.'\Functional');
$greeting = GreeterContributor::DEFAULT_GREETING;
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\Functional;

<?= $imports->render() ?>

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
<?php if ($twig): ?>
        self::assertResponseHeaderSame('Content-Type', 'text/html; charset=UTF-8');
        self::assertStringContainsString(<?= $helper->php($greeting.' Jane!') ?>, (string) $client->getResponse()->getContent());
<?php else: ?>
        self::assertResponseHeaderSame('Content-Type', 'text/plain; charset=UTF-8');
        self::assertSame(<?= $helper->php($greeting.' Jane!') ?>, $client->getResponse()->getContent());
<?php endif ?>
    }

    public function testGreetsTheWorldByDefault(): void
    {
        $client = self::createClient();

        $client->request('GET', '/hello');

        self::assertResponseIsSuccessful();
<?php if ($twig): ?>
        self::assertStringContainsString(<?= $helper->php($greeting.' '.ControllersFeature::DEFAULT_NAME.'!') ?>, (string) $client->getResponse()->getContent());
<?php else: ?>
        self::assertSame(<?= $helper->php($greeting.' '.ControllersFeature::DEFAULT_NAME.'!') ?>, $client->getResponse()->getContent());
<?php endif ?>
    }

    public function testRouteCanBeGenerated(): void
    {
        $client = self::createClient();
        $urlGenerator = self::getContainer()->get('router');
        self::assertInstanceOf(UrlGeneratorInterface::class, $urlGenerator);

        $client->request('GET', $urlGenerator->generate(<?= $helper->php(ControllersFeature::routeName($naming)) ?>, ['name' => 'Jane']));

        self::assertResponseIsSuccessful();
        self::assertRouteSame(<?= $helper->php(ControllersFeature::routeName($naming)) ?>, ['name' => 'Jane']);
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
