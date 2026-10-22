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
use Yceruto\BundleFlex\Contributor\Core\DocumentationContributor;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class DocumentationContributorTest extends TestCase
{
    public function testContributesTheDocumentation(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());

        (new DocumentationContributor())->contribute($blueprint);

        self::assertSame(['README.md', 'CHANGELOG.md', 'docs/index.md'], array_keys($blueprint->getFiles()));
        self::assertSame(['/docs'], $blueprint->slots->getValues(ListSlot::GitAttributesExportIgnore));
    }
}
