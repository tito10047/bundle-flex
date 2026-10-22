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

namespace Yceruto\BundleFlex\Tests\Support;

use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\Constraint;
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Blueprint\RouteDefinition;
use Yceruto\BundleFlex\Blueprint\ServiceAlias;
use Yceruto\BundleFlex\Blueprint\ServiceArgument;
use Yceruto\BundleFlex\Blueprint\ServiceDefinition;
use Yceruto\BundleFlex\Blueprint\ServiceTag;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureInterface;
use Yceruto\BundleFlex\Feature\FeatureSelection;

/**
 * Test-only feature exercising every contribution point of the blueprint,
 * so snapshots cover each rendering branch in every configuration format.
 */
final class KitchenSinkFeature implements FeatureInterface
{
    public const NAME = 'kitchen-sink';
    public const TEMPLATE_DIR = __DIR__.'/templates';

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
        return 'Add every possible contribution?';
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return false;
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        $naming = $blueprint->naming;
        $alias = $naming->alias;

        $blueprint->composer->require('symfony/http-foundation', Constraint::SYMFONY);
        // already required by a core contributor with the same constraint: no-op
        $blueprint->composer->requireDev('symfony/framework-bundle', Constraint::SYMFONY);
        $blueprint->composer->requireDev('symfony/browser-kit', Constraint::SYMFONY);
        $blueprint->composer->addKeyword('kitchen-sink');
        $blueprint->composer->addScript('lint', ['@php -l src/', '@test'], qa: true);
        $blueprint->composer->addScript('kitchen-sink', 'echo kitchen-sink', 'Checks the kitchen sink');
        $blueprint->composer->allowPlugin('phpstan/extension-installer');

        $blueprint->services->setParameter($alias.'.kitchen_sink.greeting', 'Hello %name%');
        $blueprint->services->setParameter($alias.'.channels', ['email', '@sms', 'it\'s']);
        $blueprint->services->setParameter($alias.'.enabled', true);

        $blueprint->services->add(new ServiceDefinition($alias.'.kitchen_sink', $naming->namespace.'\\KitchenSink', [
            ServiceArgument::parameter($alias.'.kitchen_sink.greeting'),
            ServiceArgument::service($alias.'.formatter'),
            ServiceArgument::value('@literal 100%'),
            ServiceArgument::value(42),
            ServiceArgument::value(1.5),
            ServiceArgument::value(false),
            ServiceArgument::value(null),
            ServiceArgument::value(['a', 'b']),
        ]));
        $blueprint->services->add(new ServiceDefinition($alias.'.formatter', $naming->namespace.'\\Formatter\\Formatter'));
        $blueprint->services->add(new ServiceDefinition('.'.$alias.'.cache_warmer', $naming->namespace.'\\CacheWarmer\\Formatter', [
            ServiceArgument::service($alias.'.formatter'),
        ], [
            new ServiceTag('kernel.cache_warmer', ['priority' => 10]),
        ]));
        $blueprint->services->add(new ServiceDefinition($alias.'.controller.hello', $naming->namespace.'\\Controller\\HelloController', [
            ServiceArgument::service($alias.'.greeter'),
        ], [
            new ServiceTag('controller.service_arguments'),
        ], public: true));
        $blueprint->services->addAlias(new ServiceAlias($naming->namespace.'\\Formatter\\FormatterInterface', $alias.'.formatter'));
        $blueprint->services->addAlias(new ServiceAlias($alias.'.default_greeter', $alias.'.greeter', public: true));

        $blueprint->routes->add(new RouteDefinition($alias.'_hello', '/hello/{name}', $alias.'.controller.hello', ['GET', 'HEAD']));
        $blueprint->routes->add(new RouteDefinition($alias.'_home', '/', $alias.'.controller.hello'));

        $blueprint->slots->addValue(ListSlot::BundleUses, 'Symfony\\Component\\DependencyInjection\\Compiler\\PassConfig');
        $blueprint->slots->addPartial(Slot::BundleLoadExtension, 'kitchen-sink/partials/load_extension.php.tpl');
        $blueprint->slots->addPartial(Slot::BundlePrependExtension, 'kitchen-sink/partials/prepend_extension.php.tpl', ['extension' => 'framework']);
        $blueprint->slots->addPartial(Slot::BundleBuild, 'kitchen-sink/partials/build.php.tpl');
        $blueprint->slots->addPartial(Slot::BundleBuild, 'kitchen-sink/partials/build_first.php.tpl', priority: 10);

        $blueprint->slots->addValue(ListSlot::ReadmeBadges, '[![Kitchen sink](https://img.shields.io/badge/kitchen-sink-blue)](https://example.com)', '[![PHP](https://img.shields.io/badge/php-8.2-blue)](https://php.net)');
        $blueprint->slots->addPartial(Slot::ReadmeInstallationSteps, 'kitchen-sink/partials/readme_step.md.tpl', ['title' => 'Import the Routes']);
        $blueprint->slots->addPartial(Slot::ReadmeInstallationSteps, 'kitchen-sink/partials/readme_step.md.tpl', ['title' => 'Install the Assets'], priority: -10);
        $blueprint->slots->addPartial(Slot::ReadmeSections, 'kitchen-sink/partials/section.md.tpl', ['title' => 'Kitchen Sink']);
        $blueprint->slots->addPartial(Slot::DocsSections, 'kitchen-sink/partials/section.md.tpl', ['title' => 'Kitchen Sink']);

        $blueprint->slots->addValue(ListSlot::TestKernelBundles, 'Symfony\\Bundle\\TwigBundle\\TwigBundle');
        $blueprint->slots->addValue(ListSlot::TestKernelUses, 'Symfony\\Component\\HttpKernel\\KernelEvents');
        $blueprint->slots->addPartial(Slot::TestKernelConfigureContainer, 'kitchen-sink/partials/kernel_configure_container.php.tpl');
        $blueprint->slots->addPartial(Slot::TestKernelConfigureRoutes, 'kitchen-sink/partials/kernel_configure_routes.php.tpl');

        $blueprint->slots->addValue(ListSlot::GitAttributesExportIgnore, '/kitchen-sink.dist.php');
        $blueprint->slots->addValue(ListSlot::GitIgnore, '/.kitchen-sink.cache', '/vendor/');

        $blueprint->addFile('kitchen-sink/NOTES.md.tpl', 'docs/notes.md', ['note' => 'Generated for <'.$naming->className.'>']);
        $blueprint->addEmptyDirectory('translations');
    }
}
