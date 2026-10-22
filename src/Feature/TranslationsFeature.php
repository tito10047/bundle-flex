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

namespace Yceruto\BundleFlex\Feature;

use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\Constraint;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Naming\BundleNaming;

/**
 * The messages of the bundle, in English, in its own translation domain:
 * translations/<ShortName>.en.xlf, e.g. translations/AcmeBlog.en.xlf. FrameworkBundle loads the
 * translations/ directory of every bundle, and applications override or translate the messages
 * with their own files of the same domain.
 *
 * Like the messages of Symfony itself, the ids are the English messages: they are readable even
 * when the application has no translator (the identity translator, or the Twig "trans" filter
 * without the Translation component, returns them as is). The template of the "twig" feature
 * uses them.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#translation-files
 */
final class TranslationsFeature implements FeatureInterface
{
    public const NAME = 'translations';

    /** Title of the template of the "twig" feature. */
    public const TITLE_MESSAGE = 'Welcome!';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getGroup(): FeatureGroup
    {
        return FeatureGroup::Features;
    }

    public function getQuestion(): string
    {
        return 'Add translations (translations/, XLIFF)?';
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return false;
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        // the tests check the messages with the real translator
        $blueprint->composer->requireDev('symfony/translation', Constraint::SYMFONY);

        $blueprint->addFile('features/translations/translations/messages.en.xlf.tpl', self::fileName($blueprint->naming, 'en'), [
            'messages' => self::getMessages($blueprint),
        ]);
        $blueprint->addFile('features/translations/tests/Integration/TranslationsTest.php.tpl', 'tests/Integration/TranslationsTest.php', [
            'messages' => self::getMessages($blueprint),
        ]);

        $blueprint->slots->addPartial(Slot::TestKernelConfigureContainer, 'features/translations/partials/test_kernel.php.tpl');
        $blueprint->slots->addPartial(Slot::DocsSections, 'features/translations/partials/docs.md.tpl');
    }

    /**
     * The messages of the bundle, which are also their ids.
     *
     * @return non-empty-list<string>
     */
    public static function getMessages(BundleBlueprint $blueprint): array
    {
        return [self::TITLE_MESSAGE];
    }

    /**
     * Path of the translation file of a locale, e.g. "translations/AcmeBlog.en.xlf".
     */
    public static function fileName(BundleNaming $naming, string $locale): string
    {
        return 'translations/'.$naming->translationDomain.'.'.$locale.'.xlf';
    }
}
