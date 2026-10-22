<?php
use Yceruto\BundleFlex\Feature\CommandFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$commandName = $helper->php(CommandFeature::commandName($naming));
$imports = $helper->imports([
    'Symfony\Bundle\FrameworkBundle\Console\Application',
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
    'Symfony\Component\Console\CommandLoader\CommandLoaderInterface',
    'Symfony\Component\Console\Tester\CommandTester',
], $naming->testsNamespace.'\Integration\Command');
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\Integration\Command;

<?= $imports->render() ?>

final class HelloCommandTest extends <?= $imports->name('Symfony\Bundle\FrameworkBundle\Test\KernelTestCase') ?>

{
    public function testCommandIsRegisteredInTheApplication(): void
    {
        $application = new <?= $imports->name('Symfony\Bundle\FrameworkBundle\Console\Application') ?>(self::bootKernel());
        $tester = new <?= $imports->name('Symfony\Component\Console\Tester\CommandTester') ?>($application->find(<?= $commandName ?>));

        $tester->execute(['name' => 'Jane']);

        $tester->assertCommandIsSuccessful();
        self::assertStringContainsString('Hello Jane!', $tester->getDisplay());
    }

    public function testCommandIsLazyLoaded(): void
    {
        self::bootKernel();

        $commandLoader = self::getContainer()->get('console.command_loader');

        self::assertInstanceOf(<?= $imports->name('Symfony\Component\Console\CommandLoader\CommandLoaderInterface') ?>::class, $commandLoader);
        self::assertTrue($commandLoader->has(<?= $commandName ?>));
    }
}
