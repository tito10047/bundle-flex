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
        $blueprint->composer->requireDev('symfony/framework-bundle', Constraint::SYMFONY);
        $blueprint->composer->requireDev('phpunit/phpunit', '^11.5 || ^12.0');
        $blueprint->composer->addKeyword('kitchen-sink');
        $blueprint->composer->addScript('test', 'phpunit', 'Runs the tests', qa: true);
        $blueprint->composer->addScript('lint', ['@php -l src/', '@test'], qa: true);
        $blueprint->composer->allowPlugin('phpstan/extension-installer');

        $blueprint->services->setParameter($alias.'.greeting', 'Hello %name%');
        $blueprint->services->setParameter($alias.'.channels', ['email', '@sms', 'it\'s']);
        $blueprint->services->setParameter($alias.'.enabled', true);

        $blueprint->services->add(new ServiceDefinition($alias.'.greeter', $naming->namespace.'\\Greeter', [
            ServiceArgument::parameter($alias.'.greeting'),
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
        $blueprint->services->addAlias(new ServiceAlias($naming->namespace.'\\GreeterInterface', $alias.'.greeter'));
        $blueprint->services->addAlias(new ServiceAlias($alias.'.default_greeter', $alias.'.greeter', public: true));

        $blueprint->routes->add(new RouteDefinition($alias.'_hello', '/hello/{name}', $alias.'.controller.hello', ['GET', 'HEAD']));
        $blueprint->routes->add(new RouteDefinition($alias.'_home', '/', $alias.'.controller.hello'));

        $blueprint->slots->addValue(ListSlot::BundleUses, 'Symfony\\Component\\DependencyInjection\\Compiler\\PassConfig');
        $blueprint->slots->addPartial(Slot::BundleLoadExtension, 'kitchen-sink/partials/load_extension.php.tpl');
        $blueprint->slots->addPartial(Slot::BundlePrependExtension, 'kitchen-sink/partials/prepend_extension.php.tpl', ['extension' => 'framework']);
        $blueprint->slots->addPartial(Slot::BundleBuild, 'kitchen-sink/partials/build.php.tpl');
        $blueprint->slots->addPartial(Slot::BundleBuild, 'kitchen-sink/partials/build_first.php.tpl', priority: 10);

        $blueprint->addFile('kitchen-sink/NOTES.md.tpl', 'docs/notes.md', ['note' => 'Generated for <'.$naming->className.'>']);
        $blueprint->addEmptyDirectory('translations');
    }
}
