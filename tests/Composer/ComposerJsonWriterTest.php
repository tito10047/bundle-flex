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

namespace Yceruto\BundleFlex\Tests\Composer;

use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Composer\ComposerJsonWriter;
use Yceruto\BundleFlex\Tests\Support\Filesystem;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class ComposerJsonWriterTest extends TestCase
{
    public function testBuildsTheManifestFromTheBlueprint(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create('my-vendor/foo-bundle'));
        $blueprint->composer->addKeyword('symfony');
        $blueprint->composer->require('symfony/http-kernel', '^7.4 || ^8.0');
        $blueprint->composer->require('php', '>=8.2');
        $blueprint->composer->requireDev('phpunit/phpunit', '^11.5');
        $blueprint->composer->addScript('test', 'phpunit', 'Runs the tests', qa: true);
        $blueprint->composer->addScript('cs', 'php-cs-fixer check', qa: true);
        $blueprint->composer->addScript('cs:fix', ['php-cs-fixer fix', 'echo done']);
        $blueprint->composer->allowPlugin('phpstan/extension-installer');

        $skeleton = [
            'name' => 'yceruto/bundle-skeleton',
            'type' => 'project',
            'license' => 'MIT',
            'description' => 'A skeleton',
            'minimum-stability' => 'stable',
            'require' => ['symfony/framework-bundle' => '^6.4'],
            'require-dev' => ['yceruto/bundle-flex' => '^1.0'],
            'scripts' => ['old' => 'echo'],
            'extra' => ['branch-alias' => []],
            'config' => ['allow-plugins' => ['yceruto/bundle-flex' => true, 'other/plugin' => false], 'platform' => ['php' => '8.2.0']],
        ];

        self::assertSame([
            'name' => 'my-vendor/foo-bundle',
            'description' => 'MyVendorFooBundle for Symfony',
            'license' => 'MIT',
            'type' => 'symfony-bundle',
            'keywords' => ['symfony'],
            'authors' => [['name' => 'Jane Doe', 'email' => 'jane@example.com']],
            'require' => ['php' => '>=8.2', 'symfony/http-kernel' => '^7.4 || ^8.0'],
            'require-dev' => ['phpunit/phpunit' => '^11.5'],
            'autoload' => ['psr-4' => ['MyVendor\\FooBundle\\' => 'src/']],
            'autoload-dev' => ['psr-4' => ['MyVendor\\FooBundle\\Tests\\' => 'tests/']],
            'minimum-stability' => 'stable',
            'config' => [
                'allow-plugins' => ['other/plugin' => false, 'phpstan/extension-installer' => true],
                'platform' => ['php' => '8.2.0'],
                'sort-packages' => true,
            ],
            'extra' => ['branch-alias' => []],
            'scripts' => [
                'cs' => 'php-cs-fixer check',
                'cs:fix' => ['php-cs-fixer fix', 'echo done'],
                'qa' => ['@test', '@cs'],
                'test' => 'phpunit',
            ],
            'scripts-descriptions' => [
                'qa' => 'Runs every quality check',
                'test' => 'Runs the tests',
            ],
        ], (new ComposerJsonWriter())->build($blueprint, $skeleton));
    }

    public function testOmitsEmptySections(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(email: null));

        self::assertSame([
            'name' => 'acme/blog-bundle',
            'description' => 'AcmeBlogBundle for Symfony',
            'license' => 'MIT',
            'type' => 'symfony-bundle',
            'authors' => [['name' => 'Jane Doe']],
            'autoload' => ['psr-4' => ['Acme\\BlogBundle\\' => 'src/']],
            'autoload-dev' => ['psr-4' => ['Acme\\BlogBundle\\Tests\\' => 'tests/']],
            'config' => ['sort-packages' => true],
        ], (new ComposerJsonWriter())->build($blueprint, ['config' => ['allow-plugins' => ['yceruto/bundle-flex' => true]]]));
    }

    public function testScriptsWithoutDescriptions(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());
        $blueprint->composer->addScript('test', 'phpunit');

        $manifest = (new ComposerJsonWriter())->build($blueprint);

        self::assertSame(['test' => 'phpunit'], $manifest['scripts']);
        self::assertArrayNotHasKey('scripts-descriptions', $manifest);
    }

    public function testWritesTheFile(): void
    {
        $dir = Filesystem::createTempDir('writer');

        try {
            $writer = new ComposerJsonWriter();
            $writer->write(new BundleBlueprint(OptionsFactory::create()), $dir.'/composer.json');

            $content = (string) file_get_contents($dir.'/composer.json');
            self::assertStringStartsWith("{\n    \"name\": \"acme/blog-bundle\",\n", $content);
            self::assertStringContainsString('"Acme\\\\BlogBundle\\\\": "src/"', $content);
            self::assertStringEndsWith("}\n", $content);
        } finally {
            Filesystem::remove($dir);
        }
    }
}
