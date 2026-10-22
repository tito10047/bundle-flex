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
 * and is imported by AcmeBlogBundle::configure().
 *
 * @see https://symfony.com/doc/current/bundles/configuration.html
 */
final class SemanticConfigFeature implements FeatureInterface
{
    public const NAME = 'config';

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
        $blueprint->slots->addPartial(Slot::BundleConfigure, 'features/config/partials/bundle_configure.php.tpl');
    }
}
