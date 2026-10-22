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

namespace Yceruto\BundleFlex\Feature;

/**
 * Immutable set of answers to the feature questions (feature name => enabled).
 */
final readonly class FeatureSelection
{
    /**
     * @param array<string, bool> $answers
     */
    public function __construct(private array $answers = [])
    {
    }

    public function with(string $feature, bool $enabled): self
    {
        $answers = $this->answers;
        $answers[$feature] = $enabled;

        return new self($answers);
    }

    /**
     * Whether the given feature has been answered (enabled or not).
     */
    public function has(string $feature): bool
    {
        return \array_key_exists($feature, $this->answers);
    }

    /**
     * @throws \LogicException when the feature has not been answered (typo, or asked after the caller)
     */
    public function isEnabled(string $feature): bool
    {
        if (!$this->has($feature)) {
            throw new \LogicException(\sprintf('The feature "%s" has not been answered (yet). Answered features: "%s". Features can only depend on features registered before them.', $feature, implode('", "', array_keys($this->answers))));
        }

        return $this->answers[$feature];
    }

    /**
     * @return list<string>
     */
    public function getEnabled(): array
    {
        return array_keys(array_filter($this->answers));
    }

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return $this->answers;
    }
}
