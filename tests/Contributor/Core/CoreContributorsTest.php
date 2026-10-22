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
use Yceruto\BundleFlex\Blueprint\FileTemplate;
use Yceruto\BundleFlex\Contributor\Core\BundleClassContributor;
use Yceruto\BundleFlex\Contributor\Core\DocumentationContributor;
use Yceruto\BundleFlex\Contributor\Core\GreeterContributor;
use Yceruto\BundleFlex\Contributor\Core\LicenseContributor;
use Yceruto\BundleFlex\Contributor\Core\PackageContributor;
use Yceruto\BundleFlex\Contributor\Core\RepositoryContributor;
use Yceruto\BundleFlex\Contributor\Core\TestsContributor;
use Yceruto\BundleFlex\Contributor\CoreContributors;
use Yceruto\BundleFlex\Options\ConfigFormat;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class CoreContributorsTest extends TestCase
{
    public function testDefaultContributors(): void
    {
        $contributors = iterator_to_array(CoreContributors::createDefault(2030));

        self::assertSame([
            PackageContributor::class,
            BundleClassContributor::class,
            GreeterContributor::class,
            TestsContributor::class,
            DocumentationContributor::class,
            LicenseContributor::class,
            RepositoryContributor::class,
        ], array_map(static fn (object $contributor): string => $contributor::class, $contributors));

        self::assertSame(['year' => 2030], self::renderLicenseParameters(CoreContributors::createDefault(2030)));
    }

    public function testDefaultLicenseYearIsTheCurrentYear(): void
    {
        self::assertSame(['year' => (int) date('Y')], self::renderLicenseParameters(CoreContributors::createDefault()));
    }

    public function testPackageContributor(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());
        (new PackageContributor())->contribute($blueprint);

        self::assertSame(['symfony', 'symfony-bundle'], $blueprint->composer->getKeywords());
        self::assertSame([
            'php' => '>=8.2',
            'symfony/config' => '^7.4 || ^8.0',
            'symfony/dependency-injection' => '^7.4 || ^8.0',
            'symfony/http-kernel' => '^7.4 || ^8.0',
        ], $blueprint->composer->getRequire());
        self::assertSame([], $blueprint->composer->getRequireDev());
    }

    public function testPackageContributorRequiresYamlForTheYamlFormat(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(configFormat: ConfigFormat::Yaml));
        (new PackageContributor())->contribute($blueprint);

        self::assertSame('^7.4 || ^8.0', $blueprint->composer->getRequire()['symfony/yaml'] ?? null);
    }

    public function testBundleClassContributor(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create('my-vendor/foo-bundle'));
        (new BundleClassContributor())->contribute($blueprint);

        self::assertEquals(['src/MyVendorFooBundle.php' => new FileTemplate('core/src/Bundle.php.tpl', 'src/MyVendorFooBundle.php')], $blueprint->getFiles());
    }

    /**
     * @return array<string, mixed>
     */
    private static function renderLicenseParameters(CoreContributors $contributors): array
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create());
        foreach ($contributors as $contributor) {
            if ($contributor instanceof LicenseContributor) {
                $contributor->contribute($blueprint);
            }
        }

        return $blueprint->getFiles()['LICENSE']->parameters;
    }
}
