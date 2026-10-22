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

namespace Yceruto\BundleFlex\Tests;

use Composer\IO\BufferIO;
use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\BundleCreator;
use Yceruto\BundleFlex\Contributor\CoreContributors;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureRegistry;
use Yceruto\BundleFlex\Generator\BundleGenerator;
use Yceruto\BundleFlex\Options\BundleOptionsAsker;
use Yceruto\BundleFlex\Options\EnvPresets;
use Yceruto\BundleFlex\Tests\Support\FakeCommandRunner;
use Yceruto\BundleFlex\Tests\Support\Filesystem;
use Yceruto\BundleFlex\Tests\Support\TestFeature;

final class BundleCreatorTest extends TestCase
{
    private string $dir;
    private BufferIO $io;

    protected function setUp(): void
    {
        $this->dir = Filesystem::createTempDir('creator');
        $this->io = new BufferIO();
        file_put_contents($this->dir.'/composer.json', '{"name": "yceruto/bundle-skeleton"}');
    }

    protected function tearDown(): void
    {
        Filesystem::remove($this->dir);
    }

    public function testCreatesTheBundle(): void
    {
        mkdir($this->dir.'/.github/workflows', 0o777, true);
        foreach (['LICENSE', 'README.md', 'CLAUDE.md', '.gitattributes', '.github/workflows/ci.yml', '.gitignore'] as $file) {
            file_put_contents($this->dir.'/'.$file, 'skeleton');
        }
        $runner = new FakeCommandRunner();

        $this->createBundleCreator($runner, qa: true)->run();

        self::assertSame(['.gitignore', 'composer.json', 'config/services.php', 'src/AcmeBlogBundle.php'], array_keys(Filesystem::readTree($this->dir)));
        self::assertSame(['update', 'run-script cs:fix'], $runner->calls);

        $output = $this->io->getOutput();
        self::assertStringContainsString('AcmeBlogBundle generated.', $output);
        self::assertStringContainsString('AcmeBlogBundle is ready to rock the Symfony world!', $output);
        self::assertStringContainsString('1. cd '.basename($this->dir), $output);
        self::assertStringContainsString('2. composer qa', $output);
        self::assertStringContainsString('3. git init', $output);
        self::assertStringContainsString('https://packagist.org/packages/submit', $output);
        self::assertStringContainsString('https://symfony.com/doc/current/bundles/best_practices.html', $output);
    }

    public function testSuggestsTheTestScriptWithoutQaScripts(): void
    {
        $this->createBundleCreator(new FakeCommandRunner(), test: true)->run();

        self::assertStringContainsString('2. composer test', $this->io->getOutput());
    }

    public function testDoesNotSuggestMissingScripts(): void
    {
        $this->createBundleCreator(new FakeCommandRunner())->run();

        self::assertStringContainsString('2. git init', $this->io->getOutput());
    }

    public function testReportsUpdateFailures(): void
    {
        $runner = new FakeCommandRunner(2);

        $this->createBundleCreator($runner, qa: true)->run();

        self::assertSame(['update'], $runner->calls);
        self::assertStringNotContainsString('ready to rock', $this->io->getOutput());
        self::assertStringContainsString('The dependencies could not be installed, fix the issue above and run "composer update".', $this->io->getOutput());
        self::assertStringContainsString('Next steps', $this->io->getOutput());
    }

    public function testReportsUpdateExceptions(): void
    {
        $runner = new FakeCommandRunner(new \RuntimeException('Network is down'));

        $this->createBundleCreator($runner)->run();

        self::assertStringContainsString('Network is down', $this->io->getOutput());
        self::assertStringContainsString('The dependencies could not be installed', $this->io->getOutput());
    }

    public function testReportsScriptFailures(): void
    {
        $runner = new FakeCommandRunner(0, ['cs:fix' => 1]);

        $this->createBundleCreator($runner, qa: true)->run();

        self::assertStringContainsString('The "cs:fix" script failed, run "composer cs:fix" once fixed.', $this->io->getOutput());
        self::assertStringContainsString('Next steps', $this->io->getOutput());
    }

    public function testDefaultInstanceGeneratesTheBundleAndRunsComposer(): void
    {
        // Packagist is disabled: the update fails fast and offline, which is reported, not thrown
        file_put_contents($this->dir.'/composer.json', '{"name": "yceruto/bundle-skeleton", "repositories": [{"packagist.org": false}]}');
        $presets = ['BUNDLE_FLEX_NAME=acme/default-bundle', 'BUNDLE_FLEX_AUTHOR_NAME=Jane', 'BUNDLE_FLEX_CONFIG=0'];
        array_map(putenv(...), $presets);

        try {
            BundleCreator::createDefault($this->io, $this->dir)->run();
        } finally {
            array_map(static fn (string $preset): bool => putenv(explode('=', $preset)[0]), $presets);
        }

        self::assertFileExists($this->dir.'/src/AcmeDefaultBundle.php');
        self::assertStringContainsString('The dependencies could not be installed', $this->io->getOutput());
        self::assertStringContainsString('"name": "acme/default-bundle"', (string) file_get_contents($this->dir.'/composer.json'));
    }

    private function createBundleCreator(FakeCommandRunner $runner, bool $qa = false, bool $test = false): BundleCreator
    {
        $features = new FeatureRegistry(
            new TestFeature('testing', default: $test, contribution: static function (BundleBlueprint $blueprint): void {
                $blueprint->composer->addScript('test', 'phpunit');
            }),
            new TestFeature('cs', FeatureGroup::QualityAssurance, default: $qa, contribution: static function (BundleBlueprint $blueprint): void {
                $blueprint->composer->addScript('cs', 'php-cs-fixer check', qa: true);
                $blueprint->composer->addScript('cs:fix', 'php-cs-fixer fix');
                $blueprint->composer->runScriptAfterInstall('cs:fix');
            }),
        );
        $presets = new EnvPresets([EnvPresets::NAME => 'acme/blog-bundle', EnvPresets::AUTHOR_NAME => 'Jane']);

        return new BundleCreator(
            $this->io,
            $this->dir,
            new BundleOptionsAsker($this->io, $features, $this->dir, $presets),
            new BundleGenerator(CoreContributors::createDefault(), $features),
            $runner,
        );
    }
}
