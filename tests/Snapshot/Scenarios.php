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
use Yceruto\BundleFlex\Options\License;
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

    private const QUALITY_ASSURANCE = ['phpstan', 'cs-fixer', 'rector', 'ci'];

    private const WEB = ['controllers', 'twig', 'translations', 'public-assets', 'ux'];

    private const BACKEND = ['command', 'event-listener', 'doctrine'];

    /**
     * @return array<string, Scenario>
     */
    public static function all(): array
    {
        // every built-in feature, like BUNDLE_FLEX_ALL=1 (the test-only kitchen-sink feature is not built in)
        $everything = array_keys(iterator_to_array(FeatureRegistry::createDefault()));

        $scenarios = [
            // the bundle generated with the default answers
            new Scenario('default', 'acme/blog-bundle', ConfigFormat::Php, ['config', ...self::QUALITY_ASSURANCE]),
            // nothing but the core files, in each configuration format
            new Scenario('minimal', 'acme/minimal-bundle'),
            new Scenario('minimal-php-array', 'acme/minimal-php-array-bundle', ConfigFormat::PhpArray),
            new Scenario('minimal-yaml', 'acme/minimal-yaml-bundle', ConfigFormat::Yaml, license: License::Proprietary),
            // the quality assurance features, alone and on top of the core files
            new Scenario('ci', 'acme/ci-bundle', features: ['ci']),
            new Scenario('quality-assurance', 'acme/quality-assurance-bundle', features: self::QUALITY_ASSURANCE),
            // the web features, each alone, then together in each configuration format
            new Scenario('controllers', 'acme/controllers-bundle', features: ['controllers']),
            new Scenario('twig', 'acme/twig-bundle', ConfigFormat::PhpArray, ['twig']),
            new Scenario('translations', 'acme/translations-bundle', ConfigFormat::Yaml, ['translations']),
            new Scenario('public-assets', 'acme/public-assets-bundle', features: ['public-assets']),
            new Scenario('ux', 'acme/ux-bundle', ConfigFormat::PhpArray, ['ux']),
            new Scenario('web-php', 'acme/web-php-bundle', ConfigFormat::Php, ['config', ...self::WEB, ...self::QUALITY_ASSURANCE]),
            new Scenario('web-php-array', 'acme/web-php-array-bundle', ConfigFormat::PhpArray, ['config', ...self::WEB, ...self::QUALITY_ASSURANCE]),
            new Scenario('web-yaml', 'my-vendor/web-yaml-bundle', ConfigFormat::Yaml, [...self::WEB, ...self::QUALITY_ASSURANCE], License::Proprietary),
            // the backend features, alone, then together with the quality assurance features in each configuration format
            new Scenario('command', 'acme/command-bundle', features: ['command']),
            new Scenario('event-listener', 'acme/event-listener-bundle', features: ['event-listener']),
            new Scenario('doctrine', 'acme/doctrine-entity-bundle', features: ['doctrine']),
            new Scenario('backend-php', 'acme/backend-php-bundle', ConfigFormat::Php, ['config', ...self::BACKEND, ...self::QUALITY_ASSURANCE]),
            new Scenario('backend-php-array', 'acme/backend-php-array-bundle', ConfigFormat::PhpArray, ['config', ...self::BACKEND, ...self::QUALITY_ASSURANCE]),
            new Scenario('backend-yaml', 'acme/backend-yaml-bundle', ConfigFormat::Yaml, ['config', ...self::BACKEND, ...self::QUALITY_ASSURANCE]),
            // every built-in feature together (BUNDLE_FLEX_ALL=1), in each configuration format
            new Scenario('everything-php', 'acme/everything-php-bundle', ConfigFormat::Php, $everything),
            new Scenario('everything-php-array', 'acme/everything-php-array-bundle', ConfigFormat::PhpArray, $everything),
            new Scenario('everything-yaml', 'my-vendor/everything-yaml-bundle', ConfigFormat::Yaml, $everything),
            // every contribution point, in each configuration format
            new Scenario('kitchen-sink-php', 'acme/php-kitchen-sink-bundle', ConfigFormat::Php, ['config', 'kitchen-sink', ...self::QUALITY_ASSURANCE]),
            new Scenario('kitchen-sink-php-array', 'acme/php-array-kitchen-sink-bundle', ConfigFormat::PhpArray, ['config', 'kitchen-sink', ...self::QUALITY_ASSURANCE]),
            new Scenario('kitchen-sink-yaml', 'my-vendor/yaml-kitchen-sink-bundle', ConfigFormat::Yaml, ['config', 'kitchen-sink', ...self::QUALITY_ASSURANCE]),
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
