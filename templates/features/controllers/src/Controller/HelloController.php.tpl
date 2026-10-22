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
    GreeterContributor::interfaceName($naming),
    'Symfony\Component\HttpFoundation\Response',
    ...($twig ? ['Twig\Environment'] : []),
], $naming->namespace.'\Controller');
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->namespace ?>\Controller;

<?= $imports->render() ?>

/**
<?php if ($twig): ?>
 * Greets people on a page rendered from the "<?= TwigFeature::templateName($naming) ?>" template.
<?php else: ?>
 * Greets people, in plain text.
<?php endif ?>
 *
 * Registered as the "<?= ControllersFeature::serviceId($naming) ?>" service, and routed by the "<?= ControllersFeature::routeName($naming) ?>" route.
 */
final readonly class HelloController
{
    public function __construct(
        private <?= $imports->name(GreeterContributor::interfaceName($naming)) ?> $greeter,
<?php if ($twig): ?>
        private <?= $imports->name('Twig\Environment') ?> $twig,
<?php endif ?>
    ) {
    }

    public function __invoke(string $name): Response
    {
<?php if ($twig): ?>
        return new Response($this->twig->render(<?= $helper->php(TwigFeature::templateName($naming)) ?>, [
            'greeting' => $this->greeter->greet($name),
        ]));
<?php else: ?>
        return new Response($this->greeter->greet($name), Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
<?php endif ?>
    }
}
