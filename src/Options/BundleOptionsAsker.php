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

namespace Yceruto\BundleFlex\Options;

use Composer\IO\IOInterface;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureInterface;
use Yceruto\BundleFlex\Feature\FeatureRegistry;
use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Naming\BundleNaming;

/**
 * The bundle assistant: asks the questions, shows a summary of the derived names and
 * asks for a final confirmation (on refusal, questions are asked again with the previous
 * answers as defaults).
 *
 * Default answers, by precedence: previous answer, BUNDLE_FLEX_* preset, computed default.
 * Without interaction, defaults are used as is; they are validated in any case.
 */
final class BundleOptionsAsker
{
    private const FALLBACK_VENDOR = 'acme';
    private const FALLBACK_NAME = 'blog-bundle';

    public function __construct(
        private readonly IOInterface $io,
        private readonly FeatureRegistry $features,
        private readonly string $bundleDir,
        private readonly EnvPresets $presets = new EnvPresets(),
        private readonly GitConfig $git = new GitConfig(),
        private readonly OptionNormalizer $normalizer = new OptionNormalizer(),
        private readonly OptionsSummary $summary = new OptionsSummary(),
    ) {
    }

    /**
     * @throws InvalidPresetException when an environment preset is invalid
     */
    public function ask(): BundleOptions
    {
        $this->io->write(['', '<info>Symfony bundle assistant</info>', '']);

        $previous = null;
        while (true) {
            $options = $this->askOptions($previous);

            $this->io->write($this->summary->render($options, $this->features));

            if ($this->io->askConfirmation('Generate the bundle with these options? [<comment>Y/n</comment>] ', true)) {
                return $options;
            }

            $this->io->write(['', 'Let\'s try again, your previous answers are now the defaults.', '']);
            $previous = $options;
        }
    }

    private function askOptions(?BundleOptions $previous): BundleOptions
    {
        $this->section('Identity');
        $presetNaming = $this->presets->getValid(EnvPresets::NAME, $this->normalizer->packageName(...));
        $naming = $this->askPackageName($previous->naming->packageName ?? $presetNaming->packageName ?? $this->defaultPackageName());

        $description = $this->askString(
            'Description',
            $previous->description ?? $this->presets->getValid(EnvPresets::DESCRIPTION, $this->normalizer->description(...)) ?? $naming->className.' for Symfony',
            $this->normalizer->description(...),
        );

        $authorName = $this->askString(
            'Author name',
            $previous->author->name ?? $this->presets->getValid(EnvPresets::AUTHOR_NAME, $this->normalizer->authorName(...)) ?? $this->git->get('user.name'),
            $this->normalizer->authorName(...),
        );

        // a "none" preset must not fall back to the Git email
        $authorEmail = $this->askString(
            \sprintf('Author email ("%s" to omit it)', OptionNormalizer::NO_EMAIL),
            match (true) {
                null !== $previous => $previous->author->email,
                null !== $this->presets->get(EnvPresets::AUTHOR_EMAIL) => $this->presets->getValid(EnvPresets::AUTHOR_EMAIL, $this->normalizer->authorEmail(...)),
                default => $this->git->get('user.email'),
            },
            $this->normalizer->authorEmail(...),
        );

        $license = $this->askString(
            \sprintf('License (%s)', implode(', ', License::identifiers())),
            ($previous->license ?? $this->presets->getValid(EnvPresets::LICENSE, $this->normalizer->license(...)) ?? License::Mit)->value,
            $this->normalizer->license(...),
        );

        $this->section('Configuration');
        $this->hint('  Services and routes can be defined with: '.implode(', ', array_map(static fn (ConfigFormat $format): string => \sprintf('<comment>%s</comment> (%s)', $format->value, $format->getDescription()), ConfigFormat::cases())));
        $configFormat = $this->askString(
            'Configuration format',
            ($previous->configFormat ?? $this->presets->getValid(EnvPresets::CONFIG_FORMAT, $this->normalizer->configFormat(...)) ?? ConfigFormat::Php)->value,
            $this->normalizer->configFormat(...),
        );

        $selection = new FeatureSelection();
        foreach (FeatureGroup::cases() as $group) {
            $features = $this->features->getByGroup($group);
            if ([] === $features) {
                continue;
            }

            $this->section($group->value);
            foreach ($features as $feature) {
                $selection = $selection->with($feature->getName(), $this->askFeature($feature, $selection, $previous));
            }
        }

        return new BundleOptions($naming, $description, new Author($authorName, $authorEmail), $license, $configFormat, $selection);
    }

