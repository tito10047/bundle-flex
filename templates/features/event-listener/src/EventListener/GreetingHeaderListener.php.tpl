<?php
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Feature\EventListenerFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    GreeterContributor::interfaceName($naming),
    'Symfony\Component\HttpKernel\Event\ResponseEvent',
], $naming->namespace.'\EventListener');
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->namespace ?>\EventListener;

<?= $imports->render() ?>

/**
 * Adds a greeting header to the responses of the application, e.g. "<?= EventListenerFeature::headerName($naming) ?>: <?= GreeterContributor::DEFAULT_GREETING ?> World!".
 *
 * Registered as the "<?= EventListenerFeature::serviceId($naming) ?>" service, listening to the "<?= EventListenerFeature::EVENT ?>" event.
 */
final readonly class GreetingHeaderListener
{
    public const HEADER = <?= $helper->php(EventListenerFeature::headerName($naming)) ?>;

    public function __construct(
        private <?= $imports->name(GreeterContributor::interfaceName($naming)) ?> $greeter,
    ) {
    }

    public function onKernelResponse(<?= $imports->name('Symfony\Component\HttpKernel\Event\ResponseEvent') ?> $event): void
    {
        // the responses of sub-requests (fragments, ESI) are part of the response of the main request
        if (!$event->isMainRequest()) {
            return;
        }

        $headers = $event->getResponse()->headers;

        // a header set by the application takes precedence
        if ($headers->has(self::HEADER)) {
            return;
        }

        $headers->set(self::HEADER, $this->greeter->greet('World'));
    }
}
