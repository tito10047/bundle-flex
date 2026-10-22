<?php
/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->namespace.'\Entity\Greeting',
    'PHPUnit\Framework\TestCase',
], $naming->testsNamespace.'\Entity');
$greeting = $imports->name($naming->namespace.'\Entity\Greeting');
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\Entity;

<?= $imports->render() ?>

final class GreetingTest extends <?= $imports->name('PHPUnit\Framework\TestCase') ?>

{
    public function testHoldsTheGreeting(): void
    {
        $createdAt = new \DateTimeImmutable('2026-01-01 10:00:00');
        $greeting = new <?= $greeting ?>('Jane', 'Hello Jane!', $createdAt);

        self::assertNull($greeting->getId());
        self::assertSame('Jane', $greeting->getName());
        self::assertSame('Hello Jane!', $greeting->getMessage());
        self::assertSame($createdAt, $greeting->getCreatedAt());
    }

    public function testIsCreatedNowByDefault(): void
    {
        $before = new \DateTimeImmutable();
        $greeting = new <?= $greeting ?>('Jane', 'Hello Jane!');
        $after = new \DateTimeImmutable();

        self::assertGreaterThanOrEqual($before, $greeting->getCreatedAt());
        self::assertLessThanOrEqual($after, $greeting->getCreatedAt());
    }
}
