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

namespace Yceruto\BundleFlex\Tests;

use Composer\Composer;
use Composer\IO\NullIO;
use Composer\Script\ScriptEvents;
use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Flex;

/**
 * Flex::onPostCreateProject() is covered end-to-end by "composer create-project" (see CONTRIBUTING.md).
 */
final class FlexTest extends TestCase
{
    public function testSubscribesToTheProjectCreation(): void
    {
        self::assertSame([ScriptEvents::POST_CREATE_PROJECT_CMD => 'onPostCreateProject'], Flex::getSubscribedEvents());
    }

    public function testLifecycleHooksHaveNoSideEffects(): void
    {
        $flex = new Flex();
        $composer = new Composer();
        $io = new NullIO();

        $flex->activate($composer, $io);
        $flex->deactivate($composer, $io);
        $flex->uninstall($composer, $io);

        $this->addToAssertionCount(1);
    }
}
