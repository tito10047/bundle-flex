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
use Yceruto\BundleFlex\Blueprint\Slot;

/**
 * Semantic configuration: the configuration tree lives in config/definition.php
 * and is imported by AcmeBlogBundle::configure(). AcmeBlogBundle::loadExtension()
 * passes the processed configuration to the services, through container parameters.
 *
 * The tree configures the greeter of the core domain example (see GreeterContributor).
 *
 * @see https://symfony.com/doc/current/bundles/configuration.html
 */
final class SemanticConfigFeature implements FeatureInterface
{
    public const NAME = 'config';

    /** The configuration comes first in README.md and docs/index.md, before the usage sections. */
    public const DOCS_PRIORITY = 10;

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
        return 'Add a semantic configuration (config/definition.php)?';
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return true;
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        $blueprint->addFile('features/config/definition.php.tpl', 'config/definition.php');
        $blueprint->addFile('features/config/tests/Integration/ConfigurationTest.php.tpl', 'tests/Integration/ConfigurationTest.php');

        $blueprint->slots->addPartial(Slot::BundleConfigure, 'features/config/partials/bundle_configure.php.tpl');
        $blueprint->slots->addPartial(Slot::BundleLoadExtension, 'features/config/partials/bundle_load_extension.php.tpl');
        $blueprint->slots->addPartial(Slot::ReadmeSections, 'features/config/partials/readme.md.tpl', priority: self::DOCS_PRIORITY);
        $blueprint->slots->addPartial(Slot::DocsSections, 'features/config/partials/docs.md.tpl', priority: self::DOCS_PRIORITY);
    }
}
