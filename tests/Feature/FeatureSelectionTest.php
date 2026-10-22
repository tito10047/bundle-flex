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

namespace Yceruto\BundleFlex\Tests\Feature;

use PHPUnit\Framework\TestCase;
use Yceruto\BundleFlex\Feature\FeatureSelection;

final class FeatureSelectionTest extends TestCase
{
    public function testIsImmutable(): void
    {
        $empty = new FeatureSelection();
        $selection = $empty->with('twig', true)->with('doctrine', false)->with('ux', true);

        self::assertFalse($empty->has('twig'));
        self::assertTrue($selection->has('doctrine'));
        self::assertTrue($selection->isEnabled('twig'));
        self::assertFalse($selection->isEnabled('doctrine'));
        self::assertSame(['twig', 'ux'], $selection->getEnabled());
        self::assertSame(['twig' => true, 'doctrine' => false, 'ux' => true], $selection->toArray());
    }

    public function testOverridingAnAnswerKeepsItsPosition(): void
    {
        $selection = (new FeatureSelection(['a' => true, 'b' => true]))->with('a', false);

        self::assertSame(['a' => false, 'b' => true], $selection->toArray());
    }

    public function testUnansweredFeaturesAreErrors(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('The feature "twig" has not been answered (yet). Answered features: "controllers".');

        (new FeatureSelection(['controllers' => true]))->isEnabled('twig');
    }
}
