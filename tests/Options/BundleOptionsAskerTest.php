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

namespace Yceruto\BundleFlex\Tests\Options;

use Composer\IO\BufferIO;
use Composer\Util\ProcessExecutor;
use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureRegistry;
use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Options\BundleOptionsAsker;
use Yceruto\BundleFlex\Options\ConfigFormat;
use Yceruto\BundleFlex\Options\EnvPresets;
use Yceruto\BundleFlex\Options\GitConfig;
use Yceruto\BundleFlex\Options\InvalidPresetException;
use Yceruto\BundleFlex\Options\License;
use Yceruto\BundleFlex\Tests\Support\TestFeature;

final class BundleOptionsAskerTest extends TestCase
{
    private string $bundleDir;

    protected function setUp(): void
    {
        $this->bundleDir = sys_get_temp_dir().'/bundle-flex-asker/my-blog';
        if (!is_dir($this->bundleDir)) {
            mkdir($this->bundleDir, 0o777, true);
        }
    }

    protected function tearDown(): void
    {
        rmdir($this->bundleDir);
        rmdir(\dirname($this->bundleDir));
    }

    public function testUsesDefaultsWithoutInteraction(): void
    {
        $io = new BufferIO();

        $options = $this->createAsker($io, git: ['user.name' => 'Jane Doe', 'user.email' => 'jane@example.com'])->ask();

        self::assertSame('acme/my-blog-bundle', $options->naming->packageName);
        self::assertSame('AcmeMyBlogBundle for Symfony', $options->description);
        self::assertSame('Jane Doe', $options->author->name);
        self::assertSame('jane@example.com', $options->author->email);
        self::assertSame(License::Mit, $options->license);
        self::assertSame(ConfigFormat::Php, $options->configFormat);
        self::assertSame(['controllers' => false, 'twig' => false, 'phpstan' => true], $options->features->toArray());

        $output = $io->getOutput();
        self::assertStringContainsString('Symfony bundle assistant', $output);
        self::assertStringContainsString('Extension alias     acme_my_blog', $output);
        self::assertStringContainsString('Twig namespace      @AcmeMyBlog', $output);
        self::assertStringContainsString('Features            none', $output);
        self::assertStringContainsString('Quality assurance   phpstan', $output);
        self::assertStringNotContainsString(' Identity', $output, 'Headings of unasked questions are not printed.');
    }

    public function testUsesGithubUserAsDefaultVendor(): void
    {
        $options = $this->createAsker(new BufferIO(), git: ['user.name' => 'Jane', 'github.user' => 'Jane-Doe'])->ask();

        self::assertSame('jane-doe/my-blog-bundle', $options->naming->packageName);
        self::assertNull($options->author->email);
    }

    public function testUsesPresetsWithoutInteraction(): void
    {
        $presets = new EnvPresets([
            EnvPresets::NAME => 'my-vendor/foo-bundle',
            EnvPresets::DESCRIPTION => 'Foo for Symfony',
            EnvPresets::AUTHOR_NAME => 'John',
            EnvPresets::AUTHOR_EMAIL => 'john@example.com',
            EnvPresets::LICENSE => 'apache-2.0',
            EnvPresets::CONFIG_FORMAT => 'yaml',
            EnvPresets::ALL => '1',
            'BUNDLE_FLEX_TWIG' => '0',
        ]);

        $options = $this->createAsker(new BufferIO(), $presets, ['user.name' => 'Jane'])->ask();

        self::assertSame('my-vendor/foo-bundle', $options->naming->packageName);
        self::assertSame('Foo for Symfony', $options->description);
        self::assertSame('John', $options->author->name);
        self::assertSame('john@example.com', $options->author->email);
        self::assertSame(License::Apache2, $options->license);
        self::assertSame(ConfigFormat::Yaml, $options->configFormat);
        self::assertSame(['controllers' => true, 'twig' => false, 'phpstan' => true], $options->features->toArray());
    }

    public function testEmailPresetCanOmitTheGitEmail(): void
    {
        $options = $this->createAsker(new BufferIO(), new EnvPresets([EnvPresets::AUTHOR_EMAIL => 'none']), ['user.name' => 'Jane', 'user.email' => 'jane@example.com'])->ask();

        self::assertNull($options->author->email);
    }

    public function testAllPresetCanDisableEverything(): void
    {
        $options = $this->createAsker(new BufferIO(), new EnvPresets([EnvPresets::ALL => '0']), ['user.name' => 'Jane'])->ask();

        self::assertSame([], $options->features->getEnabled());
    }

    public function testRejectsInvalidPresets(): void
    {
        $this->expectException(InvalidPresetException::class);
        $this->expectExceptionMessage('Invalid value for the environment variable "BUNDLE_FLEX_CONFIG_FORMAT"');

        $this->createAsker(new BufferIO(), new EnvPresets([EnvPresets::CONFIG_FORMAT => 'xml']), ['user.name' => 'Jane'])->ask();
    }

