<?php

declare(strict_types=1);

/*
 * This file is part of the yceruto/bundle-flex package.
 *
 * (c) Yonel Ceruto <yonelceruto@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Yceruto\BundleFlex\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\FileTemplate;
use Yceruto\BundleFlex\Blueprint\Partial;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Feature\TranslationsFeature;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class TranslationsFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new TranslationsFeature();

        self::assertSame('translations', $feature->getName());
        self::assertSame(FeatureGroup::Features, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
        self::assertFalse($feature->isEnabledByDefault(new FeatureSelection()));
    }

    public function testContributesTheMessagesInTheDomainOfTheBundle(): void
    {
        $blueprint = $this->createBlueprint(twig: false, ux: false);

        (new TranslationsFeature())->contribute($blueprint);

        self::assertEquals([
            'translations/AcmeBlog.en.xlf' => new FileTemplate('features/translations/translations/messages.en.xlf.tpl', 'translations/AcmeBlog.en.xlf', ['messages' => ['Welcome!']]),
            'tests/Integration/TranslationsTest.php' => new FileTemplate('features/translations/tests/Integration/TranslationsTest.php.tpl', 'tests/Integration/TranslationsTest.php', ['messages' => ['Welcome!']]),
        ], $blueprint->getFiles());
        self::assertSame([], $blueprint->composer->getRequire());
        self::assertSame(['symfony/translation' => '^7.4 || ^8.0'], $blueprint->composer->getRequireDev());
        self::assertEquals([new Partial('features/translations/partials/test_kernel.php.tpl')], $blueprint->slots->getPartials(Slot::TestKernelConfigureContainer));
        self::assertEquals([new Partial('features/translations/partials/docs.md.tpl')], $blueprint->slots->getPartials(Slot::DocsSections));
    }

    public function testTheCloseButtonOfTheTemplateIsTranslated(): void
    {
        self::assertSame(['Welcome!'], TranslationsFeature::getMessages($this->createBlueprint(twig: true, ux: false)));
        self::assertSame(['Welcome!'], TranslationsFeature::getMessages($this->createBlueprint(twig: false, ux: true)));
        self::assertSame(['Welcome!', 'Close'], TranslationsFeature::getMessages($this->createBlueprint(twig: true, ux: true)));
    }

    public function testDerivesTheFileName(): void
    {
        self::assertSame('translations/MyVendorFooBar.fr.xlf', TranslationsFeature::fileName(OptionsFactory::create('my-vendor/foo-bar-bundle')->naming, 'fr'));
    }

    private function createBlueprint(bool $twig, bool $ux): BundleBlueprint
    {
        return new BundleBlueprint(OptionsFactory::create(features: ['twig' => $twig, 'translations' => true, 'ux' => $ux]));
    }
}
