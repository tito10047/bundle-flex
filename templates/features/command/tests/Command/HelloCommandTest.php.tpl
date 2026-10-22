<?php
use Yceruto\BundleFlex\Feature\CommandFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    $naming->namespace.'\Command\HelloCommand',
    $naming->namespace.'\Greeter',
    'PHPUnit\Framework\TestCase',
    'Symfony\Component\Console\Command\Command',
    'Symfony\Component\Console\Tester\CommandTester',
], $naming->testsNamespace.'\Command');
$command = $imports->name('Symfony\Component\Console\Command\Command');
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\Command;

<?= $imports->render() ?>

final class HelloCommandTest extends <?= $imports->name('PHPUnit\Framework\TestCase') ?>

{
    public function testIsDescribedByItsAttribute(): void
    {
        $command = $this->createCommand();

        self::assertSame(<?= $helper->php(CommandFeature::commandName($naming)) ?>, $command->getName());
        self::assertSame('Greets someone', $command->getDescription());
        self::assertTrue($command->getDefinition()->getArgument('name')->isRequired());
        self::assertSame('y', $command->getDefinition()->getOption('yell')->getShortcut());
    }

    public function testGreetsTheGivenName(): void
    {
        $tester = new <?= $imports->name('Symfony\Component\Console\Tester\CommandTester') ?>($this->createCommand());

        $tester->execute(['name' => 'Jane']);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('[OK] Hello Jane!', $tester->getDisplay());
    }

    public function testYellsTheGreeting(): void
    {
        $tester = new <?= $imports->name('Symfony\Component\Console\Tester\CommandTester') ?>($this->createCommand());

        $tester->execute(['name' => 'Jane', '--yell' => true]);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('[OK] HELLO JANE!', $tester->getDisplay());
    }

    public function testRejectsAnEmptyName(): void
    {
        $tester = new <?= $imports->name('Symfony\Component\Console\Tester\CommandTester') ?>($this->createCommand());

        $tester->execute(['name' => ' ']);

        self::assertSame(<?= $command ?>::INVALID, $tester->getStatusCode());
        self::assertStringContainsString('[ERROR] The name to greet must not be empty.', $tester->getDisplay());
    }

    private function createCommand(): <?= $command ?>

    {
        return new <?= $command ?>(null, new <?= $imports->name($naming->namespace.'\Command\HelloCommand') ?>(new <?= $imports->name($naming->namespace.'\Greeter') ?>('Hello')));
    }
}
