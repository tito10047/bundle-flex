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

namespace Yceruto\BundleFlex\Generator;

use Composer\Util\Filesystem;
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\Constraint;
use Yceruto\BundleFlex\Blueprint\FileTemplate;
use Yceruto\BundleFlex\Composer\ComposerJsonWriter;
use Yceruto\BundleFlex\Contributor\CoreContributors;
use Yceruto\BundleFlex\Feature\FeatureRegistry;
use Yceruto\BundleFlex\Options\BundleOptions;
use Yceruto\BundleFlex\Template\SlotRenderer;
use Yceruto\BundleFlex\Template\TemplateHelper;
use Yceruto\BundleFlex\Template\TemplateRenderer;

/**
 * Generates the bundle files in two phases:
 *
 *  1. contribution: core contributors, then the enabled features, describe the bundle in a blueprint;
 *  2. rendering: every file of the blueprint is rendered from its template and written,
 *     then composer.json is written.
 *
 * Besides contributed files, the configuration files are always derived from the blueprint:
 * config/services.{php,yaml} is always generated, config/routes.{php,yaml} when routes exist
 * (symfony/routing is then required).
 */
final class BundleGenerator
{
    /** Variables given to every template and partial; contributed parameters cannot override them. */
    public const STANDARD_VARIABLES = ['options', 'naming', 'blueprint', 'slots', 'helper'];

    public function __construct(
        private readonly CoreContributors $coreContributors,
        private readonly FeatureRegistry $features,
        private readonly TemplateRenderer $renderer = new TemplateRenderer(),
        private readonly ComposerJsonWriter $composerJsonWriter = new ComposerJsonWriter(),
        private readonly TemplateHelper $helper = new TemplateHelper(),
    ) {
    }

    public static function createDefault(): self
    {
        return new self(CoreContributors::createDefault(), FeatureRegistry::createDefault());
    }

    /**
     * Runs the contribution phase only.
     */
    public function createBlueprint(BundleOptions $options): BundleBlueprint
    {
        $blueprint = new BundleBlueprint($options);

        foreach ($this->coreContributors as $contributor) {
            $contributor->contribute($blueprint);
        }

        foreach ($this->features->getEnabled($options->features) as $feature) {
            $feature->contribute($blueprint);
        }

        $blueprint->services->validate();
        if (!$blueprint->routes->isEmpty()) {
            $blueprint->composer->require('symfony/routing', Constraint::SYMFONY);
        }

        return $blueprint;
    }

    /**
     * Renders every file of the blueprint, without writing anything.
     *
     * @return array<string, string> file contents indexed by path relative to the bundle root, sorted by path
     */
    public function render(BundleBlueprint $blueprint): array
    {
        $slots = new SlotRenderer($blueprint->slots, $this->renderer, $this->helper);
        $context = [
            'options' => $blueprint->options,
            'naming' => $blueprint->naming,
            'blueprint' => $blueprint,
            'slots' => $slots,
            'helper' => $this->helper,
        ];
        $slots->setContext($context);

        $contents = [];
        foreach ($this->resolveFiles($blueprint) as $destination => $file) {
            if ([] !== $conflicts = array_intersect_key($file->parameters, $context)) {
                throw new \LogicException(\sprintf('The parameters of "%s" cannot override the standard template variables "%s".', $destination, implode('", "', array_keys($conflicts))));
            }

            $contents[$destination] = $this->renderer->render($file->template, $file->parameters + $context);
        }

        ksort($contents);

        return $contents;
    }

    /**
     * Generates the bundle into the given directory, composer.json included.
     *
     * @return BundleBlueprint the blueprint the bundle was generated from
     */
    public function generate(BundleOptions $options, string $bundleDir): BundleBlueprint
    {
        $blueprint = $this->createBlueprint($options);
        $filesystem = new Filesystem();

        foreach ($this->render($blueprint) as $destination => $content) {
            $path = $bundleDir.'/'.$destination;
            $filesystem->ensureDirectoryExists(\dirname($path));

            if (false === @file_put_contents($path, $content)) {
                throw new \RuntimeException(\sprintf('Unable to write the file "%s".', $path));
            }
        }

        $this->composerJsonWriter->write($blueprint, $bundleDir.'/composer.json');

        return $blueprint;
    }

    /**
     * @return array<string, FileTemplate>
     */
    private function resolveFiles(BundleBlueprint $blueprint): array
    {
        $format = $blueprint->options->configFormat;
        $files = $blueprint->getFiles();

        foreach (['services' => true, 'routes' => !$blueprint->routes->isEmpty()] as $baseName => $generated) {
            if ($generated) {
                $fileName = $format->getFileName($baseName);
                $files['config/'.$fileName] = new FileTemplate(\sprintf('core/config/%s/%s.tpl', $format->value, $fileName), 'config/'.$fileName);
            }
        }

        return $files;
    }
}