    public function testFailsWithoutInteractionWhenNoAuthorIsKnown(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The author name must not be empty.');

        $this->createAsker(new BufferIO())->ask();
    }

    public function testWarnsAboutTheBundleSuffixWithoutInteraction(): void
    {
        $io = new BufferIO();

        $options = $this->createAsker($io, new EnvPresets([EnvPresets::NAME => 'acme/blog']), ['user.name' => 'Jane'])->ask();

        self::assertSame('acme/blog', $options->naming->packageName);
        self::assertStringContainsString('Bundle package names should end with "-bundle", e.g. "acme/blog-bundle".', $io->getOutput());
    }

    public function testAsksQuestionsInteractively(): void
    {
        $io = new BufferIO();
        $io->setUserInputs([
            'Acme/Invalid',      // invalid package name, asked again
            'acme/blog',         // valid, without the -bundle suffix
            'n',                 // do not keep it: asked again with "acme/blog-bundle" as default
            '',                  // accept the suggestion
            'The best blog',     // description
            '',                  // author name from git
            'not-an-email',      // invalid email, asked again
            'none',              // no email (the default comes from git)
            'bsd-3-clause',      // license
            'php-array',         // config format
            'y',                 // controllers
            '',                  // twig: defaults to yes because controllers are enabled
            'n',                 // phpstan
            'y',                 // confirm
        ]);

        $options = $this->createAsker($io, git: ['user.name' => 'Jane Doe', 'user.email' => 'jane@example.com'])->ask();

        self::assertSame('acme/blog-bundle', $options->naming->packageName);
        self::assertSame('The best blog', $options->description);
        self::assertSame('Jane Doe', $options->author->name);
        self::assertNull($options->author->email);
        self::assertSame(License::Bsd3Clause, $options->license);
        self::assertSame(ConfigFormat::PhpArray, $options->configFormat);
        self::assertSame(['controllers' => true, 'twig' => true, 'phpstan' => false], $options->features->toArray());

        $output = $io->getOutput();
        self::assertStringContainsString('The package name "Acme/Invalid" is invalid', $output);
        self::assertStringContainsString('"not-an-email" is not a valid email address.', $output);
        self::assertStringContainsString('Keep "acme/blog" anyway?', $output);
        self::assertStringContainsString('Package name (vendor/name) [acme/blog-bundle]', $output);
        self::assertStringContainsString(' Identity', $output);
        self::assertStringContainsString('Services and routes can be defined with:', $output);
    }

    public function testKeepsAPackageNameWithoutSuffixWhenConfirmed(): void
    {
        $io = new BufferIO();
        $io->setUserInputs(['acme/blog', 'y', '', '', '', '', '', '', '', '', '', 'y']);

        $options = $this->createAsker($io, git: ['user.name' => 'Jane'])->ask();

        self::assertSame('acme/blog', $options->naming->packageName);
    }

    public function testAsksAgainWithPreviousAnswersAsDefaultsWhenNotConfirmed(): void
    {
        $io = new BufferIO();
        $io->setUserInputs([
            // first round
            'acme/first-bundle', 'First', 'John', 'john@example.com', 'MPL-2.0', 'yaml', 'y', 'n', 'n',
            'n', // not confirmed
            // second round: only the description changes
            '', 'Second', '', '', '', '', '', '', '',
            'y',
        ]);

        $options = $this->createAsker($io, git: ['user.name' => 'Jane'])->ask();

        self::assertSame('acme/first-bundle', $options->naming->packageName);
        self::assertSame('Second', $options->description);
        self::assertSame('John', $options->author->name);
        self::assertSame('john@example.com', $options->author->email);
        self::assertSame(License::Mpl2, $options->license);
        self::assertSame(ConfigFormat::Yaml, $options->configFormat);
        self::assertSame(['controllers' => true, 'twig' => false, 'phpstan' => false], $options->features->toArray());
        self::assertStringContainsString('your previous answers are now the defaults', $io->getOutput());
    }

    /**
     * @param array<string, string> $git
     */
    private function createAsker(BufferIO $io, EnvPresets $presets = new EnvPresets(), array $git = []): BundleOptionsAsker
    {
        $process = $this->createMock(ProcessExecutor::class);
        $process->method('execute')->willReturnCallback(static function (array $command, mixed &$output) use ($git): int {
            $key = $command[3];
            if (!\is_string($key) || !isset($git[$key])) {
                return 1;
            }
            $output = $git[$key]."\n";

            return 0;
        });

        $features = new FeatureRegistry(
            new TestFeature('controllers'),
            new TestFeature('twig', default: static fn (FeatureSelection $previous): bool => $previous->isEnabled('controllers')),
            new TestFeature('phpstan', FeatureGroup::QualityAssurance, default: true),
        );

        return new BundleOptionsAsker($io, $features, $this->bundleDir, $presets, new GitConfig($process));
    }
}
