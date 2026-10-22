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
use Yceruto\BundleFlex\Blueprint\BundleBlueprint;
use Yceruto\BundleFlex\Blueprint\FileTemplate;
use Yceruto\BundleFlex\Blueprint\ListSlot;
use Yceruto\BundleFlex\Blueprint\Partial;
use Yceruto\BundleFlex\Blueprint\Slot;
use Yceruto\BundleFlex\Feature\DoctrineFeature;
use Yceruto\BundleFlex\Feature\FeatureGroup;
use Yceruto\BundleFlex\Feature\FeatureSelection;
use Yceruto\BundleFlex\Naming\BundleNaming;
use Yceruto\BundleFlex\Tests\Support\OptionsFactory;

final class DoctrineFeatureTest extends TestCase
{
    public function testDescribesTheQuestion(): void
    {
        $feature = new DoctrineFeature();

        self::assertSame('doctrine', $feature->getName());
        self::assertSame(FeatureGroup::Features, $feature->getGroup());
        self::assertStringEndsWith('?', $feature->getQuestion());
        self::assertFalse($feature->isEnabledByDefault(new FeatureSelection()));
    }

    public function testContributesTheEntityItsMappingAndItsTests(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['doctrine' => true, 'phpstan' => false]));

        (new DoctrineFeature())->contribute($blueprint);

        self::assertSame(['doctrine/orm' => DoctrineFeature::ORM_CONSTRAINT], $blueprint->composer->getRequire());
        self::assertSame(['doctrine/doctrine-bundle' => DoctrineFeature::DOCTRINE_BUNDLE_CONSTRAINT], $blueprint->composer->getRequireDev());
        self::assertContains('doctrine', $blueprint->composer->getKeywords());
        self::assertEquals([
            'src/Entity/Greeting.php' => new FileTemplate('features/doctrine/src/Entity/Greeting.php.tpl', 'src/Entity/Greeting.php'),
            'config/doctrine/Greeting.orm.xml' => new FileTemplate('features/doctrine/config/doctrine/Greeting.orm.xml.tpl', 'config/doctrine/Greeting.orm.xml'),
            'tests/Entity/GreetingTest.php' => new FileTemplate('features/doctrine/tests/Entity/GreetingTest.php.tpl', 'tests/Entity/GreetingTest.php'),
            'tests/Integration/Doctrine/GreetingMappingTest.php' => new FileTemplate('features/doctrine/tests/Integration/Doctrine/GreetingMappingTest.php.tpl', 'tests/Integration/Doctrine/GreetingMappingTest.php'),
        ], $blueprint->getFiles());
        self::assertSame([], $blueprint->services->getServices());
    }

    public function testRegistersTheMappingAndTheTestDatabase(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['doctrine' => true, 'phpstan' => false]));

        (new DoctrineFeature())->contribute($blueprint);

        self::assertEquals([new Partial('features/doctrine/partials/bundle_prepend_extension.php.tpl')], $blueprint->slots->getPartials(Slot::BundlePrependExtension));
        self::assertSame(['Doctrine\Bundle\DoctrineBundle\DoctrineBundle'], $blueprint->slots->getValues(ListSlot::TestKernelBundles));
        self::assertSame(['Composer\InstalledVersions'], $blueprint->slots->getValues(ListSlot::TestKernelUses));
        self::assertEquals([new Partial('features/doctrine/partials/test_kernel.php.tpl')], $blueprint->slots->getPartials(Slot::TestKernelConfigureContainer));
        self::assertSame(['pdo_sqlite'], $blueprint->slots->getValues(ListSlot::CiPhpExtensions));
        self::assertEquals([new Partial('features/doctrine/partials/readme_step.md.tpl')], $blueprint->slots->getPartials(Slot::ReadmeInstallationSteps));
        self::assertEquals([new Partial('features/doctrine/partials/docs.md.tpl')], $blueprint->slots->getPartials(Slot::DocsSections));
        self::assertSame([], $blueprint->slots->getPartials(Slot::PhpStanParameters));
    }

    public function testConfiguresThePhpStanExtensionWhenPhpStanIsEnabled(): void
    {
        $blueprint = new BundleBlueprint(OptionsFactory::create(features: ['doctrine' => true, 'phpstan' => true]));

        (new DoctrineFeature())->contribute($blueprint);

        self::assertSame('^2.0', $blueprint->composer->getConstraint('phpstan/phpstan-doctrine'));
        self::assertTrue($blueprint->hasFile('tests/object-manager.php'));
        self::assertEquals([new Partial('features/doctrine/partials/phpstan.neon.tpl')], $blueprint->slots->getPartials(Slot::PhpStanParameters));
    }

    public function testNamesTheMappingAndTheTableAfterTheBundle(): void
    {
        $naming = new BundleNaming('my-vendor/foo-bar-bundle');

        self::assertSame('MyVendorFooBarBundle', DoctrineFeature::mappingName($naming));
        self::assertSame('my_vendor_foo_bar_greeting', DoctrineFeature::tableName($naming));
    }
}
