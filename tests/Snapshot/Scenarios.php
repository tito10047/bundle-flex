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

namespace Yceruto\BundleFlex\Tests\Snapshot;

use Yceruto\BundleFlex\Contributor\CoreContributors;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureRegistry;
use Yceruto\BundleFlex\Generator\BundleGenerator;
use Yceruto\BundleFlex\Options\ConfigFormat;
use Yceruto\BundleFlex\Template\TemplateRenderer;
use Yceruto\BundleFlex\Tests\Support\KitchenSinkFeature;

/**
 * The snapshot scenarios. Add a scenario here, then run "UPDATE_SNAPSHOTS=1 vendor/bin/phpunit"
 * and review the generated fixtures before committing them.
 */
final class Scenarios
{
    /** Year of the copyright notices, fixed so the snapshots do not change every year. */
    public const YEAR = 2026;

    /**
     * @return array<string, Scenario>
     */
    public static function all(): array
    {
        $scenarios = [
            // nothing but the core files, in each configuration format
            new Scenario('minimal', 'acme/minimal-bundle'),
            new Scenario('minimal-php-array', 'acme/minimal-php-array-bundle', ConfigFormat::PhpArray),
            new Scenario('minimal-yaml', 'acme/minimal-yaml-bundle', ConfigFormat::Yaml),
            // every contribution point, in each configuration format
            new Scenario('kitchen-sink-php', 'acme/php-kitchen-sink-bundle', ConfigFormat::Php, ['config', 'kitchen-sink']),
            new Scenario('kitchen-sink-php-array', 'acme/php-array-kitchen-sink-bundle', ConfigFormat::PhpArray, ['config', 'kitchen-sink']),
            new Scenario('kitchen-sink-yaml', 'my-vendor/yaml-kitchen-sink-bundle', ConfigFormat::Yaml, ['config', 'kitchen-sink']),
        ];

        return array_combine(array_map(static fn (Scenario $scenario): string => $scenario->name, $scenarios), $scenarios);
    }

    /**
     * The built-in features plus the test-only kitchen-sink feature (last of its group).
     */
    public static function createFeatureRegistry(): FeatureRegistry
    {
        $features = [];
        foreach (FeatureGroup::cases() as $group) {
            array_push($features, ...FeatureRegistry::createDefault()->getByGroup($group));
            if (FeatureGroup::Features === $group) {
                $features[] = new KitchenSinkFeature();
            }
        }

        return new FeatureRegistry(...$features);
    }

    public static function createGenerator(FeatureRegistry $features): BundleGenerator
    {
        return new BundleGenerator(CoreContributors::createDefault(self::YEAR), $features, new TemplateRenderer([\dirname(__DIR__, 2).'/templates', KitchenSinkFeature::TEMPLATE_DIR]));
    }
}
