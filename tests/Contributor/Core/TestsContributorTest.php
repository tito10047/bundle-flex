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
use Yceruto\BundleFlex\Contributor\Core\TestsContributor;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class TestsContributorTest extends TestCase
{
    public function testContributesTheTestSuite(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create('my-vendor/foo-bundle'));

        (new TestsContributor())->contribute($blueprint);

        self::assertSame(['phpunit.dist.xml', 'tests/App/TestKernel.php', 'tests/Integration/MyVendorFooBundleTest.php'], array_keys($blueprint->getFiles()));
        self::assertSame([
            'phpunit/phpunit' => TestsContributor::PHPUNIT_CONSTRAINT,
            'symfony/framework-bundle' => '^7.4 || ^8.0',
        ], $blueprint->composer->getRequireDev());
        self::assertSame([], $blueprint->composer->getRequire());

        $script = $blueprint->composer->getScripts()['test'] ?? null;
        self::assertNotNull($script);
        self::assertSame(['phpunit'], $script->commands);
        self::assertSame(['test'], $blueprint->composer->getQaScriptNames());

        self::assertSame(['/phpunit.dist.xml', '/tests'], $blueprint->slots->getValues(ListSlot::GitAttributesExportIgnore));
        self::assertSame(['/.phpunit.cache/'], $blueprint->slots->getValues(ListSlot::GitIgnore));
    }
}
