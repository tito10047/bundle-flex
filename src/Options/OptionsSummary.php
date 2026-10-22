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

use Symfony\Component\Console\Formatter\OutputFormatter;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureInterface;
use Yceruto\BundleFlex\Feature\FeatureRegistry;

/**
 * Summary of the answers and of the derived names, shown before the final confirmation.
 */
final class OptionsSummary
{
    /**
     * @return list<string> console lines (with Composer IO formatting tags)
     */
    public function render(BundleOptions $options, FeatureRegistry $features): array
    {
        $naming = $options->naming;
        $author = $options->author->name.(null !== $options->author->email ? ' <'.$options->author->email.'>' : '');

        $rows = [
            'Package' => $naming->packageName,
            'Description' => $options->description,
            'Author' => $author,
            'License' => $options->license->value,
            'Config format' => $options->configFormat->value.' ('.$options->configFormat->getDescription().')',
            'Namespace' => $naming->namespace,
            'Bundle class' => $naming->fqcn,
            'Extension alias' => $naming->alias,
            'Translation domain' => $naming->translationDomain,
            'Twig namespace' => '@'.$naming->twigNamespace,
        ];

        foreach (FeatureGroup::cases() as $group) {
            $groupFeatures = $features->getByGroup($group);
            if ([] === $groupFeatures) {
                continue;
            }

            $enabled = array_filter($groupFeatures, static fn (FeatureInterface $feature): bool => $options->features->has($feature->getName()) && $options->features->isEnabled($feature->getName()));
            $rows[$group->value] = [] === $enabled ? 'none' : implode(', ', array_map(static fn (FeatureInterface $feature): string => $feature->getName(), $enabled));
        }

        $width = max(array_map(strlen(...), array_keys($rows)));
        $lines = ['', ' <info>Summary</info>', ''];
        foreach ($rows as $label => $value) {
            $lines[] = \sprintf('  %s  <comment>%s</comment>', str_pad($label, $width), OutputFormatter::escape($value));
        }
        $lines[] = '';

        return $lines;
    }
}
