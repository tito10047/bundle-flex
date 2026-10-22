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

namespace Yceruto\BundleFlex;

use Composer\IO\IOInterface;
use Composer\Util\Filesystem;
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\ComposerManifest;
use Yceruto\BundleFlex\Composer\CommandRunner;
use Yceruto\BundleFlex\Composer\CommandRunnerInterface;
use Yceruto\BundleFlex\Contributor\CoreContributors;
use Yceruto\BundleFlex\Feature\FeatureRegistry;
use Yceruto\BundleFlex\Generator\BundleGenerator;
use Yceruto\BundleFlex\Options\BundleOptionsAsker;
use Yceruto\BundleFlex\Options\EnvPresets;

/**
 * Turns the freshly created skeleton project into the bundle:
 *
 *  1. removes the files of the skeleton itself (README, LICENSE, CI...)
 *  2. asks the questions and shows the summary
 *  3. generates the files and composer.json
 *  4. runs a single "composer update" (which also uninstalls this plugin)
 *  5. runs the scripts requested by the features (e.g. "cs:fix")
 *  6. prints the next steps
 *
 * Once the update has started, the plugin files are removed from vendor/: the code running
 * after it must only use classes that are already loaded.
 */
final class BundleCreator
{
    /** Files of the bundle-skeleton repository that must not end up in the bundle. */
    public const SKELETON_FILES = ['.github', '.gitattributes', 'CLAUDE.md', 'LICENSE', 'README.md'];

    public function __construct(
        private readonly IOInterface $io,
        private readonly string $bundleDir,
        private readonly BundleOptionsAsker $asker,
        private readonly BundleGenerator $generator,
        private readonly CommandRunnerInterface $commandRunner,
        private readonly Filesystem $filesystem = new Filesystem(),
    ) {
    }

    /**
     * Wires the default collaborators: built-in features, BUNDLE_FLEX_* presets and in-process Composer commands.
     */
    public static function createDefault(IOInterface $io, string $bundleDir): self
    {
        $features = FeatureRegistry::createDefault();

        return new self(
            $io,
            $bundleDir,
            new BundleOptionsAsker($io, $features, $bundleDir, EnvPresets::fromEnvironment()),
            new BundleGenerator(CoreContributors::createDefault(), $features),
            new CommandRunner($io, $bundleDir),
        );
    }

    public function run(): void
    {
        $this->removeSkeletonFiles();

        $options = $this->asker->ask();
        $blueprint = $this->generator->generate($options, $this->bundleDir);
        $this->io->write(\sprintf('<info>%s generated.</info> Installing its dependencies...', $options->naming->className));

        $scripts = $blueprint->composer->getScriptsAfterInstall();
        $nextSteps = $this->nextSteps($blueprint);

        try {
            $status = $this->commandRunner->update();
        } catch (\Exception $e) {
            $this->io->writeError('<error>'.$e->getMessage().'</error>');
            $status = 1;
        }

        if (0 !== $status) {
            $this->io->writeError(['', '<warning>The dependencies could not be installed, fix the issue above and run "composer update".</warning>']);
            $this->io->write($nextSteps);

            return;
        }

        foreach ($scripts as $script) {
            if (0 !== $this->commandRunner->runScript($script)) {
                $this->io->writeError(\sprintf('<warning>The "%s" script failed, run "composer %1$s" once fixed.</warning>', $script));
            }
        }

        $this->io->write(['', \sprintf('<bg=green;fg=white> %s is ready to rock the Symfony world! </>', $options->naming->className)]);
        $this->io->write($nextSteps);
    }

    private function removeSkeletonFiles(): void
    {
        foreach (self::SKELETON_FILES as $file) {
            $path = $this->bundleDir.'/'.$file;
            if (file_exists($path) || is_link($path)) {
                $this->filesystem->remove($path);
            }
        }
    }

    /**
     * @return list<string>
     */
    private function nextSteps(BundleBlueprint $blueprint): array
    {
        $steps = ['cd '.basename($this->bundleDir)];
        if ([] !== $blueprint->composer->getQaScriptNames()) {
            $steps[] = 'composer '.ComposerManifest::QA_SCRIPT;
        } elseif ($blueprint->composer->hasScript(ComposerManifest::TEST_SCRIPT)) {
            $steps[] = 'composer '.ComposerManifest::TEST_SCRIPT;
        }
        $steps[] = 'git init && git add -A && git commit -m "Initial commit"';
        $steps[] = 'Push it to GitHub and submit it to Packagist: https://packagist.org/packages/submit';

        $lines = [
            '',
            ' <info>Next steps</info>',
            '',
        ];
        foreach ($steps as $i => $step) {
            $lines[] = \sprintf('  %d. %s', $i + 1, $step);
        }
        $lines[] = '';
        $lines[] = ' Keep following the best practices: <comment>https://symfony.com/doc/current/bundles/best_practices.html</comment>';
        $lines[] = '';

        return $lines;
    }
}
