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

namespace Yceruto\BundleFlex\Tests\Blueprint;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\FileTemplate;
use Yceruto\BundleFlex\Options\ConfigFormat;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class BundleBlueprintTest extends TestCase
{
    public function testExposesOptionsAndComponents(): void
    {
        $options = OptionsFactory::create(features: ['config' => true]);
        $blueprint = new BundleBlueprint($options);

        self::assertSame($options, $blueprint->options);
        self::assertSame($options->naming, $blueprint->naming);
        self::assertTrue($blueprint->hasFeature('config'));
        self::assertTrue($blueprint->services->isEmpty());
        self::assertTrue($blueprint->routes->isEmpty());
        self::assertSame([], $blueprint->composer->getRequire());
        self::assertSame([], $blueprint->getFiles());
    }

    public function testCollectsFiles(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());
        $blueprint->addFile('a.tpl', 'docs/index.md', ['x' => 1]);
        $blueprint->addEmptyDirectory('translations/');

        self::assertTrue($blueprint->hasFile('docs/index.md'));
        self::assertFalse($blueprint->hasFile('README.md'));
        self::assertEquals([
            'docs/index.md' => new FileTemplate('a.tpl', 'docs/index.md', ['x' => 1]),
            'translations/.gitkeep' => new FileTemplate('core/gitkeep.tpl', 'translations/.gitkeep'),
        ], $blueprint->getFiles());
    }

    public function testRejectsDuplicateDestinations(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());
        $blueprint->addFile('a.tpl', 'README.md');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The file "README.md" is already generated from the template "a.tpl".');

        $blueprint->addFile('b.tpl', 'README.md');
    }

    /**
     * @return iterable<string, array{ConfigFormat, string, string}>
     */
    public static function provideReservedDestinations(): iterable
    {
        yield 'composer.json' => [ConfigFormat::Php, 'composer.json', '$blueprint->composer'];
        yield 'php services' => [ConfigFormat::Php, 'config/services.php', '$blueprint->services'];
        yield 'php-array routes' => [ConfigFormat::PhpArray, 'config/routes.php', '$blueprint->routes'];
        yield 'yaml services' => [ConfigFormat::Yaml, 'config/services.yaml', '$blueprint->services'];
        yield 'yaml routes' => [ConfigFormat::Yaml, 'config/routes.yaml', '$blueprint->routes'];
    }

    #[DataProvider('provideReservedDestinations')]
    public function testRejectsDestinationsDerivedFromTheBlueprint(ConfigFormat $format, string $destination, string $api): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(configFormat: $format));

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(\sprintf('The file "%s" is generated from the blueprint: use %s instead.', $destination, $api));

        $blueprint->addFile('a.tpl', $destination);
    }
}
