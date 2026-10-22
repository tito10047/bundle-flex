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

namespace Yceruto\BundleFlex\Contributor;

use Yceruto\BundleFlex\Contributor\Core\BundleClassContributor;
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Contributor\Core\LicenseContributor;
use Yceruto\BundleFlex\Contributor\Core\PackageContributor;
use Yceruto\BundleFlex\Contributor\Core\TestsContributor;

/**
 * Contributors of the files every bundle has, whatever the selected features.
 * They run before the features, in this order.
 *
 * @implements \IteratorAggregate<int, ContributorInterface>
 */
final class CoreContributors implements \IteratorAggregate
{
    /** @var list<ContributorInterface> */
    private readonly array $contributors;

    public function __construct(ContributorInterface ...$contributors)
    {
        $this->contributors = array_values($contributors);
    }

    /**
     * @param int|null $year year of the copyright notice of the LICENSE file, the current year by default
     */
    public static function createDefault(?int $year = null): self
    {
        return new self(
            new PackageContributor(),
            new BundleClassContributor(),
            new GreeterContributor(),
            new TestsContributor(),
            new LicenseContributor($year ?? (int) date('Y')),
        );
    }

    /**
     * @return \ArrayIterator<int, ContributorInterface>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->contributors);
    }
}
