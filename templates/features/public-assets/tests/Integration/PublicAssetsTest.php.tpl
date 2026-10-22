<?php
use Yceruto\BundleFlex\Feature\PublicAssetsFeature;

/**
 * @var Yceruto\BundleFlex\Naming\BundleNaming     $naming
 * @var Yceruto\BundleFlex\Template\TemplateHelper $helper
 */
$imports = $helper->imports([
    'Symfony\Bundle\FrameworkBundle\Console\Application',
    'Symfony\Bundle\FrameworkBundle\Test\KernelTestCase',
    'Symfony\Component\Console\Tester\CommandTester',
    'Symfony\Component\Filesystem\Filesystem',
], $naming->testsNamespace.'\Integration');
?>
<?= "<?php\n" ?>

declare(strict_types=1);

namespace <?= $naming->testsNamespace ?>\Integration;

<?= $imports->render() ?>

final class PublicAssetsTest extends KernelTestCase
{
    public function testAssetsAreInstalledInThePublicDirectoryOfTheApplication(): void
    {
        $application = new Application(self::bootKernel());
        $filesystem = new Filesystem();
        $publicDir = $filesystem->tempnam(sys_get_temp_dir(), <?= $helper->php($naming->slug.'-public-') ?>);
        $filesystem->remove($publicDir);
        $filesystem->mkdir($publicDir);

        try {
            $tester = new CommandTester($application->find('assets:install'));
            $tester->execute(['target' => $publicDir]);

            $tester->assertCommandIsSuccessful();
            self::assertFileEquals(
                \dirname(__DIR__, 2).'/public/<?= PublicAssetsFeature::stylesheetPath($naming) ?>',
                $publicDir.'/<?= PublicAssetsFeature::stylesheetPublicPath($naming) ?>',
            );
        } finally {
            $filesystem->remove($publicDir);
        }
    }
}
