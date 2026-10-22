<?php
use Yceruto\BundleFlex\Feature\PublicAssetsFeature;
use Yceruto\BundleFlex\Feature\TranslationsFeature;
use Yceruto\BundleFlex\Feature\TwigFeature;
use Yceruto\BundleFlex\Feature\UxFeature;

/**
 * @var Yceruto\BundleFlex\Blueprint\BundleBlueprint $blueprint
 * @var Yceruto\BundleFlex\Naming\BundleNaming       $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper   $helper
 */
$imports = $helper->imports([
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
    'Twig\Environment',
    ...($blueprint->hasFeature(PublicAssetsFeature::NAME) ? ['Symfony\Component\Asset\Packages'] : []),
], $naming->testsNamespace.'\Integration');
$template = TwigFeature::templateName($naming);
$ux = $blueprint->hasFeature(UxFeature::NAME);
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\Integration;

<?= $imports->render() ?>

final class TemplateTest extends KernelTestCase
{
    public function testTemplateIsRegisteredInTheNamespaceOfTheBundle(): void
    {
        self::assertTrue($this->getTwig()->getLoader()->exists(<?= $helper->php($template) ?>));
    }

    public function testRendersTheGreeting(): void
    {
        $html = $this->getTwig()->render(<?= $helper->php($template) ?>, ['greeting' => 'Hello Jane!']);

        self::assertStringContainsString('Hello Jane!', $html);
<?php if ($blueprint->hasFeature(TranslationsFeature::NAME)): ?>
        self::assertStringContainsString(<?= $helper->php(TranslationsFeature::TITLE_MESSAGE) ?>, $html);
<?php endif ?>
<?php if ($blueprint->hasFeature(PublicAssetsFeature::NAME)): ?>
        // the URL depends on the configuration of the application, e.g. versioned with AssetMapper
        $packages = self::getContainer()->get('assets.packages');
        self::assertInstanceOf(Packages::class, $packages);
        self::assertStringContainsString('href="'.$packages->getUrl(<?= $helper->php(PublicAssetsFeature::stylesheetPublicPath($naming)) ?>).'"', $html);
<?php endif ?>
<?php if ($ux): ?>
        self::assertStringContainsString(<?= $helper->php('data-controller="'.UxFeature::controllerIdentifier($naming).'"') ?>, $html);
        self::assertStringContainsString(<?= $helper->php('data-action="'.UxFeature::controllerIdentifier($naming).'#close"') ?>, $html);
<?php endif ?>
    }

    public function testEscapesTheGreeting(): void
    {
        $html = $this->getTwig()->render(<?= $helper->php($template) ?>, ['greeting' => 'Hello <b>Jane</b>!']);

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
