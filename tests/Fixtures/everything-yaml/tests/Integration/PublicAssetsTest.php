<?php

declare(strict_types=1);

namespace MyVendor\EverythingYamlBundle\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

final class PublicAssetsTest extends KernelTestCase
{
    public function testAssetsAreInstalledInThePublicDirectoryOfTheApplication(): void
    {
        $application = new Application(self::bootKernel());
        $filesystem = new Filesystem();
        $publicDir = $filesystem->tempnam(sys_get_temp_dir(), 'my-vendor-everything-yaml-public-');
        $filesystem->remove($publicDir);
        $filesystem->mkdir($publicDir);

        try {
            $tester = new CommandTester($application->find('assets:install'));
            $tester->execute(['target' => $publicDir]);

            $tester->assertCommandIsSuccessful();
            self::assertFileEquals(
                \dirname(__DIR__, 2).'/public/css/my-vendor-everything-yaml.css',
                $publicDir.'/bundles/myvendoreverythingyaml/css/my-vendor-everything-yaml.css',
            );
        } finally {
            $filesystem->remove($publicDir);
        }
    }
}
