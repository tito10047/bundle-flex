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

namespace Yceruto\BundleFlex\Tests\Contributor\Core;

use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Contributor\Core\RepositoryContributor;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class RepositoryContributorTest extends TestCase
{
    public function testContributesTheRepositoryFiles(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());

        (new RepositoryContributor())->contribute($blueprint);

        self::assertSame(['.editorconfig', '.gitattributes', '.gitignore'], array_keys($blueprint->getFiles()));
        self::assertSame(['/.editorconfig', '/.gitattributes', '/.github', '/.gitignore'], $blueprint->slots->getValues(ListSlot::GitAttributesExportIgnore));
        self::assertSame(['/composer.lock', '/vendor/'], $blueprint->slots->getValues(ListSlot::GitIgnore));
    }
}
