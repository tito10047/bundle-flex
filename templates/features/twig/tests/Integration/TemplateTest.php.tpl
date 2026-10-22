<?php
use Yceruto\BundleFlex\Feature\TwigFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
    'Twig\Environment',
], $naming->testsNamespace.'\Integration');
$template = TwigFeature::templateName($naming);
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
