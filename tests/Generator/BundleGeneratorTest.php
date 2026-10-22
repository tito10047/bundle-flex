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

namespace Yceruto\BundleFlex\Tests\Generator;

use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\RouteDefinition;
use Yceruto\BundleFlex\Blueprint\ServiceAlias;
use Yceruto\BundleFlex\Contributor\ContributorInterface;
use Yceruto\BundleFlex\Contributor\CoreContributors;
use Yceruto\BundleFlex\Feature\FeatureRegistry;
use Yceruto\BundleFlex\Generator\BundleGenerator;
use Yceruto\BundleFlex\Options\ConfigFormat;
use Yceruto\BundleFlex\Template\TemplateRenderer;
use Yceruto\BundleFlex\Tests\Support\Filesystem;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;
use Yceruto\BundleFlex\Tests\Support\TestFeature;

final class BundleGeneratorTest extends TestCase
{
    public function testRunsCoreContributorsThenEnabledFeaturesInOrder(): void
    {
        $calls = [];
        $core = new class(static function () use (&$calls): void {
            $calls[] = 'core';
        }) implements ContributorInterface {
            public function __construct(private readonly \Closure $callback)
            {
            }

            public function contribute(BundleBlueprint $blueprint): void
            {
                ($this->callback)();
            }
        };
        $features = new FeatureRegistry(
            new TestFeature('a', contribution: static function () use (&$calls): void { $calls[] = 'a'; }),
            new TestFeature('b', contribution: static function () use (&$calls): void { $calls[] = 'b'; }),
            new TestFeature('c', contribution: static function () use (&$calls): void { $calls[] = 'c'; }),
        );

        (new BundleGenerator(new CoreContributors($core), $features))->createBlueprint(OptionsFactory::create(features: ['a' => true, 'b' => false, 'c' => true]));

        self::assertSame(['core', 'a', 'c'], $calls);
    }

    public function testValidatesServices(): void
    {
        $features = new FeatureRegistry(new TestFeature('broken', contribution: static function (BundleBlueprint $blueprint): void {
            $blueprint->services->addAlias(new ServiceAlias('Acme\\FooInterface', 'acme_blog.missing'));
        }));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('targets the undefined service "acme_blog.missing"');

        (new BundleGenerator(new CoreContributors(), $features))->createBlueprint(OptionsFactory::create(features: ['broken' => true]));
    }

    public function testRoutesRequireTheRoutingComponentAndGenerateTheRoutingFile(): void
    {
        $features = new FeatureRegistry(new TestFeature('routes', contribution: static function (BundleBlueprint $blueprint): void {
            $blueprint->routes->add(new RouteDefinition('acme_blog_home', '/', 'acme_blog.controller'));
        }));
        $generator = new BundleGenerator(new CoreContributors(), $features);

        $withRoutes = $generator->createBlueprint(OptionsFactory::create(configFormat: ConfigFormat::Yaml, features: ['routes' => true]));
        $withoutRoutes = $generator->createBlueprint(OptionsFactory::create(configFormat: ConfigFormat::Yaml, features: ['routes' => false]));

        self::assertSame(['symfony/routing' => '^7.4 || ^8.0'], $withRoutes->composer->getRequire());
        self::assertSame(['config/routes.yaml', 'config/services.yaml'], array_keys($generator->render($withRoutes)));
        self::assertSame([], $withoutRoutes->composer->getRequire());
        self::assertSame(['config/services.yaml'], array_keys($generator->render($withoutRoutes)));
    }

    public function testFileParametersCannotOverrideStandardVariables(): void
    {
        $features = new FeatureRegistry(new TestFeature('override', contribution: static function (BundleBlueprint $blueprint): void {
            $blueprint->addFile('core/gitkeep.tpl', 'a/.gitkeep', ['naming' => 'x']);
        }));
        $generator = new BundleGenerator(new CoreContributors(), $features);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The parameters of "a/.gitkeep" cannot override the standard template variables "naming".');

        $generator->render($generator->createBlueprint(OptionsFactory::create(features: ['override' => true])));
    }

    public function testGeneratesFilesAndComposerJson(): void
    {
        $dir = Filesystem::createTempDir('generator');
        file_put_contents($dir.'/composer.json', '{"name": "yceruto/bundle-skeleton", "minimum-stability": "stable"}');

        try {
            $blueprint = BundleGenerator::createDefault()->generate(OptionsFactory::create(features: ['config' => true]), $dir);

            self::assertSame('acme/blog-bundle', $blueprint->naming->packageName);
            self::assertSame(['composer.json', 'config/definition.php', 'config/services.php', 'src/AcmeBlogBundle.php'], array_keys(Filesystem::readTree($dir)));

            /** @var array{name: string, minimum-stability: string} $composer */
            $composer = json_decode((string) file_get_contents($dir.'/composer.json'), true, flags: \JSON_THROW_ON_ERROR);
            self::assertSame('acme/blog-bundle', $composer['name']);
            self::assertSame('stable', $composer['minimum-stability']);
        } finally {
            Filesystem::remove($dir);
        }
    }

    public function testFailsWhenAFileCannotBeWritten(): void
    {
        $dir = Filesystem::createTempDir('generator');
        mkdir($dir.'/src/AcmeBlogBundle.php', 0o777, true);

        try {
            $this->expectException(\RuntimeException::class);
            $this->expectExceptionMessage('Unable to write the file "'.$dir.'/src/AcmeBlogBundle.php".');

            (new BundleGenerator(CoreContributors::createDefault(), new FeatureRegistry(), new TemplateRenderer()))->generate(OptionsFactory::create(), $dir);
        } finally {
            Filesystem::remove($dir);
        }
    }
}