    private function askPackageName(string $default): BundleNaming
    {
        while (true) {
            $naming = $this->askString('Package name (vendor/name)', $default, $this->normalizer->packageName(...));
            if ($naming->hasBundleSuffix()) {
                return $naming;
            }

            $suggestion = $naming->suggestedPackageName();
            $this->io->writeError(\sprintf('  <warning>Bundle package names should end with "-bundle", e.g. "%s".</warning>', $suggestion));

            if (!$this->io->isInteractive() || $this->io->askConfirmation(\sprintf('  Keep "%s" anyway? [<comment>y/N</comment>] ', $naming->packageName), false)) {
                return $naming;
            }

            $default = $suggestion;
        }
    }

    /**
     * @template T
     *
     * @param callable(?string): T $normalizer
     *
     * @return T
     */
    private function askString(string $label, ?string $default, callable $normalizer): mixed
    {
        $question = null === $default || '' === $default
            ? \sprintf('  %s: ', $label)
            : \sprintf('  %s [<comment>%s</comment>]: ', $label, $default);

        $answer = $this->io->askAndValidate($question, static function (mixed $answer) use ($normalizer): ?string {
            $answer = \is_string($answer) ? $answer : null;
            $normalizer($answer);

            return $answer;
        }, null, $default);

        // without interaction, the default answer is returned as is: normalize (and validate) it here
        return $normalizer(\is_string($answer) ? $answer : null);
    }

    private function askFeature(FeatureInterface $feature, FeatureSelection $answered, ?BundleOptions $previous): bool
    {
        $name = $feature->getName();
        $default = (null !== $previous && $previous->features->has($name) ? $previous->features->isEnabled($name) : null)
            ?? $this->presets->getBool(EnvPresets::featureVariable($name))
            ?? $this->presets->getBool(EnvPresets::ALL)
            ?? $feature->isEnabledByDefault($answered);

        return $this->io->askConfirmation(\sprintf('  %s [<comment>%s</comment>] ', $feature->getQuestion(), $default ? 'Y/n' : 'y/N'), $default);
    }

    /**
     * Without interaction, questions are not printed: neither are their headings and hints.
     */
    private function section(string $title): void
    {
        $this->hint(['', \sprintf(' <info>%s</info>', $title)]);
    }

    /**
     * @param string|list<string> $lines
     */
    private function hint(string|array $lines): void
    {
        if ($this->io->isInteractive()) {
            $this->io->write($lines);
        }
    }

    /**
     * Like "composer init": "<github.user>/<directory name>", with the "-bundle" suffix.
     */
    private function defaultPackageName(): string
    {
        $vendor = self::slugify($this->git->get('github.user')) ?? self::FALLBACK_VENDOR;
        $directory = realpath($this->bundleDir);
        $name = self::slugify(basename(false === $directory ? $this->bundleDir : $directory)) ?? self::FALLBACK_NAME;
        if (!str_ends_with($name, 'bundle')) {
            $name .= '-bundle';
        }

        $packageName = $vendor.'/'.$name;

        return 1 === preg_match(BundleNaming::PACKAGE_NAME_PATTERN, $packageName) && 'bundle' !== $name ? $packageName : self::FALLBACK_VENDOR.'/'.self::FALLBACK_NAME;
    }

    private static function slugify(?string $value): ?string
    {
        $slug = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower((string) $value)), '-');

        return 1 === preg_match('/^[a-z]/', $slug) ? $slug : null;
    }
}
