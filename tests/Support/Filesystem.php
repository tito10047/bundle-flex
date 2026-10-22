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

use Composer\Util\Filesystem as ComposerFilesystem;

final class Filesystem
{
    public static function createTempDir(string $prefix): string
    {
        $dir = sys_get_temp_dir().'/bundle-flex-tests/'.$prefix.'-'.bin2hex(random_bytes(6));
        (new ComposerFilesystem())->ensureDirectoryExists($dir);

        return $dir;
    }

    public static function remove(string $path): void
    {
        (new ComposerFilesystem())->remove($path);
    }

    /**
     * @return array<string, string> file contents indexed by relative path, sorted
     */
    public static function readTree(string $dir): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if (!$file instanceof \SplFileInfo || !$file->isFile()) {
                continue;
            }

            $path = substr($file->getPathname(), \strlen($dir) + 1);
            $files[$path] = (string) file_get_contents($file->getPathname());
        }
        ksort($files);

        return $files;
    }
}
