<?php
/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 * @var list<string>                               $messages
 */
$imports = $helper->imports([
    'PHPUnit\Framework\Attributes\DataProvider',
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
    'Symfony\Component\Translation\TranslatorBagInterface',
    'Symfony\Component\Translation\Util\XliffUtils',
    'Symfony\Contracts\Translation\TranslatorInterface',
], $naming->testsNamespace.'\Integration');
$domain = $naming->translationDomain;
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\Integration;

<?= $imports->render() ?>

final class TranslationsTest extends KernelTestCase
{
    private const DOMAIN = <?= $helper->php($domain) ?>;

    #[DataProvider('provideMessages')]
    public function testMessagesAreLoadedInTheDomainOfTheBundle(string $message): void
    {
        self::bootKernel();
        $translator = self::getContainer()->get('translator');
        self::assertInstanceOf(TranslatorBagInterface::class, $translator);
        self::assertInstanceOf(TranslatorInterface::class, $translator);

        self::assertTrue($translator->getCatalogue('en')->defines($message, self::DOMAIN));
        self::assertSame($message, $translator->trans($message, [], self::DOMAIN, 'en'));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideMessages(): iterable
    {
<?php foreach ($messages as $message): ?>
        yield <?= $helper->php($message) ?> => [<?= $helper->php($message) ?>];
<?php endforeach ?>
    }

    public function testTranslationFilesAreValidXliff(): void
    {
        $files = glob(\dirname(__DIR__, 2).'/translations/'.self::DOMAIN.'.*.xlf');
        self::assertNotEmpty($files);

        foreach ($files as $file) {
            $document = new \DOMDocument();
            self::assertTrue($document->load($file), $file);
            self::assertSame([], XliffUtils::validateSchema($document), $file);
        }
    }
}
