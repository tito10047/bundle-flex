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

namespace Yceruto\BundleFlex\Tests\Blueprint;

use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Blueprint\Partial;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Blueprint\Slots;

final class SlotsTest extends TestCase
{
    public function testOrdersPartialsByPriorityThenRegistration(): void
    {
        $slots = new Slots();
        $slots->addPartial(Slot::BundleLoadExtension, 'a.tpl');
        $slots->addPartial(Slot::BundleLoadExtension, 'b.tpl', ['x' => 1], 5);
        $slots->addPartial(Slot::BundleLoadExtension, 'c.tpl');
        $slots->addPartial(Slot::BundleLoadExtension, 'd.tpl', priority: -1);

        self::assertEquals([
            new Partial('b.tpl', ['x' => 1], 5),
            new Partial('a.tpl'),
            new Partial('c.tpl'),
            new Partial('d.tpl', [], -1),
        ], $slots->getPartials(Slot::BundleLoadExtension));
        self::assertTrue($slots->hasPartials(Slot::BundleLoadExtension));
        self::assertFalse($slots->hasPartials(Slot::BundleConfigure));
        self::assertSame([], $slots->getPartials(Slot::BundleConfigure));
    }

    public function testKeepsUniqueValuesInRegistrationOrder(): void
    {
        $slots = new Slots();
        $slots->addValue(ListSlot::BundleUses, 'B', 'A');
        $slots->addValue(ListSlot::BundleUses, 'B', 'C');

        self::assertSame(['B', 'A', 'C'], $slots->getValues(ListSlot::BundleUses));
        self::assertSame([], $slots->getValues(ListSlot::GitIgnore));
    }

    public function testRejectsEmptyValues(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Values of the "gitignore" slot must not be empty.');

        (new Slots())->addValue(ListSlot::GitIgnore, '');
    }

    /**
     * A slot that no master template renders is a bug: contributions to it would be silently lost.
     */
    public function testEverySlotIsRenderedByACoreTemplate(): void
    {
        $templates = '';
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(\dirname(__DIR__, 2).'/templates/core', \FilesystemIterator::SKIP_DOTS));
        foreach ($iterator as $file) {
            if ($file instanceof \SplFileInfo && $file->isFile()) {
                $templates .= file_get_contents($file->getPathname());
            }
        }

        foreach (Slot::cases() as $slot) {
            self::assertMatchesRegularExpression('/\$slots->(render|renderEach)\(Slot::'.$slot->name.'\b/', $templates, $slot->name);
        }
        foreach (ListSlot::cases() as $slot) {
            self::assertMatchesRegularExpression('/\$slots->values\(ListSlot::'.$slot->name.'\)/', $templates, $slot->name);
        }
    }
}
