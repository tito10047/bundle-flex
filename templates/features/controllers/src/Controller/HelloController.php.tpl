<?php
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Feature\ControllersFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    GreeterContributor::interfaceName($naming),
    'Symfony\Component\HttpFoundation\Response',
], $naming->namespace.'\Controller');
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->namespace ?>\Controller;

<?= $imports->render() ?>

/**
 * Greets people, in plain text.
 *
 * Registered as the "<?= ControllersFeature::serviceId($naming) ?>" service, and routed by the "<?= ControllersFeature::routeName($naming) ?>" route.
 */
final readonly class HelloController
{
    public function __construct(
        private <?= $imports->name(GreeterContributor::interfaceName($naming)) ?> $greeter,
    ) {
    }

    public function __invoke(string $name): Response
    {
        return new Response($this->greeter->greet($name), Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
