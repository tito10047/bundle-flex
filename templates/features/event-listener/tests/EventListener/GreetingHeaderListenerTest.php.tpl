<?php
/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->namespace.'\EventListener\GreetingHeaderListener',
    $naming->namespace.'\Greeter',
    'PHPUnit\Framework\TestCase',
    'Symfony\Component\HttpFoundation\Request',
    'Symfony\Component\HttpFoundation\Response',
    'Symfony\Component\HttpKernel\Event\ResponseEvent',
    'Symfony\Component\HttpKernel\HttpKernelInterface',
], $naming->testsNamespace.'\EventListener');
$listener = $imports->name($naming->namespace.'\EventListener\GreetingHeaderListener');
$response = $imports->name('Symfony\Component\HttpFoundation\Response');
$kernel = $imports->name('Symfony\Component\HttpKernel\HttpKernelInterface');
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\EventListener;

<?= $imports->render() ?>

final class GreetingHeaderListenerTest extends <?= $imports->name('PHPUnit\Framework\TestCase') ?>

{
    public function testAddsTheGreetingHeaderToTheMainResponse(): void
    {
        $response = $this->dispatch(<?= $kernel ?>::MAIN_REQUEST, new <?= $response ?>());

        self::assertSame('Hi World!', $response->headers->get(<?= $listener ?>::HEADER));
    }

    public function testIgnoresTheResponsesOfSubRequests(): void
    {
        $response = $this->dispatch(<?= $kernel ?>::SUB_REQUEST, new <?= $response ?>());

        self::assertFalse($response->headers->has(<?= $listener ?>::HEADER));
    }

    public function testKeepsTheHeaderSetByTheApplication(): void
    {
        $response = $this->dispatch(<?= $kernel ?>::MAIN_REQUEST, new <?= $response ?>(headers: [<?= $listener ?>::HEADER => 'Welcome!']));

        self::assertSame('Welcome!', $response->headers->get(<?= $listener ?>::HEADER));
    }

    /**
     * Calls the listener with the response of a request of the given type, then returns the response.
     *
     * @param <?= $kernel ?>::*_REQUEST $requestType
     */
    private function dispatch(int $requestType, <?= $response ?> $response): <?= $response ?>

    {
        $listener = new <?= $listener ?>(new <?= $imports->name($naming->namespace.'\Greeter') ?>('Hi'));

        $listener->onKernelResponse(new <?= $imports->name('Symfony\Component\HttpKernel\Event\ResponseEvent') ?>(self::createStub(<?= $kernel ?>::class), <?= $imports->name('Symfony\Component\HttpFoundation\Request') ?>::create('/'), $requestType, $response));

        return $response;
    }
}
