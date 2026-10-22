<?php
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Feature\EventListenerFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->namespace.'\EventListener\GreetingHeaderListener',
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
    'Symfony\Component\EventDispatcher\EventDispatcherInterface',
    'Symfony\Component\HttpFoundation\Request',
    'Symfony\Component\HttpFoundation\Response',
    'Symfony\Component\HttpKernel\Event\ResponseEvent',
    'Symfony\Component\HttpKernel\HttpKernelInterface',
    'Symfony\Component\HttpKernel\KernelEvents',
], $naming->testsNamespace.'\Integration\EventListener');
$listener = $imports->name($naming->namespace.'\EventListener\GreetingHeaderListener');
$kernelEvents = $imports->name('Symfony\Component\HttpKernel\KernelEvents');
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\Integration\EventListener;

<?= $imports->render() ?>

final class GreetingHeaderListenerTest extends <?= $imports->name('Symfony\Bundle\FrameworkBundle\Test\KernelTestCase') ?>

{
    public function testListenerIsRegistered(): void
    {
        self::bootKernel();
        $dispatcher = $this->getEventDispatcher();

        $registered = [];
        foreach ($dispatcher->getListeners(<?= $kernelEvents ?>::RESPONSE) as $listener) {
            if (\is_array($listener) && $listener[0] instanceof <?= $listener ?>) {
                // Symfony 7.4 documents the listeners as "callable[]|callable": the callable is rebuilt for
                // the static analysis
                $registered[] = [$listener[1], $dispatcher->getListenerPriority(<?= $kernelEvents ?>::RESPONSE, [$listener[0], 'onKernelResponse'])];
            }
        }

        // the method and the priority of the "kernel.event_listener" tag
        self::assertSame([['onKernelResponse', <?= EventListenerFeature::PRIORITY ?>]], $registered);
    }

    public function testResponsesGetTheGreetingHeader(): void
    {
        $kernel = self::bootKernel();
        $response = new <?= $imports->name('Symfony\Component\HttpFoundation\Response') ?>();

        $this->getEventDispatcher()->dispatch(
            new <?= $imports->name('Symfony\Component\HttpKernel\Event\ResponseEvent') ?>($kernel, <?= $imports->name('Symfony\Component\HttpFoundation\Request') ?>::create('/'), <?= $imports->name('Symfony\Component\HttpKernel\HttpKernelInterface') ?>::MAIN_REQUEST, $response),
            <?= $kernelEvents ?>::RESPONSE,
        );

        self::assertSame('<?= GreeterContributor::DEFAULT_GREETING ?> World!', $response->headers->get(<?= $listener ?>::HEADER));
    }

    private function getEventDispatcher(): <?= $imports->name('Symfony\Component\EventDispatcher\EventDispatcherInterface') ?>

    {
        $dispatcher = self::getContainer()->get('event_dispatcher');
        self::assertInstanceOf(<?= $imports->name('Symfony\Component\EventDispatcher\EventDispatcherInterface') ?>::class, $dispatcher);

        return $dispatcher;
    }
}
