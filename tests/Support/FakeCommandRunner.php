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

use Yceruto\BundleFlex\Composer\CommandRunnerInterface;

final class FakeCommandRunner implements CommandRunnerInterface
{
    /** @var list<string> */
    public array $calls = [];

    /**
     * @param array<string, int> $scriptStatuses
     */
    public function __construct(
        private readonly int|\Exception $updateResult = 0,
        private readonly array $scriptStatuses = [],
    ) {
    }

    public function update(): int
    {
        $this->calls[] = 'update';
        if ($this->updateResult instanceof \Exception) {
            throw $this->updateResult;
        }

        return $this->updateResult;
    }

    public function runScript(string $script): int
    {
        $this->calls[] = 'run-script '.$script;

        return $this->scriptStatuses[$script] ?? 0;
    }
}
