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
 * Web assets served as is: a stylesheet in public/, e.g. public/css/acme-blog.css.
 *
 * "assets:install" copies (or links) the public/ directory of every bundle to the
 * public/bundles/<shortname>/ directory of the application, e.g. public/bundles/acmeblog/;
 * AssetMapper serves it under the same path. The template of the "twig" feature links the
 * stylesheet with the asset() function of the Asset component.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#directory-structure
 */
final class PublicAssetsFeature implements FeatureInterface
{
    public const NAME = 'public-assets';

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
        return 'Add public web assets (public/, installed with assets:install)?';
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return false;
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        if ($blueprint->hasFeature(TwigFeature::NAME)) {
            // the asset() function of the template
            TwigFeature::requireForTemplates($blueprint, 'symfony/asset', Constraint::SYMFONY);
        }
        // the test installs the assets with the "assets:install" command
        $blueprint->composer->requireDev('symfony/console', Constraint::SYMFONY);

        $blueprint->addFile('features/public-assets/public/css/stylesheet.css.tpl', 'public/'.self::stylesheetPath($blueprint->naming));
        $blueprint->addFile('features/public-assets/tests/Integration/PublicAssetsTest.php.tpl', 'tests/Integration/PublicAssetsTest.php');

        $blueprint->slots->addPartial(Slot::ReadmeInstallationSteps, 'features/public-assets/partials/readme_step.md.tpl');
        $blueprint->slots->addPartial(Slot::DocsSections, 'features/public-assets/partials/docs.md.tpl');
    }

    /**
     * Path of the stylesheet in the public/ directory, e.g. "css/acme-blog.css".
     */
    public static function stylesheetPath(BundleNaming $naming): string
    {
        return 'css/'.$naming->slug.'.css';
    }

    /**
     * Public path of the stylesheet in the application, e.g. "bundles/acmeblog/css/acme-blog.css".
     */
    public static function stylesheetPublicPath(BundleNaming $naming): string
    {
        return $naming->publicAssetsDirectory.'/'.self::stylesheetPath($naming);
    }
}
