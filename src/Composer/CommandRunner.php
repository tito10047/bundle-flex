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

namespace Yceruto\BundleFlex\Composer;

use Composer\Command\BaseCommand;
use Composer\Command\RunScriptCommand;
use Composer\Command\UpdateCommand;
use Composer\Console\Application;
use Composer\EventDispatcher\ScriptExecutionException;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Json\JsonFile;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs Composer commands in-process against the bundle composer.json.
 *
 * Each command gets a fresh Composer instance, so it sees the composer.json written by the plugin.
 */
final class CommandRunner implements CommandRunnerInterface
{
    private readonly OutputInterface $output;

    public function __construct(
        private readonly IOInterface $io,
        private readonly string $workingDir,
        private readonly Application $application = new Application(),
        ?OutputInterface $output = null,
    ) {
        $this->output = $output ?? Factory::createOutput();
    }

    /**
     * Runs "composer update", which installs the bundle dependencies and uninstalls this plugin.
     *
     * This plugin is still installed when the update starts but no longer allowed in composer.json:
     * it is allowed for the duration of the update only, so Composer neither prompts for it nor fails.
     * The allow-plugins section is not part of the lock file hash, restoring it is safe.
     */
    public function update(): int
    {
        $file = $this->workingDir.'/composer.json';
        if (!is_file($file) || false === $original = file_get_contents($file)) {
            throw new \RuntimeException(\sprintf('Unable to read "%s".', $file));
        }

        $json = new JsonFile($file);
        $manifest = $json->read();
        if (\is_array($manifest)) {
            $config = \is_array($manifest['config'] ?? null) ? $manifest['config'] : [];
            $allowPlugins = $config['allow-plugins'] ?? [];
            if (\is_array($allowPlugins)) {
                $allowPlugins[ComposerJsonWriter::PLUGIN_PACKAGE] = true;
                $config['allow-plugins'] = $allowPlugins;
                $manifest['config'] = $config;
                $json->write($manifest);
            }
        }

        try {
            return $this->run(new UpdateCommand(), []);
        } finally {
            file_put_contents($file, $original);
        }
    }

    /**
     * Runs "composer run-script <script>"; a failing script is reported, not thrown.
     */
    public function runScript(string $script): int
    {
        try {
            return $this->run(new RunScriptCommand(), ['script' => $script]);
        } catch (ScriptExecutionException $e) {
            $this->io->writeError('<error>'.$e->getMessage().'</error>');

            return 0 !== $e->getCode() ? $e->getCode() : 1;
        }
    }

    /**
     * @param array<string, mixed> $arguments
     */
    private function run(BaseCommand $command, array $arguments): int
    {
        $command->setApplication($this->application);
        $command->setIO($this->io);
        $command->setComposer(Factory::create($this->io, $this->workingDir.'/composer.json'));

        $input = new ArrayInput($arguments, $command->getDefinition());
        $input->setInteractive($this->io->isInteractive());

        // like "composer --working-dir": scripts and relative paths are resolved from the bundle directory
        $cwd = getcwd();
        if (!chdir($this->workingDir)) {
            throw new \RuntimeException(\sprintf('Unable to change the working directory to "%s".', $this->workingDir));
        }

        try {
            return $command->run($input, $this->output);
        } finally {
            if (false !== $cwd) {
                chdir($cwd);
            }
        }
    }
}
