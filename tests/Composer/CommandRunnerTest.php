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

use Composer\IO\BufferIO;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\NullOutput;
use Yceruto\BundleFlex\Composer\CommandRunner;
use Yceruto\BundleFlex\Tests\Support\Filesystem;

/**
 * Runs real Composer commands, offline: the project has no dependency and Packagist is disabled.
 */
final class CommandRunnerTest extends TestCase
{
    private string $dir;
    private string|false $noAudit;

    protected function setUp(): void
    {
        $this->dir = Filesystem::createTempDir('runner');
        $this->noAudit = getenv('COMPOSER_NO_AUDIT');
        putenv('COMPOSER_NO_AUDIT=1');
    }

    protected function tearDown(): void
    {
        Filesystem::remove($this->dir);
        putenv(false === $this->noAudit ? 'COMPOSER_NO_AUDIT' : 'COMPOSER_NO_AUDIT='.$this->noAudit);
    }

    public function testUpdateAllowsThisPluginOnlyWhileRunning(): void
    {
        $composerJson = $this->writeComposerJson([
            'config' => ['allow-plugins' => ['other/plugin' => false]],
            'scripts' => ['pre-update-cmd' => "@php -r \"copy('composer.json', 'composer.during-update.json');\""],
        ]);

        $io = new BufferIO();
        $status = (new CommandRunner($io, $this->dir, output: new NullOutput()))->update();

        self::assertSame(0, $status, $io->getOutput());
        self::assertFileExists($this->dir.'/composer.lock');
        self::assertFileExists($this->dir.'/vendor/autoload.php');
        self::assertSame($composerJson, file_get_contents($this->dir.'/composer.json'), 'composer.json must be restored.');

        /** @var array{config: array{allow-plugins: array<string, bool>}} $duringUpdate */
        $duringUpdate = json_decode((string) file_get_contents($this->dir.'/composer.during-update.json'), true, flags: \JSON_THROW_ON_ERROR);
        self::assertSame(['other/plugin' => false, 'yceruto/bundle-flex' => true], $duringUpdate['config']['allow-plugins']);
    }

    public function testUpdateKeepsGlobalPluginPolicies(): void
    {
        $composerJson = $this->writeComposerJson(['config' => ['allow-plugins' => false]]);

        $status = (new CommandRunner(new BufferIO(), $this->dir, output: new NullOutput()))->update();

        self::assertSame(0, $status);
        self::assertSame($composerJson, file_get_contents($this->dir.'/composer.json'));
    }

    public function testUpdateFailsWithoutComposerJson(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to read "'.$this->dir.'/composer.json".');

        (new CommandRunner(new BufferIO(), $this->dir, output: new NullOutput()))->update();
    }

    public function testRunsScripts(): void
    {
        $this->writeComposerJson(['scripts' => ['hello' => "@php -r \"file_put_contents('hello.txt', 'hi');\""]]);

        $status = (new CommandRunner(new BufferIO(), $this->dir, output: new NullOutput()))->runScript('hello');

        self::assertSame(0, $status);
        self::assertStringEqualsFile($this->dir.'/hello.txt', 'hi');
    }

    public function testReportsFailingScripts(): void
    {
        $this->writeComposerJson(['scripts' => ['fail' => '@php -r "exit(3);"']]);
        $io = new BufferIO();

        $status = (new CommandRunner($io, $this->dir, output: new NullOutput()))->runScript('fail');

        self::assertSame(3, $status);
        self::assertStringContainsString('Error Output', $io->getOutput());
    }

    /**
     * @param array<string, mixed> $extra
     */
    private function writeComposerJson(array $extra): string
    {
        $content = json_encode([
            'name' => 'acme/runner-test',
            'repositories' => [['packagist.org' => false]],
        ] + $extra, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_THROW_ON_ERROR)."\n";
        file_put_contents($this->dir.'/composer.json', $content);

        return $content;
    }
}
