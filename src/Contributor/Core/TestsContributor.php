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

namespace Yceruto\BundleFlex\Contributor\Core;

use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\ComposerManifest;
use Yceruto\BundleFlex\Blueprint\Constraint;
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Contributor\ContributorInterface;

/**
 * The test suite every bundle has, run with "composer test" (a plain "phpunit"):
 *
 *  - phpunit.dist.xml, failing on any deprecation triggered by the code of the bundle;
 *  - tests/App/TestKernel.php, a minimal application registering the bundle (see the TestKernel* slots);
 *  - tests/Integration/<BundleClass>Test.php, booting it and checking the services of the bundle.
 *
 * @see https://symfony.com/doc/current/bundles/best_practices.html#tests
 */
final class TestsContributor implements ContributorInterface
{
    /**
     * PHPUnit 11.5 is the last version supporting PHP 8.2, PHPUnit 12 requires PHP 8.3:
     * Composer installs the latest one the PHP version supports.
     */
    public const PHPUNIT_CONSTRAINT = '^11.5 || ^12.0';

    public function contribute(BundleBlueprint $blueprint): void
    {
        $naming = $blueprint->naming;

        $blueprint->composer->requireDev('phpunit/phpunit', self::PHPUNIT_CONSTRAINT);
        // the test kernel (MicroKernelTrait) and KernelTestCase
        $blueprint->composer->requireDev('symfony/framework-bundle', Constraint::SYMFONY);
        $blueprint->composer->addScript(ComposerManifest::TEST_SCRIPT, 'phpunit', 'Runs the tests (PHPUnit)', qa: true);

        $blueprint->addFile('core/phpunit.dist.xml.tpl', 'phpunit.dist.xml');
        $blueprint->addFile('core/tests/App/TestKernel.php.tpl', 'tests/App/TestKernel.php');
        $blueprint->addFile('core/tests/Integration/BundleTest.php.tpl', 'tests/Integration/'.$naming->className.'Test.php');

        $blueprint->slots->addValue(ListSlot::GitAttributesExportIgnore, '/phpunit.dist.xml', '/tests');
        $blueprint->slots->addValue(ListSlot::GitIgnore, '/.phpunit.cache/');
    }
}
