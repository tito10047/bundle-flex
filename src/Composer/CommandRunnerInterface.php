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

/**
 * Composer commands run on the generated bundle.
 */
interface CommandRunnerInterface
{
    /**
     * Runs "composer update"; returns the exit code.
     */
    public function update(): int;

    /**
     * Runs "composer run-script <script>"; returns the exit code.
     */
    public function runScript(string $script): int;
}
