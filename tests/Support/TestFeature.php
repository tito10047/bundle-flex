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

namespace Yceruto\BundleFlex\Tests\Support;

use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureInterface;
use Yceruto\BundleFlex\Feature\FeatureSelection;

/**
 * Configurable feature for tests.
 */
final class TestFeature implements FeatureInterface
{
    /** @var \Closure(FeatureSelection): bool */
    private readonly \Closure $default;
    /** @var \Closure(BundleBlueprint): void */
    private readonly \Closure $contribution;

    /**
     * @param (\Closure(FeatureSelection): bool)|bool $default
     * @param (\Closure(BundleBlueprint): void)|null  $contribution
     */
    public function __construct(
        private readonly string $name,
        private readonly FeatureGroup $group = FeatureGroup::Features,
        \Closure|bool $default = false,
        ?\Closure $contribution = null,
    ) {
        $this->default = $default instanceof \Closure ? $default : static fn (): bool => $default;
        $this->contribution = $contribution ?? static function (): void {};
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getGroup(): FeatureGroup
    {
        return $this->group;
    }

    public function getQuestion(): string
    {
        return \sprintf('Enable %s?', $this->name);
    }

    public function isEnabledByDefault(FeatureSelection $previousAnswers): bool
    {
        return ($this->default)($previousAnswers);
    }

    public function contribute(BundleBlueprint $blueprint): void
    {
        ($this->contribution)($blueprint);
    }
}
