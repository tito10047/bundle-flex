<?php

declare(strict_types=1);

namespace Acme\TranslationsBundle\Tests\Integration;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\Util\XliffUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

final class TranslationsTest extends KernelTestCase
{
    private const DOMAIN = 'AcmeTranslations';

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
        yield 'Welcome!' => ['Welcome!'];
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
